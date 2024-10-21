<?php

namespace App\Console\Commands;

use App\Models\DomainDkim;
use App\Models\DomainSslCertificate;
use App\OmegaBlade;
use App\OmegaConfig;
use Illuminate\Console\Command;

class SetupEmailServer extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'omega:setup-email-server';

    /**
     * The console command description.
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sslPaths = [];
        $findSSL = DomainSslCertificate::where('domain', setting('email.hostname'))->first();
        if ($findSSL) {
            $getSslPaths = $findSSL->getSslPaths();
            if ($getSslPaths) {
                $sslPaths = $getSslPaths;
            }
        }

        $mysql = [
            'host' => OmegaConfig::get('MYSQL_HOST', '127.0.0.1'),
            'port' => OmegaConfig::get('MYSQL_PORT', '3306'),
            'username' => OmegaConfig::get('DB_USERNAME'),
            'password' => OmegaConfig::get('DB_PASSWORD'),
            'database' => OmegaConfig::get('DB_DATABASE'),
        ];

        if (!is_dir('/etc/postfix/sql')) {
            mkdir('/etc/postfix/sql');
        }

        $postfixMysqlVirtualAliasMapsCf = OmegaBlade::render('server.postfix.sql.mysql_virtual_alias_maps.cf', $mysql);
        file_put_contents('/etc/postfix/sql/mysql_virtual_alias_maps.cf', $postfixMysqlVirtualAliasMapsCf);

        $postfixMysqlVirtualDomainsMapsCf = OmegaBlade::render('server.postfix.sql.mysql_virtual_domains_maps.cf', $mysql);
        file_put_contents('/etc/postfix/sql/mysql_virtual_domains_maps.cf', $postfixMysqlVirtualDomainsMapsCf);

        $postfixMysqlVirtualMailboxMapsCf = OmegaBlade::render('server.postfix.sql.mysql_virtual_mailbox_maps.cf', $mysql);
        file_put_contents('/etc/postfix/sql/mysql_virtual_mailbox_maps.cf', $postfixMysqlVirtualMailboxMapsCf);

        $findDkim = DomainDkim::where('domain_name', setting('email.domain'))->first();
        $postfixMainCf = OmegaBlade::render('server.postfix.main.cf', [
            'hostName' => setting('email.hostname'),
            'domain' => setting('email.domain'),
            'sslPaths' => $sslPaths,
            'dkim' => $findDkim
        ]);

        file_put_contents('/etc/postfix/main.cf', $postfixMainCf);

        $postfixMasterCf = OmegaBlade::render('server.postfix.master.cf');
        file_put_contents('/etc/postfix/master.cf', $postfixMasterCf);

        $openDkimConf = OmegaBlade::render('server.opendkim.opendkim.conf', [
            'hostName' => setting('email.hostname'),
            'domain' => setting('email.domain'),
            'mysqlConnectionUrl' => $mysql['username'] . ':' . $mysql['password'] . '@' . $mysql['host'] . '/' . $mysql['database'],
        ]);
        file_put_contents('/etc/opendkim.conf', $openDkimConf);

        $commands = [
            'systemctl restart dovecot',
            'systemctl restart postfix',
            'systemctl restart opendkim'
        ];

        foreach ($commands as $command) {
            shell_exec($command);
        }
    }


}
