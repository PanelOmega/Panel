<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FirewallRuleResource\Pages;
use App\Filament\Resources\FirewallRuleResource\RelationManagers;
use App\Models\FirewallRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FirewallRuleResource extends Resource
{
    protected static ?string $model = FirewallRule::class;

    protected static ?string $navigationIcon = 'omega-firewall';

    protected static ?int $navigationSort = 10;

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

        $firewallStatus = FirewallRule::isEnabled();

        $emptyStateActions = [];
        $tableColumns = [];

        if ($firewallStatus) {

            $emptyStateHeading = 'No firewall rules';
            $emptyStateDescription = 'Create a firewall rule to secure your server.';

            $emptyStateActions[] = Tables\Actions\CreateAction::make('create')
                ->label('Create first firewall rule')
                ->icon('heroicon-m-plus')
                ->button();

            $tableColumns = [
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
                //                Tables\Columns\TextColumn::make('protocol')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('from_port')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('from_ip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to_port')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to_ip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable(),
            ];
        } else {
            $emptyStateHeading = 'Firewall is disabled';
            $emptyStateDescription = 'Enable the firewall to create firewall rules.';
            $emptyStateActions[] = Tables\Actions\Action::make('enable')
                ->label('Enable firewall')
                ->action(function (Tables\Actions\Action $action) {
                    if (FirewallRule::enableFirewall()) {
                        Notification::make()
                            ->icon('heroicon-m-shield-check')
                            ->title('Firewall enabled')
                            ->body('The firewall has been enabled.')
                            ->send();

                        $action->redirect(route('filament.admin.resources.firewall-rules.index'));

                    } else {
                        Notification::make()
                          //  ->icon('heroicon-m-shield-x')
                            ->title('Failed to enable firewall')
                            ->body('An error occurred while enabling the firewall.')
                            ->send();
                    }
                })
                ->icon('heroicon-m-shield-check')
                ->button();
        }


        return $table
            ->columns($tableColumns)
            ->emptyStateHeading($emptyStateHeading)
            ->emptyStateDescription($emptyStateDescription)
            ->emptyStateActions($emptyStateActions)
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
