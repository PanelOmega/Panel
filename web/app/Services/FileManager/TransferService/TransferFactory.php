<?php

namespace App\Services\FileManager\TransferService;

use App\Services\FileManager\ransferService\ExternalTransfer;

class TransferFactory
{
    /**
     * @param $path
     * @param $path
     * @param $clipboard
     *
     * @return ExternalTransfer|LocalTransfer
     */
    public static function build($storage, $path, $clipboard)
    {
        if ($clipboard['disk'] !== 'public') {
            return new ExternalTransfer($storage, $path, $clipboard);
        }

        return new LocalTransfer($storage, $path, $clipboard);
    }
}
