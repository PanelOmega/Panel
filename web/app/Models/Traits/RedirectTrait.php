<?php

namespace App\Models\Traits;

use App\Models\Customer;
use App\Models\Domain;

trait RedirectTrait
{
    public static function getRedirectTypes()
    {
        $types = [];
        $redirectTypes = [
            'permanent_301' => 'Permanent (301)',
            'temporary_302' => 'Temporary (302)',
        ];

        foreach ($redirectTypes as $key => $type) {
            $types[$key] = $type;
        }

        return $types;

    }

    public static function getWwwRedirects()
    {
        $wwwRedirects = [];
        $www = [
            'only' => 'Only redirect with www.',
            'redirectwithorwithoutwww' => 'Redirect with or without www.',
            'donotredirectwww' => 'Do Not Redirect wwww.'
        ];

        foreach ($www as $key => $redirect) {
            $wwwRedirects[$key] = $redirect;
        }

        return $wwwRedirects;
    }

    public static function getRedirectDomains()
    {
        $domains = [];
        $domains['all_public_domains'] = 'All Public Domains';

        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $subscriberDomains = Domain::where('hosting_subscription_id', $hostingSubscription->id)->get();

        foreach ($subscriberDomains as $subDomain) {
            $domains[$subDomain->domain] = $subDomain->domain;
        }

        return $domains;
    }
}
