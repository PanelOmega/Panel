<?php

namespace App\Models\Traits;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingSubscription\ZoneEditor;

trait ZoneEditorTrait
{

    public static function getDomains(): array
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $domains = Domain::where('hosting_subscription_id', $hostingSubscription->id)->pluck('domain');
        $currentDomains = [];

        if($domains[0]) {
            foreach ($domains as $domain) {
                $currentDomains[$domain] = $domain;
            }
        }
        return $currentDomains;
    }

    public static function getTtlOptions(): array
    {
        $ttl = [];
        $options = [
            'Auto' => '14400',
            '1 minute' => '60',
            '2 minutes' => '120',
            '5 minutes' => '300',
            '10 minutes' => '600',
            '15 minutes' => '900',
            '30 minutes' => '1800',
            '1 hour' => '3600',
            '2 hours' => '7200',
            '5 hours' => '18000',
            '12 hours' => '43200',
            '1 day' => '86400'
        ];

        foreach ($options as $key => $option) {
            $ttl[$option] = $key;
        }

        return $ttl;
    }

    public static function getTypeOptions(): array
    {
        $types = [
            'A' => 'A',
            'CNAME' => 'CNAME',
            'MX' => 'MX',
            'TXT' => 'TXT'
        ];

        return $types;
    }

}
