<?php

namespace App\Models;

use App\Jobs\HtaccessBuildIndexes;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sushi\Sushi;

class Index extends Model
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
        static::IndexesBoot();
    }

    public static function IndexesBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        $callback = function ($model) use ($hostingSubscription) {
            $htaccessBuild = new HtaccessBuildIndexes(false, $model, $hostingSubscription);
            $htaccessBuild->handle();
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

                $directoryRealPath = $storage->path($directory);
                $indexType = HtaccessBuildIndexes::getIndexType($directoryRealPath);

                return [
                    'directory' => Str::remove(self::$path . '/', $directory),
                    'directory_real_path' => $directory,
                    'index_type' => $indexType,
                    'type' => 'Folder',
                    'path' => $directory,
                ];
            });
        return collect($backPath)
            ->push(...$directories)
            ->toArray();

    }
}
