<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateVsftpdConfig extends Command
{

    // command for updating the vsftpd config file

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:update-vsftpd-config';

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
        $vsftpd = view('server.samples.vsftpd.vsftpd-conf', [])->render();

        $command = "
            if [ ! -d '/etc/vsftpd/' ]; then
                sudo mkdir -p /etc/vsftpd/
            fi
            echo \"{$vsftpd}\" | sudo tee /etc/vsftpd/vsftpd.conf
        ";

        $result = shell_exec($command);

        if ($result !== null) {
            $this->info('The vsftpd configuration is updated.');
        } else {
            $this->info('Not updated.');
        }

    }

}
