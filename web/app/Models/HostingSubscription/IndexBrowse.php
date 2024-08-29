<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildIndexes;
use App\Models\Customer;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sushi\Sushi;

class IndexBrowse extends Model
{
    use Sushi;

    protected static string $rootPath;

    protected static string $path;

    protected $fillable = [
        'directory',
        'index_type',
    ];

    protected array $schema = [
        'directory' => 'string',
        'directory_real_path' => 'string',
        'index_type' => 'string',
        'type' => 'string',
    ];

    public static function boot()
    {
        parent::boot();
        static::HostingSubscriptionIndexBrowseBoot();
    }

    public static function HostingSubscriptionIndexBrowseBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        $callback = function ($model) use ($hostingSubscription) {

            $hostingSubscriptionIndex = Index::where('hosting_subscription_id', $hostingSubscription->id)
                ->where('directory_real_path', $model->directory_real_path)->first();

            if ($hostingSubscriptionIndex) {
                $hostingSubscriptionIndex->update([
                    'index_type' => $model->index_type,
                ]);
            } else {
                Index::create([
                    'hosting_subscription_id' => $hostingSubscription->id,
                    'directory' => $model->directory,
                    'directory_real_path' => $model->directory_real_path,
                    'directory_type' => $model->type,
                    'index_type' => $model->index_type,
                ]);
            }
        };

        static::updated($callback);
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
                    'directory_real_path' => null,
                    'index_type' => null,
                    'type' => 'Folder',
                    'path' => $path->count() > 1 ? $path->take($path->count() - 1)->join('/') : '',
                ],
            ];
        }

        $storage = $this->storageInstance();

        $directories = collect($storage->directories(static::$path))
            ->sort()
            ->map(function (string $directory) use ($storage) {
                $indexType = 'inherit';
                $private = 'public';

                $hostingSubsctipion = Customer::getHostingSubscriptionSession();
                $indexType = HtaccessBuildIndexes::getIndexType($hostingSubsctipion->id, $directory);

                return [
                    'directory' => Str::remove(self::$path . '/', $directory),
                    'directory_real_path' => $directory,
                    'index_type' => empty($indexType) ? 'Inherit' : $indexType[0],
                    'type' => 'Folder',
                    'path' => $directory,
                ];
            });
        return collect($backPath)
            ->push(...$directories)
            ->toArray();

    }
}
