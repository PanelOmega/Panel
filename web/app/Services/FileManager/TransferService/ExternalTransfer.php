<?php

namespace App\Services\FileManager\ransferService;

use Alexusmai\LaravelFileManager\Traits\PathTrait;
use League\Flysystem\FilesystemException;
use League\Flysystem\MountManager;

class ExternalTransfer extends Transfer
{
    use PathTrait;

    /**
     * @var MountManager
     */
    public $manager;

    /**
     * ExternalTransfer constructor.
     *
     * @param $storage
     * @param $path
     * @param $clipboard
     */
    public function __construct($storage, $path, $clipboard)
    {
        parent::__construct($storage, $path, $clipboard);

        $this->manager = new MountManager([
            'from' => $this->storage->getDriver(),
            'to' => $this->storage->getDriver(),
        ]);
    }

    /**
     * Copy files and folders
     *
     * @return void
     * @throws FilesystemException
     */
    protected function copy()
    {
        // files
        foreach ($this->clipboard['files'] as $file) {
            $this->copyToDisk(
                $file,
                $this->renamePath($file, $this->path)
            );
        }

        // directories
        foreach ($this->clipboard['directories'] as $directory) {
            $this->copyDirectoryToDisk($directory);
        }
    }

    /**
     * Cut files and folders
     *
     * @return void
     * @throws FilesystemException
     */
    protected function cut()
    {
        // files
        foreach ($this->clipboard['files'] as $file) {
            $this->moveToDisk(
                $file,
                $this->renamePath($file, $this->path)
            );
        }

        // directories
        foreach ($this->clipboard['directories'] as $directory) {
            $this->copyDirectoryToDisk($directory);

            // remove directory
            $this->storage->deleteDirectory($directory);
        }
    }

    /**
     * Copy directory to another disk
     *
     * @param $directory
     *
     * @return void
     * @throws FilesystemException
     */
    protected function copyDirectoryToDisk($directory)
    {
        // get all directories in this directory
        $allDirectories = $this->storage->allDirectories($directory);

        $partsForRemove = count(explode('/', $directory)) - 1;

        // create this directories
        foreach ($allDirectories as $dir) {
            $this->storage->makeDirectory(
                $this->transformPath($dir, $this->path, $partsForRemove)
            );
        }

        // get all files
        $allFiles = $this->storage->allFiles($directory);

        // copy files
        foreach ($allFiles as $file) {
            $this->copyToDisk($file,
                $this->transformPath($file, $this->path, $partsForRemove));
        }
    }

    /**
     * Copy files to disk
     *
     * @param $filePath
     * @param $newPath
     *
     * @return void
     * @throws FilesystemException
     */
    protected function copyToDisk($filePath, $newPath)
    {
        $this->manager->copy(
            'from://' . $filePath,
            'to://' . $newPath
        );
    }

    /**
     * Move files to disk
     *
     * @param $filePath
     * @param $newPath
     *
     * @return void
     * @throws FilesystemException
     */
    protected function moveToDisk($filePath, $newPath)
    {
        $this->manager->move(
            'from://' . $filePath,
            'to://' . $newPath
        );
    }
}
