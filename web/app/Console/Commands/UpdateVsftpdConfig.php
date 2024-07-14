<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateVsftpdConfig extends Command
{

    // commands for updating the vsftpd config file

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

        if (file_put_contents('/etc/vsftpd/vsftpd.conf', $vsftpd)) {

            $this->info('The vsftpd configuration is updated.');
        } else {

            $this->info('Not updated.');
        }


    }

}
