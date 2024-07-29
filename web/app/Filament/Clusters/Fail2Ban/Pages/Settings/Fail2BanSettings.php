<?php

namespace App\Filament\Clusters\Fail2Ban\Pages\Settings;

use App\Filament\Clusters\Fail2Ban\Fail2Ban;
use App\Jobs\Fail2BanConfigBuild;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Components\Tab;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Fail2BanSettings extends BaseSettings
{
    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $cluster = Fail2Ban::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Fail2Ban Settings';
    }

    public array $apache_server_extensions = [];
    public array $nginx_server_extensions = [];
    public array $wordpress_server_extensions = [];
    public array $fail2ban_jails = [
        'sshd' => false,
        'apache' => false,
        'vsftpd' => false,
    ];
    public function save(): void
    {
        parent::save();
        $fail2banConfigBuild = new Fail2BanConfigBuild();
        $fail2banConfigBuild->handle();
    }
    public function schema(): array|\Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([

                    Tabs\Tab::make('General')
                        ->schema([
                            Section::make('Enable Jails')
                                ->schema([
                                    Toggle::make('fail2ban.config.general.enabled')
                                        ->label('')
                                        ->default(false),
                                ]),

                            Grid::make()
                                ->schema([
                                    TextInput::make('fail2ban.config.general.bantime')
                                        ->label('Ban Time')
                                        ->placeholder('Default: 1 hour/s'),

                                    Select::make('fail2ban.config.general.unit.bantime')
                                        ->label('Ban Time options')
                                        ->options([
                                            's' => 'second/s',
                                            'm' => 'minute/s',
                                            'h' => 'hour/s'
                                        ])
                                        ->default(function ($get) {
                                            $bantime = $get('fail2ban.config.general.bantime');
                                            return $bantime ? null : 'Select an option';
                                        })
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, $get, $set) {
                                            $bantime = $get('fail2ban.config.general.bantime');
                                            if (!$bantime) {
                                                $set('fail2ban.config.general.unit.bantime', 'Select an option');
                                            }
                                        }),
                                ])
                                ->columns(2),

                            TextInput::make('fail2ban.config.general.ignorecommand')
                                ->label('Ignore command')
                                ->placeholder('Default: null'),

                            Grid::make()
                                ->schema([
                                    TextInput::make('fail2ban.config.general.findtime')
                                        ->label('Find Time')
                                        ->placeholder('Default: 10 minute/s'),

                                    Select::make('fail2ban.config.general.unit.findtime')
                                        ->label('Find Time options')
                                        ->options([
                                            's' => 'second/s',
                                            'm' => 'minute/s',
                                            'h' => 'hour/s'
                                        ])
                                        ->default(function ($get) {
                                            $findtime = $get('fail2ban.config.general.findtime');
                                            return $findtime ? null : 'Select an option';
                                        })
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, $get, $set) {
                                            $findtime = $get('fail2ban.config.general.findtime');
                                            if (!$findtime) {
                                                $set('fail2ban.config.general.unit.findtime', 'Select an option');
                                            }
                                        }),
                                ])
                                ->columns(2),

                            Grid::make()
                                ->schema([
                                    TextInput::make('fail2ban.config.general.maxretry')
                                        ->label('Max retry')
                                        ->placeholder('Default: 5'),

                                    Select::make('fail2ban.config.general.backend')
                                        ->label('Backend')
                                        ->options([
                                            'auto' => 'auto'
                                        ])
                                        ->default('auto'),
                                ])
                                ->columns(2),

                            Grid::make()
                                ->schema([
                                    Select::make('fail2ban.config.general.usedns')
                                        ->label('Usedns')
                                        ->options([
                                            'warn' => 'warn',
                                            'yes' => 'yes',
                                            'but' => 'but',
                                            'no' => 'no',
                                            'raw' => 'raw'
                                        ])
                                        ->default('warn'),

                                    Select::make('fail2ban.config.general.logencoding')
                                        ->label('Log Encoding')
                                        ->options([
                                            'auto' => 'auto',
                                            'ascii' => 'ascii',
                                            'utf8' => 'utf-8',
                                        ])
                                        ->default('auto'),
                                ])
                                ->columns(2),
                        ]),
                    Tabs\Tab::make('Actions')
                        ->schema([
                            Grid::make()
                                ->schema([
                                    TextInput::make('fail2ban.config.action.destemail')
                                        ->label('Destination email')
                                        ->placeholder('Default: null'),

                                    TextInput::make('fail2ban.config.action.sender')
                                        ->label('Sender')
                                        ->placeholder('Default: null'),

                                    TextInput::make('fail2ban.config.action.mta')
                                        ->label('MTA')
                                        ->placeholder('Default: sendmail'),

                                    Select::make('fail2ban.config.action.protocol')
                                        ->label('Protocol')
                                        ->options([
                                            'tcp' => 'tcp',
                                            'udp' => 'udp',
                                            'tls' => 'tls',
                                            'icmp' => 'icmp',
                                        ])
                                        ->default('tcp'),

                                    TextInput::make('fail2ban.config.action.port')
                                        ->label('Port')
                                        ->placeholder('Default: 0:65535'),

                                    Select::make('fail2ban.config.action.banaction')
                                        ->label('Ban Action')
                                        ->options([
                                            'iptables' => 'iptables',
                                            'iptables-new' => 'iptables-new',
                                            'iptables-multipot' => 'iptables-multiport',
                                            'shorewall' => 'shorewall'
                                        ])
                                        ->default('iptables-multiport')
                                ])
                                ->columns(2),
                        ]),
                    Tabs\Tab::make('Jails')
                        ->schema([
                            Section::make('SSHD')
                                ->schema([
                                    Section::make('Enable Jail')
                                        ->schema([
                                            Toggle::make('fail2ban.config.jails.sshd.enabled')
                                                ->label('')
                                                ->default(false),

                                        ]),

                                    Select::make('fail2ban.config.jails.sshd.port')
                                       ->label('Port')
                                       ->options([
                                          'ssh' => 'ssh',
                                          '22' => '22',
                                          '2222' => '2222',
                                          '2200' => '2200',
                                          '2202' => '2202',
                                          '443' => '443',
                                          '80' => '80'
                                       ])
                                       ->default('ssh'),

                                    Select::make('fail2ban.config.jails.sshd.filter')
                                        ->label('Filter')
                                        ->options([
                                            'sshd' => 'sshd',
                                        ])
                                        ->default('sshd'),

                                    TextInput::make('fail2ban.config.jails.sshd.findtime')
                                        ->label('Find Time')
                                        ->placeholder('Default: 1800')
                                        ->suffix('m'),

                                    TextInput::make('fail2ban.config.jails.sshd.bantime')
                                        ->label('Ban Time')
                                        ->placeholder('Default: 7200')
                                        ->suffix('m'),

                                    Select::make('fail2ban.config.jails.sshd.banaction')
                                        ->label('Ban Action')
                                        ->options([
                                            'iptables' => 'iptables',
                                            'iptables-new' => 'iptables-new',
                                            'iptables-multipot' => 'iptables-multipot',
                                            'shorewall' => 'shorewall'
                                        ])
                                        ->default('iptables'),

                                    TextInput::make('fail2ban.config.jails.sshd.maxretry')
                                        ->label('Max Retry')
                                        ->placeholder('Default: 4'),

                                    TextInput::make('fail2ban.config.jails.sshd.logpath')
                                        ->label('Log Path')
                                        ->placeholder('Default: /var/log/fail2ban.log'),
                                ])
//                                ->visible(fn() => $this->fail2ban_jails['sshd'])
                                ->columns(2),

                            Section::make('Apache')
                                ->schema([
                                    Section::make('Enable Jail')
                                        ->schema([
                                            Toggle::make('fail2ban.config.jails.apache.enabled')
                                                ->label('')
                                                ->default(false),
                                        ]),
                                    Grid::make()
                                        ->schema([
                                            TextInput::make('fail2ban.config.jails.apache.port')
                                                ->label('Port')
                                                ->placeholder('Default: http,https')
                                                ->reactive(),

                                            Select::make('fail2ban.config.jails.apache.unit.port')
                                                ->label('Port options')
                                                ->options([
                                                    'http' => 'http',
                                                    'https' => 'https',
                                                ])
                                                ->live()
                                                ->afterStateUpdated(function ($state, $get, $set) {
                                                    $ports = $get('fail2ban.config.jails.apache.port');

                                                    if($state) {


                                                        if ($ports !== null && $ports !== '') {
                                                            $portsArr = explode(',', $ports);

                                                            if (!in_array($state, $portsArr)) {
                                                                $portsArr[] = $state;
                                                            }
                                                            $set('fail2ban.config.jails.apache.port', implode(',', $portsArr));
                                                        } else {
                                                            $set('fail2ban.config.jails.apache.port', $state);
                                                        }
                                                    } else {
                                                        $set('fail2ban.config.jails.apache.port', null);
                                                    }
                                                })
                                        ])
                                        ->columns(2),

                                    Grid::make()
                                        ->schema([
                                            TextInput::make('fail2ban.config.jails.apache.action')
                                                ->label('Action')
                                                ->placeholder('Default: iptables[name=HTTP, port=http, protocol=tcp]')
                                                ->reactive(),

                                            Select::make('fail2ban.config.jails.apache.unit.action')
                                                ->label('Action options')
                                                ->options([
                                                    'iptables' => 'iptables',
                                                    'iptables-new' => 'iptables-new',
                                                    'iptables-multiport' => 'iptables-multiport'
                                                ])
                                                ->live()
                                                ->afterStateUpdated(function ($state, $set) {

                                                    if($state) {
                                                        $action = $state . '[name= , port= , protocol= ]';
                                                    } else {
                                                        $action = null;
                                                    }
                                                    $set('fail2ban.config.jails.apache.action', $action);
                                                }),
                                        ])
                                        ->columns(2),

                                    Select::make('fail2ban.config.jails.apache.filter')
                                        ->label('Filter')
                                        ->options([
                                            'apache-auth' => 'apache-auth',
                                            'apache-badbots' => 'apache-badbots',
                                            'apache-botsearch' => 'apache-botsearch',
                                            'apache-common' => 'apache-common',
                                            'apache-fakegooglebot' => 'apache-fakegooglebot',
                                            'apache-modsecurity' => 'apache-modsecurity',
                                            'apache-nohome' => 'apache-nohome',
                                            'apache-noscripti' => 'apache-noscripti',
                                            'apache-overflows' => 'apache-overflows',
                                            'apache-pass' => 'apache-pass',
                                            'apache-shellshock' => 'apache-shellshock',
                                        ])
                                        ->default('apache-auth'),

                                    TextInput::make('fail2ban.config.jails.apache.findtime')
                                        ->label('Find Time')
                                        ->placeholder('Default: 1800')
                                        ->suffix('m'),

                                    TextInput::make('fail2ban.config.jails.apache.bantime')
                                        ->label('Ban Time')
                                        ->placeholder('Default: 7200')
                                        ->suffix('m'),

                                    TextInput::make('fail2ban.config.jails.apache.maxretry')
                                        ->label('Max retry')
                                        ->placeholder('Default: 4'),

                                    TextInput::make('fail2ban.config.jails.apache.logpath')
                                        ->label('Log Path')
                                        ->placeholder('Default: /var/log/fail2ban.log'),
                                ])
//                                ->visible(fn() => $this->fail2ban_jails['apache'])
                                ->columns(2),


                            Section::make('vsFTPD')
                                ->schema([
                                    Section::make('Enable Jail')
                                        ->schema([
                                            Toggle::make('fail2ban.config.jails.vsftpd.enabled')
                                                ->label('')
                                                ->default(false),
                                        ]),

                                    Grid::make()
                                        ->schema([
                                            TextInput::make('fail2ban.config.jails.vsftpd.port')
                                                ->label('Port')
                                                ->placeholder('Default: ftp,ftp-data,ftps,ftps-data')
                                                ->reactive(),

                                            Select::make('fail2ban.config.jails.vsftpd.unit.port')
                                                ->label('Port options')
                                                ->options([
                                                    'ftp' => 'ftp',
                                                    'ftp-data' => 'ftp-data',
                                                    'ftps' => 'ftps',
                                                    'ftps-data' => 'ftps-data',
                                                ])
                                                ->live()
                                                ->afterStateUpdated(function ($state, $get, $set) {
                                                    $ports = $get('fail2ban.config.jails.vsftpd.port');

                                                    if($state) {
                                                        if ($ports !== null && $ports !== '') {
                                                            $portsArr = explode(',', $ports);

                                                            if (!in_array($state, $portsArr)) {
                                                                $portsArr[] = $state;
                                                            }
                                                            $set('fail2ban.config.jails.vsftpd.port', implode(',', $portsArr));
                                                        } else {
                                                            $set('fail2ban.config.jails.vsftpd.port', $state);
                                                        }
                                                    } else {
                                                        $set('fail2ban.config.jails.vsftpd.port', null);
                                                    }
                                                })
                                        ])
                                        ->columns(2),

                                    Select::make('fail2ban.config.jails.vsftpd.filter')
                                        ->label('Filter')
                                        ->options([
                                            'vsftpd' => 'vsftpd',
                                        ])
                                        ->default('vsftpd'),

                                    TextInput::make('fail2ban.config.jails.vsftpd.findtime')
                                        ->label('Find Time')
                                        ->placeholder('Default: 1800')
                                        ->suffix('m'),

                                    TextInput::make('fail2ban.config.jails.vsftpd.bantime')
                                        ->label('Ban Time')
                                        ->placeholder('Default: 7200')
                                        ->suffix('m'),

                                    Select::make('fail2ban.config.jails.vsftpd.banaction')
                                        ->label('Ban Action options')
                                        ->options([
                                            'iptables' => 'iptables',
                                            'iptables-new' => 'iptables-new',
                                            'iptables-multipot' => 'iptables-multipot',
                                            'shorewall' => 'shorewall'
                                        ])
                                        ->default('iptables'),

                                    TextInput::make('fail2ban.config.jails.vsftpd.maxretry')
                                        ->label('Max Retry')
                                        ->placeholder('Default: 4'),

                                    TextInput::make('fail2ban.config.jails.vsftpd.logpath')
                                        ->label('Log Path')
                                        ->placeholder('Default: /var/log/fail2ban.log'),
                                ])
//                                ->visible(fn() => $this->fail2ban_jails['vsftpd'])
                                ->columns(2),
                        ])
                ]),
        ];
    }
}
