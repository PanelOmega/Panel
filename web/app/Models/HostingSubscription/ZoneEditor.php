<?php

namespace App\Models\HostingSubscription;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneEditor extends Model
{
    use HasFactory;

    protected $fillable = [
        'hosting_subscription_id',
        'domain',
        'name',
        'ttl',
        'type',
        'record',
    ];

    public static function boot()
    {
        parent::boot();
        static::zoneEditorBoot();
    }

    public static function zoneEditorBoot()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        self::getDomain($hostingSubscription);
    }

    public static function getDomain($hostingSubscription)
    {
        if ($hostingSubscription) {
            ZoneEditor::firstOrCreate(
                ['hosting_subscription_id' => $hostingSubscription->id],
                ['domain' => $hostingSubscription->domain]
            );
        }
    }

    public static function testGetDomain()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        return $hostingSubscription->domain;
    }

    public function dnssecRecords()
    {
        return $this->hasMany(ZoneEditorDnssec::class, 'zone_editor_id');
    }
}
