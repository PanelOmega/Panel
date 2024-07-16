<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CustomerScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {

        $authGuard = Auth::guard('customer');

        if ($authGuard->check()) {

            $customerId = $authGuard->user()->id;

            $hostingSubscriptionId = Session::get('hosting_subscription_id');

            $builder->whereHas('hostingSubscription', function ($query) use($customerId, $hostingSubscriptionId) {
                $query->where('customer_id', $customerId);
                $query->where('id', $hostingSubscriptionId);
            });

        }

    }
}
