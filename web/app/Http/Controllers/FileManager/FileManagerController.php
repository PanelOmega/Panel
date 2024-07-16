<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Server\FileManager\FileManager;
use Illuminate\Http\JsonResponse;

class FileManagerController extends Controller
{

    public function initialize(): JsonResponse
    {
        return FileManager::initialize();
    }

    public function tree()
    {
        return FileManager::tree();
    }

    public function content()
    {
        return FileManager::content();
    }


}
