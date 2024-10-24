<?php

namespace App\Models\MyApache;

use App\Models\MyApacheProfile;
use App\Server\Helpers\OS;
use Illuminate\Database\Eloquent\Model;

class MyApachePackage extends Model
{
    use \Sushi\Sushi;

    protected $fillable = [
        'name',
        'description',
        'source',
        'is_enabled',
    ];

    protected $schema = [
        'id'=>'integer',
        'name'=>'string',
        'description'=>'string',
        'source'=>'string',
        'is_enabled'=>'boolean',
    ];

    public static $myApacheProfileId;

    public static function myApacheProfileIdQuery($myApacheProfileId)
    {
        static::$myApacheProfileId = $myApacheProfileId;
        return static::query();
    }

    public static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            $findMyApacheProfile = MyApacheProfile::find(static::$myApacheProfileId);
            if ($findMyApacheProfile) {
                $packages = $findMyApacheProfile->packages;
                if ($model->is_enabled) {
                    $packages[] = $model->name;
                } else {
                    $key = array_search($model->name, $packages);
                    if ($key !== false) {
                        unset($packages[$key]);
                    }
                }
                $findMyApacheProfile->packages = $packages;
                $findMyApacheProfile->save();
            }
        });
    }

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
