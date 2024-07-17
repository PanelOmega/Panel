<?php

namespace App\Services\FileManager\ZipService;

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

        if ($this->createArchive()) {
            return [
                'result' => [
                    'status' => 'success',
                    'message' => null
                ]
            ];
        }

        return [
            'result' => [
                'status' => 'warning',
                'message' => 'zipError'
            ],
        ];
    }

    public function extract(Request $request): array
    {
        $this->request = $request;

        if ($this->extractArchive()) {
            return [
                'result' => [
                    'status' => 'success',
                    'message' => null
                ]
            ];
        }

        return [
            'result' => [
                'status' => 'warning',
                'message' => 'zipError'
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

        $zipCreate = $this->zip->open($this->createName(), ZIPARCHIVE::OVERWRITE | ZIPARCHIVE::CREATE);

        if ($zipCreate) {
            if ($elements['files']) {
                foreach ($elements['files'] as $file) {
                    $this->zip->addFile($this->prefixer($file), basename($file));
                }
            }

            if ($elements['directories']) {
                $this->addDirs($elements['directories']);
            }

            $this->zip->close();

            return true;
        }

        return false;

    }

    protected function extractArchive(): bool
    {
        $zipPath = $this->prefixer($this->request->get('path', '/'));

        $rootPath = dirname($zipPath);
        $folder = $this->request->input('folder');

        if ($this->zip->open($zipPath)) {
            $this->zip->extractTo($folder ? $rootPath . '/' . $folder : $rootPath);
            $this->zip->close();

            return true;
        }

        return false;
    }

    protected function addDirs(array $directories): void
    {
        foreach ($directories as $directory) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->prefixer($directory)),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($this->fullPath($this->request->get('path', '/'))));
            }

            if (!$file->isDir()) {
                $this->zip->addFile($filePath, $relativePath);
            } else {
                if (!glob($filePath . '/*')) {
                    $this->zip->addEmptyDir($relativePath);
                }
            }
        }
    }

    protected function createName(): string
    {
        $path = $this->request->input('path') === null ? '' : $this->request->input('path');
        return $this->fullPath($path) . $this->request->get('name');
    }

    protected function fullPath(string $path): string
    {
        return $path ? $this->prefixer($path) . '/' : $this->prefixer('');
    }
}
