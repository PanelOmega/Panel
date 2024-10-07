<?php

namespace App\Models\HostingSubscription;

use App\Models\Customer;
use App\Models\Traits\VisitorsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Visitor extends Model
{
    use HasFactory, Sushi, VisitorsTrait;

    protected $fillable = [
        'id',
        'domain',
    ];

    protected $schema = [
        'id' => 'string',
        'domain' => 'string',
    ];

    public function getRows()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $domains = $this->getDomains($hostingSubscription->id);

        return collect($domains)->map(function ($domain, $index) {

            return [
                'id' => $index + 1,
                'domain' => $domain . ' ' . $this->getSslStatus(),
            ];
        })->toArray();
    }
}
