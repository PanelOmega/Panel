<?php

namespace App\Services\FileManager;

use App\Models\Customer;
use App\Services\FileManager\ImageService\ImageService;
use App\Services\FileManager\PermissionsService\PermissionsManager;
use App\Services\FileManager\Traits\CheckTrait;
use App\Services\FileManager\Traits\ContentTrait;
use App\Services\FileManager\Traits\PathTrait;
use App\Services\FileManager\TransferService\TransferFactory;
use App\Services\FileManager\ZipService\ZipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManager
{

    use PathTrait, ContentTrait, CheckTrait;

    private $storage;
    private $zipService;

    public function __construct(\ZipArchive $zip)
    {
        $this->storage = $this->storageInstance();
        $this->zipService = new ZipService($zip, $this->storage);
    }

    public function initialize()
    {

        if (!config()->has('file-manager')) {
            return [
                'result' => [
                    'status' => 'danger',
                    'message' => 'noConfig',
                ],
            ];
        }

        $config = $this->getInitiliazieConfig();

        return [
            "result" => [
                "status" => "success",
                "message" => null
            ],
            "config" => $config
        ];
    }

    public function tree($path)
    {
        $directories = $this->getDirectoriesTree($this->storage, $path);

        return [
            'result' => [
                'status' => 'success',
                'message' => null,
            ],
            'directories' => $directories,
        ];
    }

    public function content($path)
    {

        $content = $this->getContent($this->storage, $path);

        return [
            'result' => [
                'status' => 'success',
                'message' => null,
            ],
            'directories' => $content['directories'],
            'files' => $content['files'],
        ];
    }

    public function createFile(Request $request): array
    {
        $path = $this->newPath($request->get('path', '/'), $request->input('name'));

        if ($this->storage->exists($path)) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'fileExist',
                ],
            ];
        }

        $this->storage->put($path, '');
        $permissionsSet = PermissionsManager::setFilePermissions($path);

        if ($permissionsSet) {

            $fileProperties = $this->fileProperties($this->storage, $path);

            return [
                'result' => [
                    'status' => 'success',
                    'message' => 'fileCreated',
                    'path' => $path
                ],
                'file' => $fileProperties,
            ];

        }

        return [
            'result' => [
                'status' => 'warning',
                'message' => 'permissionsNotSet',
            ]
        ];

    }

    public function updateFile(Request $request): array
    {
        $file = $request->file('file');
        $path = $request->input('path');

        $this->storage->putFileAs($path, $file, $file->getClientOriginalName());
        $filePath = $this->newPath($path, $file->getClientOriginalName());
        $fileProperties = $this->fileProperties($this->storage, $filePath);

        return [
            'result' => [
                'status' => 'success',
                'message' => 'fileUpdated',
            ],
            'file' => $fileProperties,
        ];
    }

    public function createDirectory(Request $request): array
    {

        $path = $this->newPath($request->get('path', '/'), $request->get('name'));

        if ($this->storage->exists($path)) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'dirExist',
                ]
            ];
        }

        $this->storage->makeDirectory($path);

        $permissionsSet = PermissionsManager::setDirectoryPermissions($path, $this->storage);

        if ($permissionsSet) {

            $dirProperties = $this->directoryProperties($this->storage, $path);

            $tree = $dirProperties;
            $tree['props'] = ['hasSubdirectories' => false];

            return [
                'result' => [
                    'status' => 'success',
                    'message' => 'dirCreated',
                ],
                'directory' => $dirProperties,
                'tree' => [$tree],
            ];
        }

        return [
            'result' => [
                'status' => 'warning',
                'message' => 'permissionsNotSet',
            ]
        ];
    }

    public function upload(Request $request): array
    {

        $path = $request->get('path', '/');
        $files = $request->file('files');
        $overwrite = $request->input('overwrite');

        $fileNotUploaded = false;
        $maxUploadSize = config('file-manager.get_max_upload_file_size');
        $allowedFileTypes = config('file-manager.get_allowed_file_types');
        $slugifyNames = config('file-manager.file-manager.slugify_names');
        $bites = 1048576;

        foreach ($files as $file) {

            if (!$overwrite && $this->storage->exists($path . '/' . $file->getClientOriginalName())) {
                continue;
            }

            if ($maxUploadSize && $file->getSize() / $bites > $maxUploadSize) {
                $fileNotUploaded = true;
                continue;
            }

            if ($allowedFileTypes && !in_array($file->getClientOriginalExtension(), $allowedFileTypes)) {
                $fileNotUploaded = true;
                continue;
            }

            $name = $file->getClientOriginalName();

            if ($slugifyNames) {
                $name = Str::slug(
                        Str::replace(
                            '.' . $file->getClientOriginalExtension(),
                            '',
                            $name
                        )
                    ) . '.' . $file->getClientOriginalExtension();
            }

            $this->storage->putFileAs(
                $path,
                $file,
                $name
            );
        }

        if ($fileNotUploaded) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'notAllUploaded',
                ],
            ];
        }

        return [
            'result' => [
                'status' => 'success',
                'message' => 'uploaded',
            ],
        ];
    }

    public function delete($items): array
    {
        $deletedItems = [];

        foreach ($items as $item) {
            if (!$this->storage->exists($item['path'])) {
                continue;
            } else {
                if ($item['type'] === 'dir') {
                    $this->storage->deleteDirectory($item['path']);
                } else {
                    $this->storage->delete($item['path']);
                }
            }

            $deletedItems[] = $item;
        }

        return [
            'result' => [
                'status' => 'success',
                'message' => 'deleted',
            ],
        ];
    }

    public function paste(Request $request): array
    {

        $transferService = TransferFactory::build($this->storage, $request->get('path', '/'), $request->input('clipboard'));

        return $transferService->filesTransfer();
    }

    public function rename(Request $request): array
    {

        $oldName = $request->input('oldName');
        $newName = $request->input('newName');

        $directory = dirname($oldName);
        $newFullName = $directory . '/' . $newName;

        $this->storage->move($oldName, $newFullName);

        return [
            'result' => [
                'status' => 'success',
                'message' => 'renamed',
            ],
        ];
    }

    public function download(Request $request): StreamedResponse
    {

        $path = $request->get('path', '/');
        $filename = basename($path);

        if (!Str::isAscii($filename)) {
            $filename = Str::ascii($filename);
        }

        return $this->storage->download($path, $filename);
    }

    public function preview(Request $request)
    {

        $path = $request->get('path', '/');

        $imageContent = $this->storage->get($path);
        $contentType = $this->storage->mimeType($path);

        return response()->make($imageContent, 200, [
            'Content-Type' => $contentType,
        ]);
    }

    public function thumbnails(Request $request): mixed
    {
        $path = $request->get('path', '/');

        return response()->make(
            Image::read(
                $this->storage->get($path))
                ->coverDown(80, 80)
                ->encode(),
            200,
            ['Content-Type' => $this->storage->mimeType($path)]
        );
    }

    public function url(Request $request): array
    {
        $path = $request->get('path', '/');

        return [
            'result' => [
                'status' => 'success',
                'message' => null
            ],
            'url' => $this->storage->url($path),
        ];
    }

    public function streamFile(Request $request): StreamedResponse
    {
        $path = $request->get('path', '/');

        $filename = basename($path);

        if (!Str::isAscii($filename)) {
            $filename = Str::ascii($filename);
        }

        return $this->storage->response($path, $filename, [
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function zip(Request $request)
    {
        return $this->zipService->create($request);
    }

    public function unzip(Request $request)
    {
        return $this->zipService->extract($request);
    }


    public function storageInstance()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        return Storage::build([
            'driver' => 'local',
            'throw' => false,
            'root' => '/home/' . $hostingSubscription->system_username,
        ]);
    }
}
