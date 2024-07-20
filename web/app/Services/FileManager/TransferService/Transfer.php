<?php

namespace App\Services\FileManager\TransferService;

abstract class Transfer
{
    public $path;
    public $clipboard;
    public $storage;

    /**
     * Transfer constructor.
     *
     * @param $storage
     * @param $path
     * @param $clipboard
     */
    public function __construct($storage, $path, $clipboard)
    {
        $this->path = $path;
        $this->clipboard = $clipboard;
        $this->storage = $storage;
    }

    /**
     * Transfer files and folders
     *
     * @return array
     */
    public function filesTransfer(): array
    {
        try {
            if ($this->clipboard['type'] === 'copy') {
                $this->copy();
            } elseif ($this->clipboard['type'] === 'cut') {
                $this->cut();
            }
        } catch (\Exception $exception) {
            return [
                'result' => [
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ],
            ];
        }

        return [
            'result' => [
                'status' => 'success',
                'message' => 'copied',
            ],
        ];
    }

    abstract protected function copy();

    abstract protected function cut();
}
