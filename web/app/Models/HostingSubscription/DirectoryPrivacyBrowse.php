<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildDirectoryPrivacy;
use App\Models\Customer;
use App\Models\Traits\HasDirectoryTreeBuild;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sushi\Sushi;

class DirectoryPrivacyBrowse extends Model
{
    use HasFactory, HasDirectoryTreeBuild;

    use Sushi;

    protected static string $rootPath;

    protected static string $path;

    protected $fillable = [
        'directory',
        'username',
        'authorized_users',
        'password',
        'protected',
        'label',
    ];

    protected array $schema = [
        'directory' => 'string',
        'username' => 'string',
        'authorized_users' => 'string',
        'password' => 'string',
        'protected' => 'string',
        'label' => 'string',
        'type' => 'string',
        'path' => 'string'
    ];

    public static function boot()
    {
        parent::boot();
        static::DirectoryPrivacyListFolderBoot();
    }

    public static function DirectoryPrivacyListFolderBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::updating(function ($model) use ($hostingSubscription) {
            $directoryPrivacy = DirectoryPrivacy::where('hosting_subscription_id', $hostingSubscription->id)->first();

            if ($directoryPrivacy && $directoryPrivacy->protected != $model->protected) {
                $directoryPrivacy->update([
                    'protected' => $model->protected
                ]);
            } else {
                if (!empty($model->username)) {
                    DirectoryPrivacy::create([
                        'directory' => $model->directory,
                        'username' => $model->username,
                        'password' => $model->password,
                        'protected' => $model->protected,
                        'label' => $model->label,
                        'path' => $model->path,
                    ]);
                }
            }
        });
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

            $backPath = [
                [
                    'directory' => 'Up One Level',
                    'username' => null,
                    'authorized_users' => null,
                    'password' => null,
                    'protected' => null,
                    'label' => null,
                    'type' => 'Folder',
                    'path' => $path->count() > 1 ? $path->take($path->count() - 1)->join('/') : '',
                ],
            ];
        }

        $storage = $this->storageInstance();

        $directories = collect($storage->directories(static::$path))
            ->sort()
            ->map(function (string $directory) use ($storage) {

                $directoryRealPath = $storage->path($directory);
                $directoryPrivacyData = HtaccessBuildDirectoryPrivacy::getDirectoryPrivacyData($directoryRealPath);

                return [
                    'directory' => Str::remove(self::$path . '/', $directory),
                    'username' => '',
                    'authorized_users' => '',
                    'password' => $directoryPrivacyData['password'] ?? '',
                    'protected' => $directoryPrivacyData['protected'] ?? 'No',
                    'label' => $directoryPrivacyData['label'] ?? '',
                    'type' => 'Folder',
                    'path' => $directory,
                ];
            });
        return collect($backPath)
            ->push(...$directories)
            ->toArray();
    }


}
