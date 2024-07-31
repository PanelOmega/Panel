<?php

namespace App\Filament\Clusters\Fail2Ban\Pages\Settings;

use App\Filament\Clusters\Fail2Ban\Fail2Ban;
use App\Filament\Pages\Base\BaseSettings;
use App\Jobs\Fail2BanConfigBuild;
use CodeWithDennis\SimpleAlert\Components\Forms\SimpleAlert;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Resources\Components\Tab;
use Illuminate\Support\HtmlString;
use Marvinosswald\FilamentInputSelectAffix\TextInputSelectAffix;


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

            SimpleAlert::make('msg')
                ->info()
                ->title('Fail2Ban Settings')
                ->description('Configure Fail2Ban settings'),

            SimpleAlert::make('msg2')
                ->success()
                ->title(new HtmlString('<strong>Hoorraayy! Your request has been approved! 🎉</strong>'))
                ->description('Lorem ipsum dolor sit amet consectetur adipisicing elit.')
                ->link('https://filamentphp.com')
                ->linkLabel('Read more!'),

            Tabs::make('Settings')
                ->schema([

                    Tabs\Tab::make('General')
                        ->schema([

                            Section::make('Jails')
                                ->schema([

                                    Toggle::make('fail2ban.config.general.enabled')
                                        ->label('Enable Jails')
                                        ->live()
                                        ->default(false),

                            Group::make([

                                Grid::make()
                                    ->schema([

                                        TextInputSelectAffix::make('fail2ban.config.general.bantime')
                                            ->placeholder('Default: 1 hour/s')
                                            ->select(function () {
                                                return Select::make('fail2ban.config.general.bantime_unit')
                                                    ->options([
                                                        's' => 'second/s',
                                                        'm' => 'minute/s',
                                                        'h' => 'hour/s'
                                                    ])
                                                    ->default('h');
                                            }),

                                        TextInputSelectAffix::make('fail2ban.config.general.findtime')
                                            ->placeholder('Default: 10 minute/s')

                                            ->select(function () {
                                                return Select::make('fail2ban.config.general.findtime_unit')
                                                    ->options([
                                                        's' => 'second/s',
                                                        'm' => 'minute/s',
                                                        'h' => 'hour/s'
                                                    ])
                                                    ->default('h');
                                            }),

                                    ])
                                    ->columns(2),

                                TextInput::make('fail2ban.config.general.ignorecommand')
                                    ->label('Ignore command')
                                    ->helperText('External command that will take an tagged arguments to ignore, e.g.
                              and return true if the IP is to be ignored. False otherwise.')
                                    ->placeholder('Default: null'),
                                Grid::make()
                                    ->schema([
                                        TextInput::make('fail2ban.config.general.maxretry')
                                            ->label('Max retry')
                                            ->helperText('A host is banned if it has generated "maxretry" during the last "findtime"
//                              seconds.')
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

//                            "usedns" specifies if jails should trust hostnames in logs,
//                              warn when DNS lookups are performed, or ignore all hostnames in logs


                                        Select::make('fail2ban.config.general.logencoding')
                                            ->label('Log Encoding')
                                            ->options([
                                                'auto' => 'auto',
                                                'ascii' => 'ascii',
                                                'utf8' => 'utf-8',
                                            ])
                                            ->default('auto'),
//                                    "logencoding" specifies the encoding of the log files handled by the jail
//                                      This is used to decode the lines from the log file.
                                    ])
                                    ->columns(2),

                            ])->hidden(function (Get $get) {
                                return !$get('fail2ban.config.general.enabled');
                            }),


                        ]),
                  ]),

                    Tabs\Tab::make('Report Actions')
                        ->schema([
                            Grid::make()
                                ->schema([
                                    TextInput::make('fail2ban.config.action.destemail')
                                        ->label('Destination email')
                                        ->placeholder('Default: null'),
//                                    Destination email address used solely for the interpolations in
//                                      jail.{conf,local,d/*} configuration files.

                                    TextInput::make('fail2ban.config.action.sender')
                                        ->label('Sender')
                                        ->placeholder('Default: null'),
//                                    Sender email address used solely for some actions

                                    TextInput::make('fail2ban.config.action.mta')
                                        ->label('MTA')
                                        ->placeholder('Default: sendmail'),

//                                    Since 0.8.1 Fail2Ban uses sendmail MTA for the
//                                      mailing. Change mta configuration parameter to mail if you want to
//                                      revert to conventional 'mail'.

                                    Select::make('fail2ban.config.action.protocol')
                                        ->label('Protocol')
                                        ->options([
                                            'tcp' => 'tcp',
                                            'udp' => 'udp',
                                            'tls' => 'tls',
                                            'icmp' => 'icmp',
                                        ])
                                        ->default('tcp'),

//                                    Default protocol

                                    TextInput::make('fail2ban.config.action.port')
                                        ->label('Port')
                                        ->placeholder('Default: 0:65535'),

//                                    Ports to be banned
//                                      Usually should be overridden in a particular jail

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

//                            Default banning action (e.g. iptables, iptables-new,
//                              iptables-multiport, shorewall, etc) It is used to define
//                              action_* variables. Can be overridden globally or per
//                              section within jail.local file
                        ]),
                    Tabs\Tab::make('Jails')
                        ->schema([
//                            SSH servers
                            Section::make('SSHD')
                                ->schema([
                                    Section::make('Enable Jail')
                                        ->schema([
                                            Toggle::make('fail2ban.config.jails.sshd.enabled')
                                                ->label('')
                                                ->live()
                                                ->default(false),

 //                                    "enabled" enables the SSHD jail.
//                                     By default the jail is disabled, and it should stay this way.


                                            Group::make([
                                                Grid::make()
                                                    ->schema([
                                                        TextInput::make('fail2ban.config.jails.sshd.port')
                                                            ->label('Port')
                                                            ->placeholder('Default: ssh')
                                                            ->reactive(),


                                                        Select::make('fail2ban.config.jails.sshd.unit.port')
                                                            ->label('Port options')
                                                            ->options([
                                                                'ssh' => 'ssh',
                                                                '22' => '22',
                                                                '2222' => '2222',
                                                                '2200' => '2200',
                                                                '2202' => '2202',
                                                                '443' => '443',
                                                                '80' => '80'
                                                            ])
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                                $ports = $get('fail2ban.config.jails.sshd.port');

                                                                if($state) {


                                                                    if ($ports !== null && $ports !== '') {
                                                                        $portsArr = explode(',', $ports);

                                                                        if (!in_array($state, $portsArr)) {
                                                                            $portsArr[] = $state;
                                                                        }
                                                                        $set('fail2ban.config.jails.sshd.port', implode(',', $portsArr));
                                                                    } else {
                                                                        $set('fail2ban.config.jails.sshd.port', $state);
                                                                    }
                                                                } else {
                                                                    $set('fail2ban.config.jails.sshd.port', null);
                                                                }
                                                            })

                                                        //       Ports to be banned
                                                    ])
                                                    ->columns(2),

                                                Select::make('fail2ban.config.jails.sshd.filter')
                                                    ->label('Filter')
                                                    ->options([
                                                        'sshd' => 'sshd',
                                                    ])
                                                    ->default('sshd'),

//                                    "filter" defines the filter to use by the SSHD jail.
//                                      By default jails have names matching their filter name

                                                TextInput::make('fail2ban.config.jails.sshd.findtime')
                                                    ->label('Find Time')
                                                    ->placeholder('Default: 1800')
                                                    ->suffix('m'),

//                                    A host is banned if it has generated "maxretry" during the last "findtime"
//                                      seconds.

                                                TextInput::make('fail2ban.config.jails.sshd.bantime')
                                                    ->label('Ban Time')
                                                    ->placeholder('Default: 7200')
                                                    ->suffix('m'),

//                                    "bantime" is the number of seconds that a host is banned

                                                Select::make('fail2ban.config.jails.sshd.banaction')
                                                    ->label('Ban Action')
                                                    ->options([
                                                        'iptables' => 'iptables',
                                                        'iptables-new' => 'iptables-new',
                                                        'iptables-multipot' => 'iptables-multipot',
                                                        'shorewall' => 'shorewall'
                                                    ])
                                                    ->default('iptables'),

//                                    Default banning action (e.g. iptables, iptables-new,
//                                      iptables-multiport, shorewall, etc) It is used to define
//                                      action_* variables. Can be overridden globally or per
//                                      section within jail.local file

                                                TextInput::make('fail2ban.config.jails.sshd.maxretry')
                                                    ->label('Max Retry')
                                                    ->placeholder('Default: 4'),

                                                TextInput::make('fail2ban.config.jails.sshd.logpath')
                                                    ->label('Log Path')
                                                    ->placeholder('Default: /var/log/fail2ban.log'),
                                            ])
                                                ->hidden(function (Get $get) {
                                                    return !$get('fail2ban.config.jails.sshd.enabled');
                                                }),

//                                    identifies the jail`s default logpath for the SSHD Jail

                                        ]),

                                ])
                                ->columns(2),

                            Section::make('Apache')
                                ->schema([
//                                    HTTP servers
                                    Section::make('Enable Jail')
                                        ->schema([
                                            Toggle::make('fail2ban.config.jails.apache.enabled')
                                                ->label('')
                                                ->live()
                                                ->default(false),

//                                    "enabled" enables the Apache jail.
//                                     By default the jail is disabled, and it should stay this way.

                                            Group::make([
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

                                                        //       Ports to be banned
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

//                                            See action.d/abuseipdb.conf for usage example and details
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

//                                    "filter" defines the filter to use by the Apache jail.
//                                      By default jails have names matching their filter name

                                                TextInput::make('fail2ban.config.jails.apache.findtime')
                                                    ->label('Find Time')
                                                    ->placeholder('Default: 1800')
                                                    ->suffix('m'),

//                                    A host is banned if it has generated "maxretry" during the last "findtime"
//                                      seconds.

                                                TextInput::make('fail2ban.config.jails.apache.bantime')
                                                    ->label('Ban Time')
                                                    ->placeholder('Default: 7200')
                                                    ->suffix('m'),

//                                    "bantime" is the number of seconds that a host is banned

                                                TextInput::make('fail2ban.config.jails.apache.maxretry')
                                                    ->label('Max retry')
                                                    ->placeholder('Default: 4'),

                                                TextInput::make('fail2ban.config.jails.apache.logpath')
                                                    ->label('Log Path')
                                                    ->placeholder('Default: /var/log/fail2ban.log'),

//                                    identifies the jail`s default logpath for the Apache Jail
                                            ])
                                                ->hidden(function (Get $get) {
                                                    return !$get('fail2ban.config.jails.apache.enabled');
                                                }),

                                        ]),
                                ])
                                ->columns(2),


                            Section::make('vsFTPD')
                                ->schema([
//                                    FTP Servers
                                    Section::make('Enable Jail')
                                        ->schema([
                                            Toggle::make('fail2ban.config.jails.vsftpd.enabled')
                                                ->label('')
                                                ->live()
                                                ->default(false),

                                            //                                    "enabled" enables the vsFTPD jail.
//                                     By default the jail is disabled, and it should stay this way.

                                            Group::make([

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
                                                //       Ports to be banned

                                                Select::make('fail2ban.config.jails.vsftpd.filter')
                                                    ->label('Filter')
                                                    ->options([
                                                        'vsftpd' => 'vsftpd',
                                                    ])
                                                    ->default('vsftpd'),

//                                    "filter" defines the filter to use by the vsFTPD jail.
//                                      By default jails have names matching their filter name

                                                TextInput::make('fail2ban.config.jails.vsftpd.findtime')
                                                    ->label('Find Time')
                                                    ->placeholder('Default: 1800')
                                                    ->suffix('m'),

//                                    A host is banned if it has generated "maxretry" during the last "findtime"
//                                      seconds.

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

//                                     Default banning action (e.g. iptables, iptables-new,
//                                      iptables-multiport, shorewall, etc) It is used to define
//                                      action_* variables. Can be overridden globally or per
//                                      section within jail.local file


                                                TextInput::make('fail2ban.config.jails.vsftpd.maxretry')
                                                    ->label('Max Retry')
                                                    ->placeholder('Default: 4'),

                                                TextInput::make('fail2ban.config.jails.vsftpd.logpath')
                                                    ->label('Log Path')
                                                    ->placeholder('Default: /var/log/fail2ban.log'),

//                                    identifies the jail`s default logpath for the vsFTPD Jail

                                            ])
                                                ->hidden(function (Get $get) {
                                                    return !$get('fail2ban.config.jails.vsftpd.enabled');
                                                }),
                                        ]),

                                ])
                                ->columns(2),
                        ])
                ]),
        ];
    }
}
