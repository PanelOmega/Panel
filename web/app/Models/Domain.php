<?php

namespace App\Models;

use App\Virtualization\Docker\DockerClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $findHostingSubscription = HostingSubscription::where('id', $model->hosting_subscription_id)->first();
            if (!$findHostingSubscription) {
                throw new \Exception('Hosting Subscription not found');
            }

            $findHostingPlan = HostingPlan::where('id', $findHostingSubscription->hosting_plan_id)->first();
            if (!$findHostingPlan) {
                throw new \Exception('Hosting Plan not found');
            }

            $dockerClient = new DockerClient();

            $containers = $dockerClient->listContainers()['response'];
           // dd($containers);
            foreach ($containers as $container) {
                $dockerClient->stopContainer($container['Id']);
                $dockerClient->deleteContainer($container['Id']);
            }

            $dockerClient->pullImage('php:5.6-fpm');
            $dockerContainerName =  Str::slug('php-5.6-fpm-'. $model->domain);
            $createDocker = $dockerClient->createContainer($dockerContainerName, [
                'Image' => 'php:5.6-fpm',
                'HostConfig'=> [
                    'NetworkMode' => 'host',
//                    'PortBindings' => [
//                        '9002/tcp' => [
//                            [
//                                'HostPort' => '9002',
//                            ]
//                        ]
//                    ]
                ],
//                'ExposedPorts' => [
//                    '9002/tcp' => new \stdClass(),
//                ],
            ]);


            dump($createDocker);

            $startDockerContainer = $dockerClient->startContainer($createDocker['response']['Id']);
            dd($startDockerContainer);

        });
    }

}
