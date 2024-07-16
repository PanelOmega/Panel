<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PHPMyAdminSSOToken;

class PHPMyAdminController extends Controller
{
    public function login()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        if (!$hostingSubscription) {
            return redirect('/');
        }

        // Delete old sso tokens
        PHPMyAdminSSOToken::where('customer_id', $hostingSubscription->customer_id)
            ->where('hosting_subscription_id', $hostingSubscription->id)
            ->delete();

        // Create new sso token
        $ssoToken = new PHPMyAdminSSOToken();
        $ssoToken->customer_id = $hostingSubscription->customer_id;
        $ssoToken->hosting_subscription_id = $hostingSubscription->id;
        $ssoToken->token = md5(uniqid() . time() . $hostingSubscription->customer_id . $hostingSubscription->id);
        $ssoToken->expires_at = now()->addMinutes(5);
        $ssoToken->ip_address = request()->ip();
        $ssoToken->user_agent = request()->userAgent();
        $ssoToken->save();

        $currentUrl = url('/');
        $currentUrl = str_replace(':8443', ':8440', $currentUrl);

        return redirect($currentUrl . '/omega-sso.php?server=1&token=' . $ssoToken->token);

    }

}
