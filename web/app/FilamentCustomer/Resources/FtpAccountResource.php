<?php

namespace App\FilamentCustomer\Resources;

use App\FilamentCustomer\Resources\FtpAccountResource\Pages;
use App\FilamentCustomer\Resources\FtpAccountResource\RelationManagers;
use App\Models\Domain;
use App\Models\HostingSubscription;
use App\Models\HostingSubscriptionFtpAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;

class FtpAccountResource extends Resource
{

    protected static ?string $label = 'FTP Account';
    protected static ?string $navigationLabel = 'FTP Accounts';

    protected static ?string $model = HostingSubscriptionFtpAccount::class;

    protected static ?string $navigationGroup = 'Files';

    protected static ?string $slug = 'customer-ftp-accounts';

    protected static ?string $navigationIcon = 'omega_customer-file-ftp';

    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        $system_username = HostingSubscription::all()->pluck('system_username', 'id')->toArray();
        $path_username = array_values($system_username)[0];

        $domains = Domain::all()->pluck('domain', 'domain')->toArray();

        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make()
                            ->schema([

                                TextInput::make('ftp_username')
                                    ->label('Log In')
                                    ->suffix('@')
                                    ->required(),

                                Select::make('domain')
                                    ->searchable('domain')
                                    ->options($domains)
                                    ->label('Domain')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        $selectDomain = $state;
                                        $ftpUsername = $get('ftp_username');
                                        $set('ftp_path', 'public/' . $selectDomain . '/' . $ftpUsername);
                                    }),

                                TextInput::make('ftp_password')
                                    ->label('Password')
                                    ->confirmed()
                                    ->password()
                                    ->revealable()
                                    ->required(),

                                TextInput::make('ftp_password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->required(),

                                TextInput::make('ftp_path')
                                    ->label('Directory')
                                    ->prefix('/home/' . $path_username . '/'),

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

                Tables\Columns\TextColumn::make('ftpNameWithPrefix')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ftp_path')
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListFtpAccounts::route('/'),
            'create' => Pages\CreateFtpAccount::route('/create'),
            'edit' => Pages\EditFtpAccount::route('/{record}/edit'),
        ];
    }
}
