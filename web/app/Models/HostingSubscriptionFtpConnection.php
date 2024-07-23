<?php

namespace App\Models;

use App\Services\FtpConnections\FtpConnectionsService;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class HostingSubscriptionFtpConnection extends Model
{
    use Sushi;

    protected $fillable = [
        'user',
        'logged_in_from',
        'login_time',
        'status',
        'process_id'
    ];

    protected $schema = [
        'id' => 'integer',
        'user' => 'string',
        'logged_in_from' => 'string',
        'login_time' => 'string',
        'status' => 'string',
        'process_id' => 'string'
    ];

    public function getRows()
    {
        $ftpConnections = FtpConnectionsService::getCurrentFtpConnections();
        
        return array_map(function ($ftpConnection, $index) {
            return [
                'id' => $index + 1,
                'user' => $ftpConnection['user'],
                'logged_in_from' => $ftpConnection['logged_in_from'],
                'login_time' => $ftpConnection['login_time'],
                'status' => $ftpConnection['status'],
                'process_id' => $ftpConnection['process_id'],
            ];
        }, $ftpConnections, array_keys($ftpConnections));
    }
}
