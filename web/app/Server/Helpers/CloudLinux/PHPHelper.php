<?php

namespace App\Server\Helpers\CloudLinux;

class PHPHelper
{
    public function getSupportedPHPVersions()
    {
        $output = shell_exec('selectorctl --list --json');
        $decoded = json_decode($output, true);

        return $decoded;
    }

}
