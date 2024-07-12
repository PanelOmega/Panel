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

    // cloudlinux-limits set --username daip4454yuwx --cagefs enabled --json

    //cagefsctl --setup-cl-selector

    //yum install governor-mysql

    public function createAdminAccount()
    {
        // /usr/share/cloudlinux/hooks/post_modify_admin.py create --name admin
    }

//    public function createUserAccount()
//
//        // /usr/share/cloudlinux/hooks/post_modify_user.py create --username daip4454yuwx --owner admin
//    }
}
