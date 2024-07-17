<?php

namespace App\Services\FileManager\Traits;

//use Alexusmai\LaravelFileManager\Services\ACLService\ACL;
//use Illuminate\Support\Facades\Storage;
//use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
//use League\Flysystem\FilesystemException;

trait ContentTrait
{

    /**
     * Get data for the initilizer
     *
     *
     * @return array
     * @throws FilesystemException
     */
    public function getInitiliazieConfig(): array
    {
        $initialize = [
            'acl' => config('file-manager.acl'),
            "leftDisk" => config('file-manager.leftDisk'),
            "rightDisk" => config('file-manager.rightDisk'),
            "leftPath" => config('file-manager.leftPath'),
            "rightPath" => config('file-manager.rightPath'),
            "windowsConfig" => config('file-manager.windowsConfig'),
            "hiddenFiles" => config('file-manager.hiddenFiles'),
            "disks" => [
                "public" => [
                    "driver" => config('file-manager.driver'),
                ]
            ],
            "lang" => config('file-manager.lang'),
        ];
        return $initialize;
    }

    /**
     * Get content for the selected disk and path
     * @param $storage
     * @param null $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function getContent($storage, $path = null): array
    {
        $content = $storage->listContents($path ?: '')->toArray();

        $directories = $this->filterDir($content);
        $files = $this->filterFile($content);

        return compact('directories', 'files');
    }

    /**
     * Get directories with properties
     *
     * @param $storage
     * @param null $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function directoriesWithProperties($storage, $path = null): array
    {
        $content = $storage->listContents($path ?: '')->toArray();

        return $this->filterDir($content);
    }

    /**
     * Get files with properties
     *
     * @param       $disk
     * @param null $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function filesWithProperties($disk, $path = null): array
    {
        $content = Storage::disk($disk)->listContents($path ?: '');

        return $this->filterFile($disk, $content);
    }

    /**
     * Get directories for tree module
     * @param null $storage
     * @param null $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function getDirectoriesTree($storage, $path = null): array
    {
        $directories = $this->directoriesWithProperties($storage, $path);
        foreach ($directories as $index => $dir) {
            $directories[$index]['props'] = [
                'hasSubdirectories' => (bool)$storage->directories($dir['path']),
            ];
        }

        return $directories;
    }

    /**
     * File properties
     *
     * @param $storage
     * @param $path
     *
     * @return mixed
     */
    public function fileProperties($storage, $path = null): mixed
    {
        $pathInfo = pathinfo($path);

        $properties = [
            'type' => 'file',
            'path' => $path,
            'basename' => $pathInfo['basename'],
            'dirname' => $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'],
            'extension' => $pathInfo['extension'] ?? '',
            'filename' => $pathInfo['filename'],
            'size' => $storage->size($path),
            'timestamp' => $storage->lastModified($path),
            'visibility' => $storage->getVisibility($path),
        ];

        return $properties;
    }

    /**
     * Get properties for the selected directory
     *
     * @param $storage
     * @param null $path
     *
     * @return array|false
     */
    public function directoryProperties($storage, $path = null): bool|array
    {
        $adapter = $storage->getAdapter();

        $pathInfo = pathinfo($path);

        $properties = [
            'type' => 'dir',
            'path' => $path,
            'basename' => $pathInfo['basename'],
            'dirname' => $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'],
            'timestamp' => $adapter instanceof AwsS3V3Adapter ? null : $storage->lastModified($path),
            'visibility' => $adapter instanceof AwsS3V3Adapter ? null : $storage->getVisibility($path),
        ];

        return $properties;
    }

    /**
     * Get only directories
     *
     * @param $content
     *
     * @return array
     */
    protected function filterDir($content): array
    {
        // select only dir
        $dirsList = array_filter($content, fn($item) => $item['type'] === 'dir');

        $dirs = array_map(function ($item) {
            $pathInfo = pathinfo($item['path']);

            return [
                'type' => $item['type'],
                'path' => $item['path'],
                'basename' => $pathInfo['basename'],
                'dirname' => $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'],
                'timestamp' => $item['lastModified'],
                'visibility' => $item['visibility'],
            ];
        }, $dirsList);


        return array_values($dirs);
    }

    /**
     * Get only files
     *
     * @param $content
     *
     * @return array
     */
    protected function filterFile($content): array
    {
        $filesList = array_filter($content, fn($item) => $item['type'] === 'file');

        $files = array_map(function ($item) {
            $pathInfo = pathinfo($item['path']);

            return [
                'type' => $item['type'],
                'path' => $item['path'],
                'basename' => $pathInfo['basename'],
                'dirname' => $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'],
                'extension' => $pathInfo['extension'] ?? '',
                'filename' => $pathInfo['filename'],
                'size' => $item['fileSize'],
                'timestamp' => $item['lastModified'],
                'visibility' => $item['visibility'],
            ];
        }, $filesList);

        return array_values($files);
    }

    /**
     * ACL filter
     *
     * @param $disk
     * @param $content
     *
     * @return mixed
     */
    protected function aclFilter($disk, $content): mixed
    {
        $acl = resolve(ACL::class);

        $withAccess = array_map(function ($item) use ($acl, $disk) {
            // add acl access level
            $item['acl'] = $acl->getAccessLevel($disk, $item['path']);

            return $item;
        }, $content);

        // filter files and folders
        if ($this->configRepository->getAclHideFromFM()) {
            return array_filter($withAccess, function ($item) {
                return $item['acl'] !== 0;
            });
        }

        return $withAccess;
    }
}
