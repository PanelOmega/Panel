<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FtpFileManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'ftp_account_id',
        'parent_id',
        'name',
        'path',
        'is_directory'
    ];

    // sushi

    public function parent()
    {
        return $this->belongsTo(FtpFileManager::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FtpFileManager::class, 'parent_id');
    }

    public function ftpAccount()
    {
        return $this->belongsTo(HostingSubscriptionFtpAccount::class, 'ftp_account_id');
    }

    public function ftpFiles()
    {
        //
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $pathParts = explode('/', $model->path);
            $ftpUser = HostingSubscriptionFtpAccount::where('path', $pathParts[])->first();

            if (empty($ftpUser)) {
                return [
                    'error' => true,
                    'message' => 'Ftp account not found.'
                ];
            }

            $model->ftp_account_id = $ftpUser->id;

            $fullPath = trim($model->path, '/' . $model->name);

            $create = $model->fileManagerCreate($fullPath);

            if (isset($create['error'])) {
                throw new \Exception($create['message']);
            }
        });

        static::updating(function ($model) {

        });

        static::deleting(function ($model) {

        });
    }

    public function fileManagerCreate(string $fullPath): array
    {

        // check if the dir already exists

        if ($this->is_directory) {

            $command = "sudo mkdir -p  $fullPath";
        } else {

            $command = "sudo touch $fullPath";
        }

        if (shell_exec($command) == '') {
            return [
                'success' => true,
                'message' => 'Created successfully.'
            ];
        }

        return [
            'error' => true,
            'message' => 'Failed to complete the request.'
        ];
    }
}
