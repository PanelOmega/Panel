<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\FtpAccountResource\Pages;
use App\FilamentCustomer\Resources\FtpAccountResource\RelationManagers;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingSubscription\FtpAccount;
use App\Models\Scopes\CustomerScope;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FtpAccountResource extends Resource
{

    protected static ?string $label = 'FTP Account';

    protected static ?string $navigationLabel = 'FTP Accounts';

    protected static ?string $model = FtpAccount::class;

    protected static ?string $navigationIcon = 'omega_customer-file-ftp';

    protected static ?int $navigationSort = 2;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('ftpHostText')
                ->label('Host'),
            TextEntry::make('ftpPortText')
                ->label('Port'),
            TextEntry::make('ftpUsernameWithPrefix')
                ->label('Username'),
            TextEntry::make('ftpPathText')
                ->label('Directory'),
            TextEntry::make('ftpQuotaText')
                ->label('Quota MB'),
        ]);
    }

    public static function form(Form $form): Form
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $systemUsername = $hostingSubscription->system_username;

        $domains = Domain::where('hosting_subscription_id', $hostingSubscription->id)
            ->get()
            ->pluck('domain', 'domain')
            ->toArray();

        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make()
                            ->schema([

                                TextInput::make('ftp_username')
                                    ->label('Log In')
                                    ->prefix(function (Forms\Get $get) use ($systemUsername) {
                                        return $systemUsername . '_';
                                    })
                                    ->required(),

                                Select::make('domain')
                                    ->searchable('domain')
                                    ->options($domains)
                                    ->label('Domain')
                                    ->default(function (Forms\Get $get) use ($domains) {
                                        return array_key_first($domains);
                                    })
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        $selectDomain = $state;
                                        $ftpUsername = $get('ftp_username');
                                        $set('ftp_path', 'public_html/' . $selectDomain . '/' . $ftpUsername);
                                    }),

                                TextInput::make('ftp_password')
                                    ->label('Password')
                                    ->confirmed()
                                    ->password()
                                    ->revealable()
                                    ->hintAction(
                                        Forms\Components\Actions\Action::make('generate_password')
                                            ->icon('heroicon-m-key')
                                            ->action(function (Forms\Set $set) {
                                                $randomPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+'), 0, 24);
                                                $set('ftp_password', $randomPassword);
                                                $set('ftp_password_confirmation', $randomPassword);
                                            })
                                    )
                                    ->required(),

                                TextInput::make('ftp_password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->required(),

                                TextInput::make('ftp_path')
                                    ->label('Directory')
                                    ->prefix('/home/' . $systemUsername . '/'),

                                Radio::make('ftp_quota_type')
                                    ->options([
                                        'custom' => 'Custom',
                                        'Unlimited' => 'Unlimited',
                                    ])
                                    ->default('Unlimited')
                                    ->live(),

                                TextInput::make('ftp_quota')
                                    ->hidden(function (Forms\Get $get) {
                                        if ($get('ftp_quota_type') == 'custom') {
                                            return false;
                                        }
                                        return true;
                                    })
                                    ->suffix('MB'),

                            ])

                    ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('ftpUsernameWithPrefix')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ftpPathText')
                    ->label('Directory')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ftpQuotaText')
                    ->label('Quota MB')
                    ->searchable()
                    ->sortable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFtpAccounts::route('/'),
            'create' => Pages\CreateFtpAccount::route('/create'),
//            'edit' => Pages\EditFtpAccount::route('/{record}/edit'),
            //   'view' => Pages\ViewFtpAccount::route('/{record}'),
        ];
    }
}
