<?php

namespace App\Models;

use App\Jobs\ErrorPageBuild;
use App\Server\SupportedApplicationTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class ErrorPage extends Model
{
    use HasFactory;
    use Sushi;

    protected $fillable = [
        'name',
        'content',
        'path'
    ];

    protected array $schema = [
        'id' => 'integer',
        'name' => 'string',
        'content' => 'text',
        'path' => 'string'
    ];

    protected static function boot()
    {
        parent::boot();
        static::errorPagesBoot();
    }

    public static function errorPagesBoot()
    {
        static::updating(function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $errorPagePath = "/home/{$hostingSubscription->system_username}/public_html";
            $errorPageBuild = new ErrorPageBuild(false, $errorPagePath);
            $errorPageBuild->handle($model);
        });
    }

    public function getRows(): array
    {
        $errorPages = SupportedApplicationTypes::getErrorPages();
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $errorPagePath = "/home/{$hostingSubscription->system_username}/public_html";
        $errorPageBuild = new ErrorPageBuild(false, $errorPagePath);
        return array_map(function ($errorPage, $index) use ($errorPagePath, $errorPageBuild) {
            return [
                'id' => $index + 1,
                'name' => $errorPage,
                'content' => $errorPageBuild->getErrorPageContent($errorPage) ?? '',
                'path' => $errorPagePath
            ];
        }, $errorPages, array_keys($errorPages));
    }
}
