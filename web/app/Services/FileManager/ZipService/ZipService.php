<?php

namespace App\Services\FileManager\ZipService;

use App\Services\FileManager\PermissionsService\PermissionsManager;
use Illuminate\Http\Request;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use ZipArchive;

class ZipService
{
    private $zip;
    private $request;
    private $storage;

    public function __construct(ZipArchive $zip, $storage)
    {
        $this->zip = $zip;
        $this->storage = $storage;
    }

    public function create(Request $request): array
    {
        $this->request = $request;

        if (!$this->createArchive()) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'zipError'
                ],
            ];
        }

        $path = $this->request->get('path') ?? '' . $this->request->input('name');

        if (!PermissionsManager::setPermissions($path)) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'permissionsNotSet'
                ],
            ];
        }

        return [
            'result' => [
                'status' => 'success',
                'message' => null
            ]
        ];
    }

    public function extract(Request $request): array
    {
        $this->request = $request;

        if (!$this->extractArchive()) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'zipError'
                ]
            ];
        }

        $directory = dirname($this->request->get('path')) ?? '';
        $folder = $this->request->input('folder');
        $path = $directory . '/' . $folder;

        if (!PermissionsManager::setUnzipPermissions($path)) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'permissionsNotSet'
                ],
            ];
        }

        return [
            'result' => [
                'status' => 'success',
                'message' => null
            ]
        ];
    }

    protected function prefixer($path): string
    {
        return $this->storage->path($path);
    }

    public function createArchive(): bool
    {
        $elements = $this->request->input('elements');
        $archiveName = $this->createName();

        $zipCreate = $this->zip->open($archiveName, ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE);

        if (!$zipCreate) {
            return false;
        }

        if (!empty($elements['files'])) {
            foreach ($elements['files'] as $file) {
                $this->zip->addFile($this->prefixer($file), basename($file));
            }
        }

        if (!empty($elements['directories'])) {
            $this->addDirs($elements['directories']);
        }

        $this->zip->close();
        return true;
    }

    protected function extractArchive(): bool
    {
        $zipPath = $this->prefixer($this->request->get('path', '/'));

        if (!$this->zip->open($zipPath)) {
            return false;
        }

        $rootPath = dirname($zipPath);
        $folder = $this->request->input('folder');
        $extractPath = $folder ? $rootPath . '/' . $folder : $rootPath;

        $this->zip->extractTo($extractPath);
        $this->zip->close();

        return true;
    }

    protected function addDirs(array $directories): void
    {
        foreach ($directories as $directory) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->prefixer($directory),
                    RecursiveIteratorIterator::LEAVES_ONLY
                ));

            foreach ($files as $name => $file) {
                $filePath = $file->getRealPath();
                $path = $this->request->get('path') ?? '';
                $relativePath = substr($filePath, strlen($this->fullPath($path)));

                if (!$file->isDir()) {
                    $this->zip->addFile($filePath, $relativePath);
                } else {
                    if (!glob($filePath . '/*')) {
                        $this->zip->addEmptyDir($relativePath);
                    }
                }
            }
        }
    }

    protected function createName(): string
    {
        $path = $this->request->input('path') === null ? '' : $this->request->input('path');
        return $this->fullPath($path) . preg_replace('/\\\\/', '/', $this->request->input('name'));
    }

    protected function fullPath(string $path): string
    {
        return $path ? $this->prefixer($path) . '/' : $this->prefixer('');
    }
}
