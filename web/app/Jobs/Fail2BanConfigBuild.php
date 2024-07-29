<?php

namespace App\Jobs;

use App\Models\Fail2BanWhitelistedIp;
use App\Server\Helpers\OS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Fail2BanConfigBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;

    public function __construct($fixPermissions = false)
    {
        $this->fixPermissions = $fixPermissions;
    }

    public function handle(): void
    {
        $os = OS::getDistro();

        $settings = setting('fail2ban');
        $settingsArr = $this->getSettingsArr($settings);

        $whitelistedIps = [];
        if (Fail2BanWhitelistedIp::all()) {
            $whitelistedIps = Fail2BanWhitelistedIp::pluck('ip')->toArray();
        }

        $fail2BanConf = view('server.samples.fail2ban.fail2ban_jail_conf', [
            'whitelistedIps' => $whitelistedIps,
            'settings' => $settingsArr
        ])->render();

        if ($os == OS::UBUNTU || $os == OS::DEBIAN) {

        } elseif ($os == OS::CLOUD_LINUX || $os == OS::CENTOS || $os == OS::ALMA_LINUX) {
            file_put_contents('/etc/fail2ban/jail.local', $fail2BanConf);
            shell_exec('systemctl restart fail2ban');
        } else {
            throw new \Exception('Unsupported OS');
        }

    }

    public function getSettingsArr($settings): array {
        $settingsArr = [];
        foreach ($settings['config'] as $key => $fail2banSetting) {
            if ($key === 'general') {
                $settingsArr[$key] = [
                    'bantime' => $fail2banSetting['bantime'],
                    'bantime_option' => $fail2banSetting['unit']['bantime'],
                    'ignorecommand' => $fail2banSetting['ignorecommand'],
                    'findtime' => $fail2banSetting['findtime'],
                    'findtime_option' => $fail2banSetting['unit']['findtime'],
                    'maxretry' => $fail2banSetting['maxretry'],
                    'backend' => $fail2banSetting['backend'],
                    'usedns' => $fail2banSetting['usedns'],
                    'logencoding' => $fail2banSetting['logencoding'],
                    'enabled' => $fail2banSetting['enabled']
                ];
            } elseif ($key === 'action') {
                $settingsArr[$key] = [
                    'destemail' => $fail2banSetting['destemail'],
                    'sender' => $fail2banSetting['sender'],
                    'mta' => $fail2banSetting['mta'],
                    'protocol' => $fail2banSetting['protocol'],
                    'port' => $fail2banSetting['port'],
                    'banaction' => $fail2banSetting['banaction'],
                ];
            } elseif ($key === 'jails') {
                foreach ($fail2banSetting as $key => $jail) {
                    if ($key === 'sshd') {
                        $settingsArr['jail'][$key] = [
                            'enabled' => $jail['enabled'],
                            'port' => $jail['port'],
                            'filter' => $jail['filter'],
                            'findtime' => $jail['findtime'],
                            'bantime' => $jail['bantime'],
                            'banaction' => $jail['banaction'],
                            'maxretry' => $jail['maxretry'],
                            'logpath' => $jail['logpath']
                        ];
                    } elseif ($key === 'apache') {
                        $settingsArr['jail'][$key] = [
                            'enabled' => $jail['enabled'],
                            'port' => $jail['port'],
                            'filter' => $jail['filter'],
                            'findtime' => $jail['findtime'],
                            'bantime' => $jail['bantime'],
                            'maxretry' => $jail['maxretry'],
                            'logpath' => $jail['logpath']
                        ];
                    } elseif ($key === 'vsftpd') {
                        $settingsArr['jail'][$key] = [
                            'enabled' => $jail['enabled'],
                            'port' => $jail['port'],
                            'filter' => $jail['filter'],
                            'findtime' => $jail['findtime'],
                            'bantime' => $jail['bantime'],
                            'banaction' => $jail['banaction'],
                            'maxretry' => $jail['maxretry'],
                            'logpath' => $jail['logpath']
                        ];
                    }
                }
            }
        }
        return $settingsArr;
    }
}
