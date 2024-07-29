<?php

namespace App\Services\Fail2Ban\Fail2BanWhitelistIp;

use App\Jobs\Fail2BanConfigBuild;

class Fail2BanWhitelistIpService
{
    public static function updateWhitelistedIps()
    {
        $fail2banConfig = new Fail2BanConfigBuild();
        $fail2banConfig->handle();
    }
}
