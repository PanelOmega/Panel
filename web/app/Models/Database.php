<?php

namespace App\Models;

use App\OmegaConfig;
use App\Services\RemoteDatabaseService;
use App\UniversalDatabaseExecutor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class Database extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'remote_database_server_id',
        'is_remote_database_server',
        'database_name',
        'database_name_prefix',
        'description',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $hostingSubscription = Customer::getHostingSubscriptionSession();

            $model->hosting_subscription_id = $hostingSubscription->id;
            $model->database_name_prefix = $hostingSubscription->system_username . '_';

            $databaseName = Str::slug($model->database_name, '_');
            $databaseName = $model->database_name_prefix . $databaseName;
            $databaseName = strtolower($databaseName);

            if ($model->is_remote_database_server == 1) {

                $remoteDatabaseService = new RemoteDatabaseService($model->remote_database_server_id);
                $createDatabase = $remoteDatabaseService->createDatabase($databaseName);
                if (isset($createDatabase['error'])) {
                    throw new \Exception($createDatabase['message']);
                }

            } else {
                $universalDatabaseExecutor = new UniversalDatabaseExecutor(
                    OmegaConfig::get('MYSQL_HOST', '127.0.0.1'),
                    OmegaConfig::get('MYSQL_PORT', 3306),
                    OmegaConfig::get('MYSQL_ROOT_USERNAME'),
                    OmegaConfig::get('MYSQL_ROOT_PASSWORD'),
                );

                $createDatabase = $universalDatabaseExecutor->createDatabase($databaseName);
                if (isset($createDatabase['error'])) {
                    throw new \Exception($createDatabase['message']);
                }
            }

            return $model;

        });

        static::deleting(function($model) {

            if ($model->is_remote_database_server == 1) {

                $remoteDatabaseService = new RemoteDatabaseService($model->remote_database_server_id);
                $deleteDatabase = $remoteDatabaseService->deleteDatabase($model->database_name_prefix . $model->database_name);
                if (!$deleteDatabase) {
                    return false;
                }

            }
        });
    }

    public function hostingSubscription()
    {
        return $this->belongsTo(HostingSubscription::class);
    }

    public function databaseUsers()
    {
        return $this->hasMany(DatabaseUser::class);
    }
}
