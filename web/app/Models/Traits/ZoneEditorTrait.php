<?php

namespace App\Models\Traits;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingSubscription\ZoneEditor;

trait ZoneEditorTrait
{

    public static function getDomains() {
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

}
