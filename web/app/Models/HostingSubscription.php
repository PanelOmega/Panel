<?php

namespace App\Models;

use App\Actions\CreateLinuxWebUser;
use App\Actions\GetLinuxUser;
use App\Jobs\ApacheBuild;
use App\OmegaConfig;
use App\Server\Helpers\FtpAccount;
use App\Server\Helpers\LinuxUser;
use App\UniversalDatabaseExecutor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HostingSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'domain',
        'customer_id',
        'hosting_plan_id',
        'system_username',
        'system_password',
        'description',
        'setup_date',
        'expiry_date',
        'renewal_date',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {

            if (empty($model->system_username)) {
                throw new \Exception('System username is empty');
            }

            $getLinuxUserStatus = LinuxUser::getUser($model->system_username);
            if (empty($getLinuxUserStatus)) {
                throw new \Exception('System username not found');
            }

            $getFptUser = HostingSubscriptionFtpAccount::where('ftp_username', $model->system_username)->get();

            if (!$getFptUser->isEmpty()) {
                $getFptUser->delete();
            }

            $findRelatedDomains = Domain::where('hosting_subscription_id', $model->id)->get();
            if ($findRelatedDomains->count() > 0) {
                foreach ($findRelatedDomains as $domain) {
                    $domain->delete();
                }
            }

            // Delete databases
            $databases = Database::where('hosting_subscription_id', $model->id)->get();
            if ($databases->count() > 0) {
                foreach ($databases as $database) {
                    // Delete database users
                    $databaseUsers = DatabaseUser::where('database_id', $database->id)->get();
                    if ($databaseUsers->count() > 0) {
                        foreach ($databaseUsers as $databaseUser) {
                            $databaseUser->delete();
                        }
                    }
                    $database->delete();
                }
            }
            // Delete main database user
            $universalDatabaseExecutor = new UniversalDatabaseExecutor(
                OmegaConfig::get('MYSQL_HOST', '127.0.0.1'),
                OmegaConfig::get('MYSQL_PORT', 3306),
                OmegaConfig::get('MYSQL_ROOT_USERNAME'),
                OmegaConfig::get('MYSQL_ROOT_PASSWORD'),
            );

            // Check main database user exists
            $mainDatabaseUser = $universalDatabaseExecutor->getUserByUsername($model->system_username);
            if (!$mainDatabaseUser) {
                $deleteMainDatabaseUser = $universalDatabaseExecutor->deleteUserByUsername($model->system_username);
                if (!isset($deleteMainDatabaseUser['success'])) {
                    //throw new \Exception($deleteMainDatabaseUser['message']);
                }
            }

            // Delete linux user
            LinuxUser::deleteUser($model->system_username);

            // This must be in background
            $apacheBuild = new ApacheBuild();
            $apacheBuild->handle();

        });

    }



//    public function databases()
//    {
//        return $this->hasMany(Database::class);
//    }

    protected static function booted(): void
    {
        static::addGlobalScope('customer', function (Builder $query) {
            if (auth()->check() && auth()->guard()->name == 'web_customer') {
                $query->where('customer_id', auth()->user()->id);
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function hostingPlan()
    {
        return $this->belongsTo(HostingPlan::class);
    }

    public function backups()
    {
        return $this->hasMany(HostingSubscriptionBackup::class);
    }

    public function domain()
    {
        return $this->hasMany(Domain::class);
    }

    public function ftpAccounts()
    {
        return $this->hasMany(HostingSubscriptionFtpAccount::class);
    }

    public function hotlinkProtection()
    {
        return $this->hasOne(HotlinkProtection::class);
    }

}
