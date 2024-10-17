<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdatePdnsServiceConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:update-pdns-service';

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

//        shell_exec('systemctl restart pdns');
    }

}
