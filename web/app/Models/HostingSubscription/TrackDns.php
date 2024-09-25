<?php

namespace App\Models\HostingSubscription;

use App\Models\Traits\TrackDnsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Sushi\Sushi;

class   TrackDns extends Model
{
    use HasFactory, Sushi, TrackDnsTrait;

    protected static $hostData = '';

    protected $fillable = [
        'id',
        'host',
        'trace'
        ];

    protected function getRows(): array {
        $host = Session::get('host');
        $tracerouteData =  self::getTraceroute($host);
        static::$hostData = $tracerouteData['host'];

        $traceRows = array_map(function ($line, $index) {
            return [
                'id' => $index + 1,
                'trace' => $line
            ];
        }, array_slice($tracerouteData, 1), array_keys(array_slice($tracerouteData, 1)));
        return $traceRows;
    }

    public static function getHostData() {
        return self::$hostData;
    }

}
