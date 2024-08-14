<?php

namespace App\Models;

use App\Jobs\ApacheBuild;
use App\Jobs\HtaccessBuildPHPVersions;
use App\Server\Helpers\PHP;
use App\Server\VirtualHosts\DTO\ApacheVirtualHostSettings;
use App\Services\Domain\DomainService;
use App\Virtualization\Docker\DockerClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Docker\App\Models\DockerContainer;

class Domain extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_DELETED = 'deleted';

    public const STATUS_DEACTIVATED = 'deactivated';

    public const STATUS_BROKEN = 'broken';

    protected $fillable = [
        'domain',
        'domain_root',
        'ip',
        'hosting_subscription_id',
        'server_application_type',
        'server_application_settings',
        'status'
    ];

    protected $casts = [
        'server_application_settings' => 'array',
        'docker_settings' => 'array'
    ];


    public static function boot()
    {

        parent::boot();

        static::created(function ($model) {
            if ($model->hosting_subscription_id !== null) {
                $findHostingSubscription = HostingSubscription::where('id', $model->hosting_subscription_id)->first();
                if (!$findHostingSubscription) {
                    throw new \Exception('Hosting Subscription not found');
                }
            } else {
                $findHostingSubscription = Customer::getHostingSubscriptionSession();
                $model->hosting_subscription_id = $findHostingSubscription->id;
            }

            if ($findHostingSubscription->domain !== $model->domain) {
                $model->is_main = 0;
            }

            $findHostingPlan = HostingPlan::where('id', $findHostingSubscription->hosting_plan_id)->first();
            if (!$findHostingPlan) {
                throw new \Exception('Hosting Plan not found');
            }

            $model->server_application_type = $findHostingPlan->default_server_application_type;
            $model->server_application_settings = $findHostingPlan->default_server_application_settings;

            if ($model->is_main == 1) {
                //  $allDomainsRoot = '/home/'.$this->user.'/public_html';
                $model->domain_root = '/home/'.$findHostingSubscription->system_username;
                $model->domain_public = '/home/'.$findHostingSubscription->system_username.'/public_html';
                $model->home_root = '/home/'.$findHostingSubscription->system_username;
            } else {
                //   $allDomainsRoot = '/home/'.$model->user.'/domains';
                $model->domain_root = '/home/'.$findHostingSubscription->system_username.'/domains/'.$model->domain;
                $model->domain_public = $model->domain_root.'/public_html';
                $model->home_root = '/home/'.$findHostingSubscription->user;
            }
            $model->saveQuietly();


            $domainService = new DomainService();
            $domainService->configureVirtualHost($model->id, true, true);


//            if ($model->server_application_type == 'apache_php') {
//                $model->createDockerContainer();
//            }
//
            // This must be in background
            $apacheBuild = new ApacheBuild();
            $apacheBuild->handle();

        });

        static::updating(function ($model) {

            $domainService = new DomainService();
            $domainService->configureHtaccess($model->id);

        });

        static::updated(function ($model) {

            $apacheBuild = new ApacheBuild();
            $apacheBuild->handle();

        });

        static::deleted(function ($model) {

//            if (isset($model->docker_settings['containerId'])) {
//
//                try {
//                    $dockerClient = new DockerClient();
//                    $dockerClient->stopContainer($model->docker_settings['containerId']);
//                    $dockerClient->deleteContainer($model->docker_settings['containerId']);
//                } catch (\Exception $e) {
//                    // Do nothing
//                }
//            }
//
//            $apacheBuild = new ApacheBuild();
//            $apacheBuild->handle();

        });
    }

    public function hostingSubscription()
    {
        return $this->belongsTo(HostingSubscription::class);
    }

    public function getPHPVersionAttribute()
    {
        if (isset($this->server_application_settings['php_version'])) {
            return 'PHP ' . $this->server_application_settings['php_version'];
        }
        return 'PHP 8.3';
    }

    public function getPHPFpmAttribute()
    {
        if (isset($this->server_application_settings['enable_php_fpm']) && $this->server_application_settings['enable_php_fpm'] == true) {
            return 'enabled';
        }
        return 'disabled';
    }

    public function getDocumentRootAttribute()
    {
        return '/public_html';
    }

}
