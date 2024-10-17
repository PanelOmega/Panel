<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdatePdnsDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:update-pdns-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates pdns database configuration';

    /**
     * Execute the console command.
     */
    public function handle()
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

        shell_exec('sudo systemctl restart pdns');
    }

}
