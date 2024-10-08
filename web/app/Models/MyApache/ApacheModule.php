<?php

namespace App\Models\MyApache;

use App\Server\Helpers\OS;
use Illuminate\Database\Eloquent\Model;

class ApacheModule extends Model
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
    public function getRows()
    {
        $scanDir = scandir('/etc/my-apache/modules');
        $modules = [];
        foreach ($scanDir as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file = str_replace('.so', '', $file);
            $modules[] = [
                'id' => count($modules) + 1,
                'name' => $file,
                'description' => 'No description',
                'source' => 'PanelOmega',
                'is_enabled' => true,
            ];
        }

        return $modules;

    }
}
