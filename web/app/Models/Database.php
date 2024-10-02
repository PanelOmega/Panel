<?php

namespace App\Models;

use App\OmegaConfig;
use App\Services\RemoteDatabaseService;
use App\UniversalDatabaseExecutor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

            if (isset($model->hosting_subscription_id)) {

                $hostingSubscription = HostingSubscription::where('id', $model->hosting_subscription_id)->first();
                if (!$hostingSubscription) {
                    throw new \Exception('Hosting subscription not found');
                }

                $model->database_name_prefix = $hostingSubscription->system_username . '_';

            } else {
                $hostingSubscription = Customer::getHostingSubscriptionSession();

                $model->hosting_subscription_id = $hostingSubscription->id;
                $model->database_name_prefix = $hostingSubscription->system_username . '_';
            }

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

                // Check main database user exists
                $mainDatabaseUser = $universalDatabaseExecutor->getUserByUsername($hostingSubscription->system_username);
                if (!$mainDatabaseUser) {
                    $createMainDatabaseUser = $universalDatabaseExecutor->createUser($hostingSubscription->system_username, $hostingSubscription->system_password);
                    if (!isset($createMainDatabaseUser['success'])) {
                        throw new \Exception($createMainDatabaseUser['message']);
                    }
                }

                $createDatabase = $universalDatabaseExecutor->createDatabase($databaseName);
                if (isset($createDatabase['error'])) {
                    throw new \Exception($createDatabase['message']);
                }

                $universalDatabaseExecutor->userGrantPrivilegesToDatabase($hostingSubscription->system_username, [$databaseName]);
            }

            return $model;

        });

        static::deleting(function ($model) {

            if ($model->is_remote_database_server == 1) {

                $remoteDatabaseService = new RemoteDatabaseService($model->remote_database_server_id);
                $deleteDatabase = $remoteDatabaseService->deleteDatabase($model->database_name_prefix . $model->database_name);
                if (!$deleteDatabase) {
                    return false;
                }

            }
        });
    }

    public function calculateDatabaseSize()
    {
        $universalDatabaseExecutor = new UniversalDatabaseExecutor(
            OmegaConfig::get('MYSQL_HOST', '127.0.0.1'),
            OmegaConfig::get('MYSQL_PORT', 3306),
            OmegaConfig::get('MYSQL_ROOT_USERNAME'),
            OmegaConfig::get('MYSQL_ROOT_PASSWORD'),
        );

        return $universalDatabaseExecutor->getDatabaseUsage($this->database_name_prefix . $this->database_name);
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
