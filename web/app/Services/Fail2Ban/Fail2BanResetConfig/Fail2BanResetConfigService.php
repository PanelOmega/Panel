<?php

namespace App\Services\Fail2Ban\Fail2BanResetConfig;

class Fail2BanResetConfigService
{

    public static function resetConfig(): array {
        $settings = setting('fail2ban');

        $settingsArr = [];
        foreach ($settings['config'] as $key => $fail2banSetting) {
            if ($key === 'general') {
                $settingsArr[$key] = [
                    'bantime' => null,
                    'bantime_option' => null,
                    'ignorecommand' => null,
                    'findtime' => null,
                    'findtime_option' => null,
                    'maxretry' => null,
                    'backend' => null,
                    'usedns' => null,
                    'logencoding' => null,
                    'enabled' => null
                ];
            } elseif ($key === 'action') {
                $settingsArr[$key] = [
                    'destemail' => null,
                    'sender' => null,
                    'mta' => null,
                    'protocol' => null,
                    'port' => null,
                    'banaction' => null,
                ];
            } elseif ($key === 'jails') {
                foreach ($fail2banSetting as $key => $jail) {
                    if ($key === 'sshd') {
                        $settingsArr['jail'][$key] = [
                            'enabled' => null,
                            'port' => null,
                            'filter' => null,
                            'findtime' => null,
                            'bantime' => null,
                            'banaction' => null,
                            'maxretry' => null,
                            'logpath' => null
                        ];
                    } elseif ($key === 'apache') {
                        $settingsArr['jail'][$key] = [
                            'enabled' => null,
                            'port' => null,
                            'filter' => null,
                            'findtime' => null,
                            'bantime' => null,
                            'maxretry' => null,
                            'logpath' => null
                        ];
                    } elseif ($key === 'vsftpd') {
                        $settingsArr['jail'][$key] = [
                            'enabled' => null,
                            'port' => null,
                            'filter' => null,
                            'findtime' => null,
                            'bantime' => null,
                            'banaction' => null,
                            'maxretry' => null,
                            'logpath' => null
                        ];
                    }
                }
            }
        }

        return $settingsArr;
    }

}
