<?php

namespace App\Models;

use App\DirectoryTreeBuildTrate;
use App\Jobs\HtaccessBuildDirectoryPrivacy;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sushi\Sushi;

class DirectoryPrivacy extends Model
{
    use HasFactory, DirectoryTreeBuildTrate;

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

//    protected $fillable = [
//        'hosting_subscription_id',
//        'directory',
//        'username',
//        'password',
//        'protected',
//        'label'
//    ];

    protected array $schema = [
        'directory' => 'string',
        'username' => 'string',
        'authorized_users' => 'string',
        'password' => 'string',
        'protected' => 'string',
        'label' => 'string',
        'type' => 'string'
    ];

    public static function boot()
    {
        parent::boot();
        static::DirectoryPrivacyBoot();
    }

    public static function DirectoryPrivacyBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::saving(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
            $command = "htpasswd -nb $model->username $model->password";
            $result = shell_exec($command);
            if ($result) {
                list($user, $hashedPasswd) = explode(':', trim($result), 2);
                $model->password = $hashedPasswd;
            }
        });

        $callback = function ($model = null) use ($hostingSubscription) {
            $directoryPrivacy = new HtaccessBuildDirectoryPrivacy(false, $hostingSubscription->id);
            $directoryPrivacy->handle($model);
        };

        static::created(function () use ($callback) {
            $callback();
        });
        static::updated(function ($model) use ($callback) {
            $callback($model);
        });
        static::deleted(function ($model) use ($callback) {
            $callback($model);
        });
    }

    public static function queryForDiskAndPath(string $rootPath = 'public', string $path = ''): Builder
    {
        static::$rootPath = $rootPath;
        static::$path = $path;

        return static::query();
    }

    public function getAuthorizedUsers()
    {
        $authorizedUsersString = $this->authorized_users;
        $usernames = array_filter(array_map('trim', explode(',', $authorizedUsersString)));
        return array_map(fn($username) => ['username' => $username], $usernames);
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
                    'authorized_usernames' => $directoryPrivacyData['authorized_users'] ?? '',
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
