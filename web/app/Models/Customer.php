<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    use HasFactory;

    protected $fillable = [
        'name',
        'username',
        'password',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'company',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hostingSubscriptions()
    {
        return $this->hasMany(HostingSubscription::class);
    }

    public function canBeImpersonated()
    {
        return true;
    }

    public static function getHostingSubscriptionSession()
    {
        $hostingSubscriptionId = Session::get('hosting_subscription_id');
        $customerId = Auth::guard('customer')->user()->id;

        $findHostingSubscription = HostingSubscription::where('customer_id', $customerId)
            ->where('id', $hostingSubscriptionId)
            ->first();

        if (!$findHostingSubscription) {
            $findHostingSubscription = HostingSubscription::where('customer_id', $customerId)
                ->first();
            Session::put('hosting_subscription_id', $findHostingSubscription->id);
        }

        return $findHostingSubscription;
    }
}
