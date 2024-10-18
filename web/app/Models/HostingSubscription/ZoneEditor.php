<?php

namespace App\Models\HostingSubscription;

use App\Jobs\ZoneEditorConfigBuild;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Traits\ZoneEditorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class ZoneEditor extends Model
{
    use HasFactory, ZoneEditorTrait;

    protected $fillable = [
        'hosting_subscription_id',
        'domain',
        'name',
        'ttl',
        'type',
        'priority',
        'record',
    ];

    protected $table = 'hosting_subscription_zone_editors';

    public static function boot()
    {
        parent::boot();
        static::zoneEditorBoot();
    }

    public static function zoneEditorBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::creating(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
        });

        $callback = function($model) use ($hostingSubscription) {

            $zoneEditorBuild = new ZoneEditorConfigBuild(false, $hostingSubscription);
            $zoneEditorBuild->handle();
        };
        static::created(function($model) use ($callback) {
            $callback($model);
        });
        static::updated(function($model) use ($callback) {
            $callback($model);
        });
        static::deleted(function($model) use ($callback) {
            $callback($model);
        });
    }
}
