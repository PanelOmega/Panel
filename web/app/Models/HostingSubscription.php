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

        static::creating(function ($model) {

            $findDomain = Domain::where('domain', $model->domain)->first();
            if ($findDomain) {
                throw new \Exception('Domain already exists');
            }

            $create = $model->_createLinuxWebUser($model);
            if (isset($create['error'])) {
                throw new \Exception($create['message']);
            }

            if (isset($create['system_username']) && isset($create['system_password'])) {
                $model->system_username = $create['system_username'];
                $model->system_password = $create['system_password'];
                $model->system_user_id = $create['system_user_id'];
            } else {
                return false;
            }

        });

        static::created(function ($model) {

            $makeMainDomain = new Domain();
            $makeMainDomain->hosting_subscription_id = $model->id;
            $makeMainDomain->domain = $model->domain;
            $makeMainDomain->is_main = 1;
            $makeMainDomain->status = Domain::STATUS_ACTIVE;
            $makeMainDomain->save();

        });

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

    private function _createLinuxWebUser($model): array
    {
        $findCustomer = Customer::where('id', $model->customer_id)->first();
        if (!$findCustomer) {
            return [];
        }

        if (!empty($model->system_username)) {

            $linuxUser = LinuxUser::getUser($model->system_username);
            if (!empty($linuxUser)) {
                return [
                    'error' => true,
                    'message' => 'System username already exists.'
                ];
            }
        }

        if (empty($model->system_username)) {
            $systemUsername = $this->_generateUsername($model->domain . $findCustomer->id);
            if ($this->_startsWithNumber($systemUsername)) {
                $systemUsername = $this->_generateUsername(Str::random(4));
            }

            $linuxUser = LinuxUser::getUser($systemUsername);

            if (!empty($linuxUser)) {
                $systemUsername = $this->_generateUsername($systemUsername . $findCustomer->id . Str::random(4));
            }

            $systemPassword = Str::random(14);
        } else {
            $systemUsername = $model->system_username;
            $systemPassword = $model->system_password;
        }

        $createLinuxWebUserOutput = LinuxUser::createWebUser($systemUsername, $systemPassword);
        if (isset($createLinuxWebUserOutput['success'])) {

            return [
                'system_username' => $systemUsername,
                'system_password' => $systemPassword,
                'system_user_id' => $createLinuxWebUserOutput['linuxUserId']
            ];

        }

        return [];

    }

    private static function _generateUsername($string)
    {
        $removedMultispace = preg_replace('/\s+/', ' ', $string);
        $sanitized = preg_replace('/[^A-Za-z0-9\ ]/', '', $removedMultispace);
        $lowercased = strtolower($sanitized);
        $lowercased = str_replace(' ', '', $lowercased);
        $lowercased = trim($lowercased);
        if (strlen($lowercased) > 10) {
            $lowercased = substr($lowercased, 0, 4);
        }

        $username = $lowercased . rand(1111, 9999) . Str::random(4);
        $username = strtolower($username);

        return $username;
    }

    private function _startsWithNumber($string)
    {
        return strlen($string) > 0 && ctype_digit(substr($string, 0, 1));
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
