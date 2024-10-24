<?php

namespace App\Jobs\Traits;

trait Bind9BuildTrait
{
    public function updateSystemFile($filePath, $fileContent) {
        file_put_contents($filePath, $fileContent);
    }

}
