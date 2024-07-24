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
        ";
        $result = shell_exec($command);

        if ($result !== null) {
            $this->info('Unable to create vasftpd directory!');
        }

        $save = file_put_contents('/etc/vsftpd/vsftpd.conf', $vsftpd);

        if ($save) {
            $this->info('The vsftpd configuration is updated.');
        } else {
            $this->info('The vsftpd configuration is not updated.');
        }

        shell_exec('sudo systemctl restart vsftpd');

    }

}
