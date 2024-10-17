<?php

namespace App\Models\HostingSubscription;

use App\Models\Customer;
use App\Models\Traits\ZoneEditorDnssecTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class   ZoneEditorDnssec extends Model
{
    use HasFactory, ZoneEditorDnssecTrait;

    protected $fillable = [
        'hosting_subscription_id',
        'domain',
        'key_tag',
        'ket_type',
        'algorithm',
    ];

    protected $table = 'hosting_subscription_zone_editor_dnssecs';

    public static function boot()
    {
        parent::boot();
        static::zoneEditorDnssecBoot();
    }

    public static function zoneEditorDnssecBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();

        static::creating(function ($model) use ($hostingSubscription) {
            $model->hosting_subscription_id = $hostingSubscription->id;
        });

        $callback = function($model) use ($hostingSubscription) {

        };

        static::created(function($model) use ($callback) {
            $callback($model);
        });
        static::updated(function ($model) use ($callback) {
            $callback($model);
        });
        static::deleted(function ($model) use ($callback) {
            $callback($model);
        });


    }
}
