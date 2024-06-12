<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FirewallRuleResource\Pages;
use App\Filament\Resources\FirewallRuleResource\RelationManagers;
use App\Models\FirewallRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FirewallRuleResource extends Resource
{
    protected static ?string $model = FirewallRule::class;

    protected static ?string $navigationIcon = 'omega-firewall';

    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('action')
                    ->label('Action')
                    ->options([
                        'allow' => 'ALLOW',
                        'deny' => 'DENY',
                    ])
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\TextInput::make('port_or_ip')
                    ->label('Port or IP')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Textarea::make('comment')
                    ->label('Comment')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(function ($record) {
                        return $record->action === 'ALLOW' ? 'success' : 'danger';
                    })
                    ->searchable(),
//                Tables\Columns\TextColumn::make('direction')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('to_port')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to_ip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('protocol')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('from_port')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('from_ip')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ]);
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
            'index' => Pages\ListFirewallRules::route('/'),
//            'create' => Pages\CreateFirewallRule::route('/create'),
//            'edit' => Pages\EditFirewallRule::route('/{record}/edit'),
        ];
    }
}
