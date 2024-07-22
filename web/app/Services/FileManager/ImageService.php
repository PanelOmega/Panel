<?php

namespace App\Services\FileManager;

use Illuminate\Support\Facades\Facade;

class ImageService extends Facade
{

    public static function getFacadeAccessor()
    {
        return 'image';
    }

}
