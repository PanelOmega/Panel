<?php

namespace App\Models\HostingSubscription;

use App\Models\Traits\VisitorsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class VisitorLog extends Model
{
    use HasFactory, Sushi, VisitorsTrait;

    protected $fillable = [
        'domain_name',
        'ip',
        'url',
        'time',
        'size',
        'status',
        'method',
        'protocol',
        'referring_url',
        'user_agent',
    ];

    protected $schema = [
        'domain_name' => 'string',
        'ip' => 'string',
        'url' => 'string',
        'time' => 'string',
        'size' => 'string',
        'status' => 'string',
        'method' => 'string',
        'protocol' => 'string',
        'referring_url' => 'string',
        'user_agent' => 'string',
    ];

    public function getRows() {
        $logData = $this->getDomainLogData();

        return collect($logData)->map(function ($log) {
            return [
                'domain_name' => $log['domain'],
                'ip' => $log['ip'],
                'url' => $log['url'],
                'time' => $log['time'],
                'size' => $log['size'],
                'status' => $log['status'],
                'method' => $log['method'],
                'protocol' => $log['protocol'],
                'referring_url' => $log['referring_url'],
                'user_agent' => $log['user_agent'],
            ];
        })->toArray();
    }
}

