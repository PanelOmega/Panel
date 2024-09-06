<?php

namespace App\Jobs\Traits;

trait VsftpdConfigBuildTrait
{
    public function updateSystemFile($filePath, $newContent) {
        file_put_contents($filePath, $newContent);
    }
}
