<?php

namespace App\Services\FileManager;

use App\Models\Customer;
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

    /**
     *
     * @param
     * @return
     */
    public function storageInstance()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        return Storage::build([
            'driver' => 'local',
            'throw' => false,
            'root' => '/home/' . $hostingSubscription->system_username,
        ]);
    }

    /**
     *
     * @param
     * @return array
     */
    public function initialize(): array
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

    /**
     *
     * @param $path
     * @return array
     */
    public function tree($path): array
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

    /**
     *
     * @param $path
     * @return array
     */
    public function content($path): array
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

    /**
     *
     * @param Request $request
     * @return array
     */
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
        $permissionsSet = PermissionsManager::setPermissions($path);

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

    /**
     *
     * @param Request $request
     * @return array
     */
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

    /**
     *
     * @param Request $request
     * @return array
     */
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
        $permissionsSet = PermissionsManager::setPermissions($path);

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

    /**
     *
     * @param Request $request
     * @return array
     */
    public function upload(Request $request): array
    {
        $path = $request->get('path', '/');
        $files = $request->file('files');
        $overwrite = $request->input('overwrite');

        $fileNotUploaded = false;
        $filePermissionsNotSet = false;
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

            $fileFullPath = $path . '/' . $name;

            if (!PermissionsManager::setPermissions($fileFullPath)) {
                $filePermissionsNotSet = true;
            };
        }

        if ($fileNotUploaded) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'notAllUploaded',
                ],
            ];
        }

        if ($filePermissionsNotSet) {
            return [
                'result' => [
                    'status' => 'warning',
                    'message' => 'permissionsNotSet',
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

    /**
     *
     * @param $items
     * @return array
     */
    public function delete($items): array
    {
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
        }

        return [
            'result' => [
                'status' => 'success',
                'message' => 'deleted',
            ],
        ];
    }

    /**
     *
     * @param Request $request
     * @return array
     */
    public function paste(Request $request): array
    {
        $path = $request->get('path', '/');
        $clipboard = $request->input('clipboard');

        $transferService = TransferFactory::build($this->storage, $path, $clipboard);
        return $transferService->filesTransfer();
    }

    /**
     *
     * @param Request $request
     * @return array
     */
    public function rename(Request $request): array
    {
        $oldName = $request->input('oldName');
        $newName = $request->input('newName');
        $this->storage->move($oldName, $newName);

        return [
            'result' => [
                'status' => 'success',
                'message' => 'renamed',
            ],
        ];
    }

    /**
     *
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request): StreamedResponse
    {
        $path = $request->get('path', '/');
        $filename = basename($path);

        if (!Str::isAscii($filename)) {
            $filename = Str::ascii($filename);
        }

        return $this->storage->download($path, $filename);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request): \Illuminate\Http\Response
    {
        $path = $request->get('path', '/');

        $imageContent = $this->storage->get($path);
        $contentType = $this->storage->mimeType($path);

        return response()->make($imageContent, 200, [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
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

    /**
     *
     * @param Request $request
     * @return array
     */
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

    /**
     *
     * @param Request $request
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
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

    /**
     *
     * @param Request $request
     * @return
     */
    public function zip(Request $request)
    {
        return $this->zipService->create($request);
    }

    /**
     *
     * @param Request $request
     * @return
     */
    public function unzip(Request $request)
    {
        return $this->zipService->extract($request);

    }
}
