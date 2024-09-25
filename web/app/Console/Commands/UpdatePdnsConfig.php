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
        $pdns = view('server.samples.pdns.pdns_conf', [])->render();

        $command = "
            if [ ! -d '/etc/pdns/' ]; then
                mkdir -p /etc/pdns/
            fi
        ";
        $result = shell_exec($command);

        if ($result !== null) {
            $this->info('Unable to create pdns directory!');
        }

        $save = file_put_contents('/etc/pdns/pdns.conf', $pdns);

        if ($save) {
            $this->info('The default pdns configuration is set.');
        } else {
            $this->info('The default pdns configuration is not set.');
        }

        shell_exec('sudo systemctl restart pdns');
    }
}
