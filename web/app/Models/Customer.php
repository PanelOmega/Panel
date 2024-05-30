<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
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

    public function hostingSubscriptions()
    {
        return $this->hasMany(HostingSubscription::class);
    }

    public function canBeImpersonated()
    {
        return true;
    }
}
