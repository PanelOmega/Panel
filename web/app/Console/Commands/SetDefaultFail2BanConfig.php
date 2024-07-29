<?php

namespace App\Console\Commands;

use App\Jobs\Fail2BanConfigReset;
use App\Services\Fail2Ban\Fail2BanResetConfig\Fail2BanResetConfigService;
use Illuminate\Console\Command;

class SetDefaultFail2BanConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:set-default-fail-2-ban-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets to default the config file for the the Fail2Ban service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $resetConfig = Fail2BanResetConfigService::resetConfig();

        $file2ban = view('server.samples.fail2ban.fail2ban_jail_conf', [
            'settings' => $resetConfig
        ])->render();
        
        $command = "test ! -d /etc/fail2ban/ && -p /etc/fail2ban/ || echo 'Directory exists!'";

        $result = shell_exec($command);

        if (!str_contains($result, 'Directory exists!')) {
            $this->info('Unable to create Fail2Ban directory! Try reinstalling the service.');
        } else {
            $save = file_put_contents('/etc/fail2ban/jail.local', $file2ban);

            if ($save) {
                $this->info('Fail2Ban configuration is set to default!');
            } else {
                $this->info('Fail2Ban configuration is not set to default!');
            }
        }

        $command = 'systemctl restart fail2ban';
        shell_exec($command);
    }
}
