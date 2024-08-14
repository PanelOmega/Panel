<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\MultiPHPManagerResource\Pages;
use App\FilamentCustomer\Resources\MultiPHPManagerResource\RelationManagers;
use App\Models\Domain;
use App\Models\MultiPHPManager;
use App\Models\Scopes\CustomerScope;
use App\Server\Helpers\PHP;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MultiPHPManagerResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Multi PHP Manager';

    protected static ?string $label = 'Multi PHP Manager';
    protected static ?string $pluralLabel = 'Multi PHP Manager';

    protected static ?string $slug = 'multi-php-manager';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        $phpVersions = [];
        $getInstalledPHPVersions = PHP::getInstalledPHPVersions();
        foreach ($getInstalledPHPVersions as $version) {
            $phpVersions[$version['full']] = $version['friendlyName'];
        }

        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->label('Domain')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Select::make('server_application_settings.php_version')
                    ->label('PHP Version')
                    ->columnSpanFull()
                    ->options($phpVersions)
                    ->required(),
                Forms\Components\Toggle::make('server_application_settings.enable_php_fpm')
                    ->label('Enable PHP FPM')
                    ->columnSpanFull()
                    ->default(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phpVersion')
                    ->label('PHP Version'),
                Tables\Columns\IconColumn::make('phpFpm')
                    ->label('PHP-FPM')
                    ->icon(fn (string $state): string => match ($state) {
                        'disabled' => 'heroicon-o-x-mark',
                        'enabled' => 'heroicon-o-check-circle',
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withGlobalScope('customer', new CustomerScope());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMultiPHPManagers::route('/'),
//            'create' => Pages\CreateMultiPHPManager::route('/create'),
//            'edit' => Pages\EditMultiPHPManager::route('/{record}/edit'),
        ];
    }
}
