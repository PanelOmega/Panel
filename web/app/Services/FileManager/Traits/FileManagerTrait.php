<?php

namespace App\Services\FileManager\Traits;

use Illuminate\Support\Facades\Storage;

trait FileManagerTrait
{

    public function getDirectoriesTree(string $path)
    {
        $directories = $this->directoriesWithProperties($disk, $path);

        foreach ($directories as $index => $dir) {
            $directories[$index]['props'] = [
                'hasSubdirectories' => (bool)Storage::disk($disk)->directories($dir['path']),
            ];
        }

        return $directories;
    }

}
