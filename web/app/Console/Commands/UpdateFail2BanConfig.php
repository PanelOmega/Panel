<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateFail2BanConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:update-fail-2-ban-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the local configuration file for the Fail2Ban service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file2ban = view('server.samples.fail2ban.fail2ban_jail_conf', [])->render();
        $command = "test ! -d /etc/fail2ban/ && -p /etc/fail2ban/ || echo 'Directory exists!'";

        $result = shell_exec($command);

        if (!str_contains($result, 'Directory exists!')) {
            $this->info('Unable to create Fail2Ban directory! Try reinstalling the service.');
        } else {
            $save = file_put_contents('/etc/fail2ban/jail.local', $file2ban);

            if ($save) {
                $this->info('Fail2Ban configuration is updated!');
            } else {
                $this->info('Fail2Ban configuration is not updated!');
            }
        }

        $command = 'systemctl restart fail2ban';
        shell_exec($command);
    }
}
