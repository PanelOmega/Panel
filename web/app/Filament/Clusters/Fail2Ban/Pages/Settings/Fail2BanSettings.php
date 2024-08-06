<?php

namespace App\Filament\Clusters\Fail2Ban\Pages\Settings;

use App\Filament\Clusters\Fail2Ban\Fail2Ban;
use App\Filament\Pages\Base\BaseSettings;
use App\Jobs\Fail2BanConfigBuild;
use App\Server\SupportedApplicationTypes;
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
    public array $apache_server_extensions = [];
    public array $nginx_server_extensions = [];
    public array $wordpress_server_extensions = [];
    public array $fail2ban_jails = [
        'sshd' => false,
        'apache' => false,
        'vsftpd' => false,
    ];

    public static function getNavigationLabel(): string
    {
        return 'Fail2Ban Settings';
    }

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
                                        ->helperText('Enabling this option will activate all jails.')
                                        ->live()
                                        ->default(false),

                                    Group::make([

                                        Grid::make()
                                            ->schema([

                                                TextInputSelectAffix::make('fail2ban.config.general.bantime')
                                                    ->placeholder('Default: 1 hour/s')
                                                    ->helperText('Set the length of time an IP address will be banned by all active jails after an attack is detected.')
                                                    ->select(function () {
                                                        return Select::make('fail2ban.config.general.bantime_unit')
                                                            ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
                                                            ->default('h');
                                                    }),

                                                TextInputSelectAffix::make('fail2ban.config.general.findtime')
                                                    ->placeholder('Default: 10 minute/s')
                                                    ->helperText('A host will be banned by all active jails if it has exceeded the \'maxretry\' limit within the specified \'findtime\' period.')
                                                    ->select(function () {
                                                        return Select::make('fail2ban.config.general.findtime_unit')
                                                            ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
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
                                                    ->helperText('A host is banned if it has generated "maxretry" during the last "findtime".')
                                                    ->placeholder('Default: 5'),

                                                Select::make('fail2ban.config.general.backend')
                                                    ->label('Backend')
                                                    ->helperText('Choose the backend used to monitor file modifications.
                                            This setting can also be customized individually for each jail.')
                                                    ->options(SupportedApplicationTypes::getFial2BanGeneralBackend())
                                                    ->default('auto'),
                                            ])
                                            ->columns(2),
                                        Grid::make()
                                            ->schema([
                                                Select::make('fail2ban.config.general.usedns')
                                                    ->label('Usedns')
                                                    ->helperText('Choose whether the jails should trust and use hostnames found in logs, provide a warning when performing DNS lookups, or ignore all hostnames in logs.')
                                                    ->options(SupportedApplicationTypes::getFail2BanGeneralUsedns())
                                                    ->default('warn'),


                                                Select::make('fail2ban.config.general.logencoding')
                                                    ->label('Log Encoding')
                                                    ->helperText('The "logencoding" setting determines the type of text encoding used for the log files that the jail processes.')
                                                    ->options(SupportedApplicationTypes::getFail2BanGeneralLogencoding())
                                                    ->default('auto'),
                                            ])
                                            ->columns(2),

                                    ])->hidden(function (Get $get) {
                                        return !$get('fail2ban.config.general.enabled');
                                    }),


                                ]),
                        ]),

                    Tabs\Tab::make('Actions')
                        ->schema([
                            Section::make('Report Actions')
                                ->schema([
                                    Grid::make()
                                        ->schema([
                                            TextInput::make('fail2ban.config.action.destemail')
                                                ->label('Destination email')
                                                ->helperText('This is the email address where notifications will be sent based on the configurations in the jail.{conf,local,d/*} files.')
                                                ->placeholder('Default: null'),

                                            TextInput::make('fail2ban.config.action.sender')
                                                ->label('Sender')
                                                ->helperText('The sender email address is used specifically for certain actions in the configuration files to define who the notifications come from.')
                                                ->placeholder('Default: null'),

                                            Select::make('fail2ban.config.action.mta')
                                                ->label('MTA')
                                                ->helperText('Specify the email sending method for Fail2Ban: use sendmail for the default option, or switch to mail to use a traditional email system.')
                                                ->options(SupportedApplicationTypes::getFail2BanActionsMta())
                                                ->default('sendmail'),

                                            Select::make('fail2ban.config.action.protocol')
                                                ->label('Protocol')
                                                ->helperText('Specify the communication protocol used by Fail2Ban for its actions; by default, it uses TCP for reliable data transmission.')
                                                ->options(SupportedApplicationTypes::getFail2BanActionsProtocol())
                                                ->default('tcp'),

                                            TextInput::make('fail2ban.config.action.port')
                                                ->label('Port')
                                                ->helperText('Specify the range of ports to be banned, which is generally set individually for each specific jail. By default, it covers all ports from 0 to 65535.')
                                                ->placeholder('Default: 0:65535'),

                                            Select::make('fail2ban.config.action.banaction')
                                                ->label('Ban Action')
                                                ->helperText('Sets the default action for banning, which can be customized globally or for individual sections in the jail.local file.')
                                                ->options(SupportedApplicationTypes::getFail2BanBanactions())
                                                ->default('iptables-multiport')
                                        ])
                                        ->columns(2),
                                ])
                        ]),
                    Tabs\Tab::make('Jails')
                        ->schema([
//                            SSH servers
                            Section::make('SSHD Jail')
                                ->schema([
                                    Toggle::make('fail2ban.config.jails.sshd.enabled')
                                        ->label('')
                                        ->helperText('Turning on this option will activate the SSHD jail, which helps protect your SSH service from unauthorized access.')
                                        ->live()
                                        ->default(false),

                                    Group::make([
//                                        TextInput::make('fail2ban.config.jails.sshd.port')
//                                            ->label('Port')
//                                            ->helperText('Specify the range of ports that should be banned. By default, it is configured to protect the standard SSH port.')
//                                            ->placeholder('Default: ssh'),

//                                        Select::make('fail2ban.config.jails.sshd.filter')
//                                            ->label('Filter')
//                                            ->helperText('The \'filter\' sets the criteria used by the jail to identify malicious activity.')
//                                            ->options(SupportedApplicationTypes::getFail2BanJailFilters('sshd'))
//                                            ->default('sshd'),

                                        TextInputSelectAffix::make('fail2ban.config.jails.sshd.findtime')
                                            ->label('Find Time')
                                            ->placeholder('Default: 1800 minute/s')
                                            ->helperText('Set the time period in which a host must exceed the maximum number of failed login attempts to trigger a ban.')
                                            ->select(function () {
                                                return Select::make('fail2ban.config.jails.sshd.findtime_unit')
                                                    ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
                                                    ->default('m');
                                            }),

                                        TextInputSelectAffix::make('fail2ban.config.jails.sshd.bantime')
                                            ->label('Ban Time')
                                            ->placeholder('Default: 7200 minute/s')
                                            ->helperText('Set the length of time an IP address will be banned by the jail after an attack is detected.')
                                            ->select(function () {
                                                return Select::make('fail2ban.config.jails.sshd.bantime_unit')
                                                    ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
                                                    ->default('m');
                                            }),

//                                        Select::make('fail2ban.config.jails.sshd.banaction')
//                                            ->label('Ban Action')
//                                            ->helperText('Set the default method for blocking an IP address when a ban is triggered.')
//                                            ->options(SupportedApplicationTypes::getFail2BanBanactions())
//                                            ->default('iptables'),

                                        TextInput::make('fail2ban.config.jails.sshd.maxretry')
                                            ->label('Max Retry')
                                            ->helperText('A host is banned if it has generated "maxretry" during the last "findtime".')
                                            ->placeholder('Default: 4'),

                                        TextInput::make('fail2ban.config.jails.sshd.logpath')
                                            ->label('Log Path')
                                            ->helperText('Specify the path to the log file where Fail2Ban should look for login attempts and other relevant events.')
                                            ->placeholder('Default: /var/log/fail2ban.log'),
                                    ])
                                        ->hidden(function (Get $get) {
                                            return !$get('fail2ban.config.jails.sshd.enabled');
                                        })
                                        ->columns(2),
                                ]),

//                                    HTTP servers
                            Section::make('Apache Jail')
                                ->schema([
                                    Toggle::make('fail2ban.config.jails.apache.enabled')
                                        ->label('')
                                        ->helperText('Turning on this option will activate the Apache jail, which helps protect your Apache server from unauthorized access and malicious activity.')
                                        ->live()
                                        ->default(false),

                                    Group::make([
//                                        TextInput::make('fail2ban.config.jails.apache.port')
//                                            ->label('Port')
//                                            ->helperText('Specify the range of ports that should be banned. By default, it is configured to protect the standard HTTP and HTTPS ports.')
//                                            ->placeholder('Default: http,https')
//                                            ->reactive(),

//                                        Select::make('fail2ban.config.jails.apache.filter')
//                                            ->label('Filter')
//                                            ->helperText('The \'filter\' sets the criteria used by the jail to identify malicious activity.')
//                                            ->options(SupportedApplicationTypes::getFail2BanJailFilters('apache'))
//                                            ->default('apache-auth'),

                                        TextInputSelectAffix::make('fail2ban.config.jails.apache.findtime')
                                            ->label('Find Time')
                                            ->placeholder('Default: 1800 minute/s')
                                            ->helperText('Set the time period in which a host must exceed the maximum number of failed login attempts to trigger a ban.')
                                            ->select(function () {
                                                return Select::make('fail2ban.config.jails.apache.findtime_unit')
                                                    ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
                                                    ->default('m');
                                            }),

                                        TextInputSelectAffix::make('fail2ban.config.jails.apache.bantime')
                                            ->label('Ban Time')
                                            ->placeholder('Default: 7200 minute/s')
                                            ->helperText('Set the length of time an IP address will be banned by the jail after an attack is detected.')
                                            ->select(function () {
                                                return Select::make('fail2ban.config.jails.apache.bantime_unit')
                                                    ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
                                                    ->default('m');
                                            }),

//                                        Select::make('fail2ban.config.jails.apache.banaction')
//                                            ->label('Ban Action')
//                                            ->helperText('Set the default method for blocking an IP address when a ban is triggered.')
//                                            ->options(SupportedApplicationTypes::getFail2BanBanactions())
//                                            ->default('iptables'),

                                        TextInput::make('fail2ban.config.jails.apache.maxretry')
                                            ->label('Max retry')
                                            ->helperText('A host is banned if it has generated "maxretry" during the last "findtime".')
                                            ->placeholder('Default: 4'),

                                        TextInput::make('fail2ban.config.jails.apache.logpath')
                                            ->label('Log Path')
                                            ->helperText('Specify the path to the log file where Fail2Ban should look for login attempts and other relevant events.')
                                            ->placeholder('Default: /var/log/fail2ban.log'),

                                    ])
                                        ->hidden(function (Get $get) {
                                            return !$get('fail2ban.config.jails.apache.enabled');
                                        })
                                        ->columns(2),
                                ]),

//                                    FTP Servers
                            Section::make('vsFTPD Jail')
                                ->schema([
                                    Toggle::make('fail2ban.config.jails.vsftpd.enabled')
                                        ->label('')
                                        ->helperText('Turning on this option will activate the vsFTPD jail, which helps protect your vFTPD server from unauthorized access and malicious activity.')
                                        ->live()
                                        ->default(false),

                                    Group::make([
//                                        TextInput::make('fail2ban.config.jails.vsftpd.port')
//                                            ->label('Port')
//                                            ->helperText('Specify the range of ports that should be banned. By default, it is configured to protect the standard FTP, FTP-DATA, FTPS, FTPS-DATA ports.')
//                                            ->placeholder('Default: ftp,ftp-data,ftps,ftps-data')
//                                            ->reactive(),

//                                        Select::make('fail2ban.config.jails.vsftpd.filter')
//                                            ->label('Filter')
//                                            ->helperText('The \'filter\' sets the criteria used by the jail to identify malicious activity.')
//                                            ->options(SupportedApplicationTypes::getFail2BanJailFilters('vsftpd'))
//                                            ->default('vsftpd'),

                                        TextInputSelectAffix::make('fail2ban.config.jails.vsftpd.findtime')
                                            ->label('Find Time')
                                            ->placeholder('Default: 1800 minute/s')
                                            ->helperText('Set the time period in which a host must exceed the maximum number of failed login attempts to trigger a ban.')
                                            ->select(function () {
                                                return Select::make('fail2ban.config.jails.vsftpd.findtime_unit')
                                                    ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
                                                    ->default('m');
                                            }),

                                        TextInputSelectAffix::make('fail2ban.config.jails.vsftpd.bantime')
                                            ->label('Ban Time')
                                            ->placeholder('Default: 7200 minute/s')
                                            ->helperText('Set the length of time an IP address will be banned by the jail after an attack is detected.')
                                            ->select(function () {
                                                return Select::make('fail2ban.config.jails.vsftpd.bantime_unit')
                                                    ->options(SupportedApplicationTypes::getFail2BanTimeUnits())
                                                    ->default('m');
                                            }),

//                                        Select::make('fail2ban.config.jails.vsftpd.banaction')
//                                            ->label('Ban Action options')
//                                            ->helperText('Set the default method for blocking an IP address when a ban is triggered.')
//                                            ->options(SupportedApplicationTypes::getFail2BanBanactions())
//                                            ->default('iptables'),

                                        TextInput::make('fail2ban.config.jails.vsftpd.maxretry')
                                            ->label('Max Retry')
                                            ->helperText('A host is banned if it has generated "maxretry" during the last "findtime".')
                                            ->placeholder('Default: 4'),

                                        TextInput::make('fail2ban.config.jails.vsftpd.logpath')
                                            ->label('Log Path')
                                            ->helperText('Specify the path to the log file where Fail2Ban should look for login attempts and other relevant events.')
                                            ->placeholder('Default: /var/log/fail2ban.log'),

                                    ])
                                        ->hidden(function (Get $get) {
                                            return !$get('fail2ban.config.jails.vsftpd.enabled');
                                        })
                                        ->columns(2),
                                ]),
                        ])

                ]),
        ];
    }
}
