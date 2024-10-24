<?php

namespace App\Models\HostingSubscription;

use App\Models\Customer;
use App\Models\Traits\DiskUsageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sushi\Sushi;

class DiskUsage extends Model
{
    use HasFactory, Sushi, DiskUsageTrait;

    protected static string $rootPath;
    protected static string $path;

    protected $fillable = [
        'location',
        'size',
        'disk_usage',
    ];

    protected array $schema = [
        'location' => 'string',
        'size' => 'string',
        'disk_usage' => 'string',
    ];

    public static function boot()
    {
        parent::boot();
        static::diskUsageBoot();
    }

    public static function diskUsageBoot()
    {

    }

    public static function queryForDiskAndPath(string $rootPath = 'public', string $path = ''): Builder
    {
        static::$rootPath = $rootPath;
        static::$path = $path;
        return static::query();
    }

    public function isPreviousPath(): bool
    {
        return $this->name === 'Up One Level';
    }

    public function isFolder(): bool
    {
        return $this->type === 'Folder'
            && is_dir($this->storageInstance()->path($this->path));
    }

    public function storageInstance()
    {
        return Storage::build([
            'driver' => 'local',
            'throw' => false,
            'root' => static::$rootPath,
        ]);
    }

    public function canOpen(): bool
    {
        return $this->type !== 'Folder'
            && $this->storageInstance()->exists($this->path)
            && $this->storageInstance()->getVisibility($this->path) === FilesystemContract::VISIBILITY_PUBLIC;
    }

    public function getRows(): array
    {
        $backPath = [];
        if (self::$path) {
            $path = Str::of(self::$path)->explode('/');

//            $backPath = [
//                [
//                    'directory' => 'Up One Level',
//                    'username' => null,
//                    'authorized_users' => null,
//                    'password' => null,
//                    'protected' => null,
//                    'label' => null,
//                    'type' => 'Folder',
//                    'path' => $path->count() > 1 ? $path->take($path->count() - 1)->join('/') : '',
//                ],
//            ];
        }

        $storage = $this->storageInstance();

        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $directoryPath = "/home/{$hostingSubscription->system_username}/";

        $directories = collect($storage->directories(static::$path))
            ->sort()
            ->reject(function($directory) {
                return basename($directory)[0] === '.';
            })
            ->map(function (string $directory) use ($storage, $directoryPath) {
                $dirData = $this->getDirSize($directoryPath . $directory);
                return [
                    'location' => $directory . '/',
                    'size' => $dirData['size'],
                    'disk_usage' => $dirData['disk_usage']
                ];
            });

        $diskUsageRecords = collect([
            [
                'location' => 'Files in home directory',
                'size' => $this->getDirSize($directoryPath)['size'],
                'disk_usage' => $this->getDirSize($directoryPath)['disk_usage'],
            ],
            [
                'location' => 'Files in hidden subdirectories',
                'size' => $this->getHiddenDirSize($directoryPath)['size'],
                'disk_usage' => $this->getHiddenDirSize($directoryPath)['disk_usage'],
            ],
            [
                'location' => 'Databases',
                'size' => $this->getDatabasesSize($hostingSubscription->id)['size'],
                'disk_usage' => $this->getDatabasesSize($hostingSubscription->id)['disk_usage']
            ],
            [
                'location' => 'Mailing Lists',
                'size' => $this->getMailingListsSize()['size'],
                'disk_usage' => $this->getMailingListsSize()['disk_usage']
            ],
            [
                'location' => 'Email Archives',
                'size' => $this->getEmailArchivesSize()['size'],
                'disk_usage' => $this->getEmailArchivesSize()['disk_usage']
            ],
            [
                'location' => 'Email Accounts†',
                'size' => $this->getEmailAccountsSize($directoryPath)['size'],
                'disk_usage' => $this->getEmailAccountsSize($directoryPath)['disk_usage']
            ],
            [
                'location' => 'Other Usage‡',
                'size' => $this->getOtherUsageSize($directoryPath)['size'],
                'disk_usage' => $this->getOtherUsageSize($directoryPath)['disk_usage']
            ]
        ]);

        $diskUsageRecords->splice(2, 0, $directories->all());
        return collect($backPath)
            ->push(...$diskUsageRecords)
            ->toArray();
    }
}
