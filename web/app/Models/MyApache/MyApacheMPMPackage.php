<?php

namespace App\Models\MyApache;

use App\Models\MyApacheProfile;
use App\Server\Helpers\OS;
use Illuminate\Database\Eloquent\Model;

class MyApacheMPMPackage extends MyApachePackage
{
    public function getRows()
    {
        $findMyApacheProfile = MyApacheProfile::find(static::$myApacheProfileId);

        $scanDir = scandir('/etc/my-apache/modules');
        $modules = [];
        foreach ($scanDir as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file = str_replace('.so', '', $file);
            $isEnabled = false;

            if ($findMyApacheProfile) {
                $packages = $findMyApacheProfile->packages;
                if (in_array($file, $packages)) {
                    $isEnabled = true;
                }
            }

            if (!str_contains($file, 'mod_mpm_')) {
                continue;
            }

            $modules[] = [
                'id' => count($modules) + 1,
                'name' => $file,
                'description' => 'No description',
                'source' => 'PanelOmega',
                'is_enabled' => $isEnabled,
            ];
        }

        return $modules;

    }
}
