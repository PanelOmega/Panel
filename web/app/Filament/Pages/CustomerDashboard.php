<?php

namespace App\Filament\Pages;

use App\ModulesManager;
use Filament\Pages\Page;

class CustomerDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static string $view = 'filament.pages.customer-dashboard';

    protected static ?string $navigationGroup = 'Demo Pages';

    protected static ?string $navigationLabel = 'Customer Dashboard';

    protected static ?int $navigationSort = 1;

    protected function getViewData(): array
    {
        return [
            'menu' => [

                'email'=>[
                    'title'=>'Email',
                    'icon'=>'omega_customer-email',
                    'menu'=>[
                        [
                            'title'=>'Email Accounts',
                            'icon'=>'omega_customer-email-account',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Forwarders',
                            'icon'=>'omega_customer-email-forwarders',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Email Routing',
                            'icon'=>'omega_customer-email-routing',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Autoresponders',
                            'icon'=>'omega_customer-email-autoresponders',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Default Address',
                            'icon'=>'omega_customer-email-default',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Mailing Lists',
                            'icon'=>'omega_customer-email-list',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Track Delivery',
                            'icon'=>'omega_customer-email-track',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Global Email Filters',
                            'icon'=>'omega_customer-email-global-filter',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Email Filters',
                            'icon'=>'omega_customer-email-filter',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Email Deliverability',
                            'icon'=>'omega_customer-email-deliverability',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Address Importer',
                            'icon'=>'omega_customer-email-importer',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Spam Filters',
                            'icon'=>'omega_customer-email-spam-filters',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Encryption',
                            'icon'=>'omega_customer-email-encryption',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'BoxTrapper',
                            'icon'=>'omega_customer-email-box-trap',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Calendars and Contacts Configuration',
                            'icon'=>'omega_customer-email-calendar-configuration',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Calendars and Contacts Sharing',
                            'icon'=>'omega_customer-email-calendar-share',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Calendars and Contacts Management',
                            'icon'=>'omega_customer-email-calendar-management',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Email Disk Usage',
                            'icon'=>'omega_customer-email-disk',
                            'link'=>'#'
                        ]
                    ]
                ],

                'billing_and_support'=>[
                    'title'=>'Billing & Support',
                    'icon'=>'omega_customer-billing',
                    'menu'=>[
                        [
                            'title'=>'News & Announcemnets',
                            'icon'=>'omega_customer-billing-news-announcement',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Manage Biling Information',
                            'icon'=>'omega_customer-billing-manage-information',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Download Resources',
                            'icon'=>'omega_customer-billing-download',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'View Email History',
                            'icon'=>'omega_customer-billing-history',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'View Invoice History',
                            'icon'=>'omega_customer-billing-invoice-history',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Search our Knowledgebase',
                            'icon'=>'omega_customer-billing-search-knowledgebase',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Check Network Status',
                            'icon'=>'omega_customer-billing-network-status',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'View Billing Information',
                            'icon'=>'omega_customer-billing-information',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Manage Profile',
                            'icon'=>'omega_customer-billing-manage-profile',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Register New Domain',
                            'icon'=>'omega_customer-billing-register-domain',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Transfer a Domain',
                            'icon'=>'omega_customer-billing-transfer-domain',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Open Ticket',
                            'icon'=>'omega_customer-billing-open-ticket',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'View Support Tickets',
                            'icon'=>'omega_customer-billing-support-ticket',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Upgrade/Downgrade',
                            'icon'=>'omega_customer-billing-update',
                            'link'=>'#'
                        ]
                    ]
                ],

                'files'=>[
                    'title'=>'Files',
                    'icon'=>'omega_customer-files',
                    'menu'=>[
                        [
                            'title'=>'File Manager',
                            'icon'=>'omega_customer-file-manager',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Images',
                            'icon'=>'omega_customer-file-images',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Directory Privacy',
                            'icon'=>'omega_customer-file-directory-privacy',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Disk Usage',
                            'icon'=>'omega_customer-file-disk',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Web Disk',
                            'icon'=>'omega_customer-file-web',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'FTP Accounts',
                            'icon'=>'omega_customer-file-ftp',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'FTP Connections',
                            'icon'=>'omega_customer-file-connection',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Backup',
                            'icon'=>'omega_customer-file-backup',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Backup Wizard',
                            'icon'=>'omega_customer-file-backup-wizard',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Git Version Control',
                            'icon'=>'omega_customer-file-git',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'File and Directory Restoration',
                            'icon'=>'omega_customer-file-directory-restoration',
                            'link'=>'#'
                        ],
                    ]
                ],

                'database'=>[
                    'title'=>'Database',
                    'icon'=>'omega_customer-database',
                    'menu'=>[
                        [
                            'title'=>'phpMyAdmin',
                            'icon'=>'omega_customer-database-php',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Manage My Database',
                            'icon'=>'omega_customer-database-manage',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Database Wizard',
                            'icon'=>'omega_customer-database-wizard',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Remote Database Access',
                            'icon'=>'omega_customer-database-remote',
                            'link'=>'#'
                        ]
                    ]
                ],

                'domains'=>[
                    'title'=>'Domains',
                    'icon'=>'omega_customer-domains',
                    'menu'=>[
                        [
                            'title'=>'WP Toolkit',
                            'icon'=>'omega_customer-domains-wp',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Site Publisher',
                            'icon'=>'omega_customer-domains-site',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Sitejet Builder',
                            'icon'=>'omega_customer-domains-sitejet',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Domains',
                            'icon'=>'omega_customer-domains-domain',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Redirects',
                            'icon'=>'omega_customer-domains-redirect',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Zone Editor',
                            'icon'=>'omega_customer-domains-zone',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Dynamic DNS',
                            'icon'=>'omega_customer-domains-dynamic',
                            'link'=>'#'
                        ]
                    ]
                ],

                'metrics'=>[
                    'title'=>'Metrics',
                    'icon'=>'omega_customer-metrics',
                    'menu'=>[
                        [
                            'title'=>'Visitors',
                            'icon'=>'omega_customer-metrics-visitors',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Site Quality Monitoring',
                            'icon'=>'omega_customer-metrics-site-quality',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Errors',
                            'icon'=>'omega_customer-metrics-errors',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Bandwidth',
                            'icon'=>'omega_customer-metrics-bandwidth',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Raw Access',
                            'icon'=>'omega_customer-metrics-raw',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Awstats',
                            'icon'=>'omega_customer-metrics-awstats',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Analog Stats',
                            'icon'=>'omega_customer-metrics-analog',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Webalizer',
                            'icon'=>'omega_customer-metrics-webalizer',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Webalizer FTP',
                            'icon'=>'omega_customer-metrics-webalizer-ftp',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Metrics Editor',
                            'icon'=>'omega_customer-metrics-editor',
                            'link'=>'#'
                        ]
                    ]
                ],

                'security'=>[
                    'title'=>'Security',
                    'icon'=>'omega_customer-security',
                    'menu'=>[
                        [
                            'title'=>'SSH Access',
                            'icon'=>'omega_customer-security-ssh',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'IP Blockers',
                            'icon'=>'omega_customer-security-block',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'SSL/TLS',
                            'icon'=>'omega_customer-security-ssl-tls',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Manage API Tokens',
                            'icon'=>'omega_customer-security-api',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Hotlink Protection',
                            'icon'=>'omega_customer-security-hotlink',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Leech Protection',
                            'icon'=>'omega_customer-security-leech',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'SSL/TSL Status',
                            'icon'=>'omega_customer-security-status',
                            'link'=>'#'
                        ]
                    ]
                ],

                'software'=>[
                    'title'=>'Software',
                    'icon'=>'omega_customer-software',
                    'menu'=>[
                        [
                            'title'=>'PHP PEAR Packages',
                            'icon'=>'omega_customer-software-packages',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Perl Modules',
                            'icon'=>'omega_customer-software-perl',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Site Software',
                            'icon'=>'omega_customer-software-site',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Optimaze Website',
                            'icon'=>'omega_customer-software-optimaze',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'MultiPHP Manager',
                            'icon'=>'omega_customer-software-manager',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'MultiPHP INI Editor',
                            'icon'=>'omega_customer-software-editor',
                            'link'=>'#'
                        ]
                    ]
                ],

                'advanced'=>[
                    'title'=>'Advanced',
                    'icon'=>'omega_customer-advanced',
                    'menu'=>[
                        [
                            'title'=>'Cron Jobs',
                            'icon'=>'omega_customer-advanced-cron',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Track DNS',
                            'icon'=>'omega_customer-advanced-dns',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Indexes',
                            'icon'=>'omega_customer-advanced-indexes',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Error Pages',
                            'icon'=>'omega_customer-advanced-error',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Apache Handlers',
                            'icon'=>'omega_customer-advanced-apache',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'MIME Types',
                            'icon'=>'omega_customer-advanced-mime',
                            'link'=>'#'
                        ]
                    ]
                ],

                'preferences'=>[
                    'title'=>'Preferences',
                    'icon'=>'omega_customer-preferences',
                    'menu'=>[
                        [
                            'title'=>'Password & Security',
                            'icon'=>'omega_customer-preferences-pass',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Change Language',
                            'icon'=>'omega_customer-preferences-language',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'Contact Information',
                            'icon'=>'omega_customer-preferences-contact',
                            'link'=>'#'
                        ],
                        [
                            'title'=>'User Manager',
                            'icon'=>'omega_customer-preferences-user',
                            'link'=>'#'
                        ]
                    ]
                ]


            ],
        ];

    }
}
