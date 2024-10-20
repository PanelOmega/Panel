<?php

namespace App\Models\MyApache;

use App\Models\MyApacheProfile;
use App\Server\Helpers\OS;
use Illuminate\Database\Eloquent\Model;

class MyApachePHPExtension extends MyApachePackage
{
    public function getRows()
    {

        $modules = [];
        $modules[] = [
            'id' => count($modules) + 1,
            'name' => 'php_dx',
            'description' => 'No description',
            'source' => 'PanelOmega',
            'is_enabled' => 1,
        ];
        $modules[] = [
            'id' => count($modules) + 1,
            'name' => 'php_oooo',
            'description' => 'No description',
            'source' => 'PanelOmega',
            'is_enabled' => 1,
        ];

        return $modules;

    }
}
