<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
class UpdatePdnsConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:update-pdns-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        $this->updatePdnsConf();
//        $this->updatePdnsDb();
//        $this->updatePdnsService();
//        shell_exec('sudo systemctl restart pdns');
    }

    public function updatePdnsConf()
    {
        $pdnsConf = view('server.samples.pdns.pdns_conf', [])->render();

        $command = "
            if [ ! -d '/etc/pdns/' ]; then
                mkdir -p /etc/pdns/
            fi
        ";
        $result = shell_exec($command);

        if ($result !== null) {
            $this->info('Unable to create pdns directory!');
        }

        $save = file_put_contents('/etc/pdns/pdns.conf', $pdnsConf);

        if ($save) {
            $this->info('The default pdns configuration is set.');
        } else {
            $this->info('The default pdns configuration is not set.');
        }
    }

    public function updatePdnsDb()
    {
        $pdnsDnssecSchema = view('server.samples.pdns.schema.bind-dnssec-schema-sqlite3-sql', [])->render();

        $command = "
            if [ ! -d '/var/omega/pdns/' ]; then
                mkdir -p /var/omega/pdns/
            fi
        ";
        $result = shell_exec($command);

        if ($result !== null) {
            $this->info('Unable to create pdns directory!');
        }

        $save = file_put_contents('/var/omega/pdns/bind-dnssec.schema.sqlite3.sql', $pdnsDnssecSchema);

        if ($save) {
            $this->info('The default pdns dnssec schema configuration is set.');
        } else {
            $this->info('The default pdns dnssec schema configuration is not set.');
        }

        $pdnsDnssecSchema = view('server.samples.pdns.schema.bind-dnssec-4-2-0_to_4-3-0_schema-sqlite3-sql', [])->render();

        $save = file_put_contents('/var/omega/pdns/bind-dnssec.4.2.0_to_4.3.0_schema.sqlite3.sql', $pdnsDnssecSchema);

        if ($save) {
            $this->info('The default pdns dnssec 4.2.0_to_4.3.0_schema configuration is set.');
        } else {
            $this->info('The default pdns dnssec 4.2.0_to_4.3.0_schema configuration is not set.');
        }
    }

    public function updatePdnsService()
    {
        $configPath = '/lib/systemd/system/pdns.service';

        $pdnsData = [
            'afterTarget' => 'network-online.target time-sync.target',
            'userId' => 'named',
            'groupId' => 'named'
        ];

        $pdns = view('server.samples.pdns.pdns_lib_service', [
            'pdnsData' => $pdnsData
        ])->render();


        $save = file_put_contents($configPath, $pdns);

        if ($save) {
            $this->info('The default pdns configuration is set.');
        } else {
            $this->info('The default pdns configuration is not set.');
        }
    }

}
