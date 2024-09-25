<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
class UpdateBind9Config extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:update-bind9-config';

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
        $named = view('server.samples.bind9.bind9_named_conf', [])->render();

        $save = file_put_contents('/etc/named.conf', $named);

        if ($save) {
            $this->info('The default bind9 configuration is set.');
        } else {
            $this->info('The default bind9 configuration is not set.');
        }

        shell_exec('sudo systemctl restart named');
    }

}
