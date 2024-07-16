<?php

namespace App\Server\FileManager;

use App\Models\HostingSubscription;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Finder\Finder;

class FileManager
{
    public static function initialize()
    {
        return response()->json([
            "result" => [
                "status" => "success",
                "message" => null
            ],
            "config" => [
                "acl" => false,
                "leftDisk" => null,
                "rightDisk" => null,
                "leftPath" => null,
                "rightPath" => null,
                "windowsConfig" => 2,
                "hiddenFiles" => true,
                "disks" => [
                    "public" => [
                        "driver" => "local"
                    ]
                ],
                "lang" => "en"
            ]
        ]);
    }

    public static function tree()
    {
        $userId = Auth::guard('customer')->user()->id;

        $hostingSubscription = HostingSubscription::where('customer_id', $userId)->first();

        if (!$hostingSubscription) {
            throw new \Exception("Hosting subscripton doesn't exist");
        }

        $rootPath = '/home/' . $hostingSubscription->system_username;
        $dirs = [];
        $systemDirs = ['apache2', 'templates'];

        $finder = new Finder();

        $finder->directories()->in($rootPath);

        foreach ($finder as $directory) {

            if (!in_array($directory->getBasename(), $systemDirs)) {

                $hasSubdirectories = (!empty(glob($directory->getRealPath() . '/*', GLOB_ONLYDIR)));
                $isVisible = $directory->isReadable() ? 'public' : 'private';

                $dirs[] = [
                    'type' => $directory->getType(),
                    'path' => $directory->getRealPath(),
                    'basename' => $directory->getBasename(),
                    'dirname' => $directory->getRealPath(),
                    'timestamp' => $directory->getMTime(),
                    'visibility' => $isVisible,
                    'props' => [
                        'hasSubdirectories' => $hasSubdirectories
                    ]
                ];
            }
        }

        return response()->json([
            "result" => [
                "status" => "success",
                "message" => null
            ],
            "directories" => $dirs
        ]);
    }

    public static function content()
    {

        $userId = Auth::guard('customer')->user()->id;

        $hostingSubscription = HostingSubscription::where('customer_id', $userId)->first();

        if (!$hostingSubscription) {
            throw new \Exception("Hosting subscripton doesn't exist");
        }

        $rootPath = '/home/' . $hostingSubscription->system_username;
        $dirs = [];
        $systemDirs = ['apache2', 'templates'];

        $finder = new Finder();
        $finder->files()->in($rootPath);

        dd($finder);
        foreach ($finder as $directory) {

            if (!in_array($directory->getBasename(), $systemDirs)) {

                $hasSubdirectories = (!empty(glob($directory->getRealPath() . '/*', GLOB_ONLYDIR)));
                $isVisible = $directory->isReadable() ? 'public' : 'private';

                $dirs[] = [
                    'type' => $directory->getType(),
                    'path' => $directory->getRealPath(),
                    'basename' => $directory->getBasename(),
                    'dirname' => $directory->getRealPath(),
                    'timestamp' => $directory->getMTime(),
                    'visibility' => $isVisible,
                    'props' => [
                        'hasSubdirectories' => $hasSubdirectories
                    ]
                ];
            }
        }


        return response()->json([
            "result" => [
                "status" => "success",
                "message" => null
            ],
            "directories" => [
                [
                    "type" => "dir",
                    "path" => "wow",
                    "basename" => "wow",
                    "dirname" => "",
                    "timestamp" => 1721045452,
                    "visibility" => "public"
                ]
            ],
            "files" => [
                [
                    "type" => "file",
                    "path" => "dd.xte",
                    "basename" => "dd.xte",
                    "dirname" => "",
                    "extension" => "xte",
                    "filename" => "dd",
                    "size" => 0,
                    "timestamp" => 1721045349,
                    "visibility" => "public"
                ],
                [
                    "type" => "file",
                    "path" => "Screen Shot 2024-07-05 at 16.58.25.png",
                    "basename" => "Screen Shot 2024-07-05 at 16.58.25.png",
                    "dirname" => "",
                    "extension" => "png",
                    "filename" => "Screen Shot 2024-07-05 at 16.58.25",
                    "size" => 127606,
                    "timestamp" => 1721045499,
                    "visibility" => "public"
                ],
                [
                    "type" => "file",
                    "path" => "Screen Shot 2024-07-11 at 00.50.01.png",
                    "basename" => "Screen Shot 2024-07-11 at 00.50.01.png",
                    "dirname" => "",
                    "extension" => "png",
                    "filename" => "Screen Shot 2024-07-11 at 00.50.01",
                    "size" => 240657,
                    "timestamp" => 1721045483,
                    "visibility" => "public"
                ]
            ]
        ]);
    }
}
