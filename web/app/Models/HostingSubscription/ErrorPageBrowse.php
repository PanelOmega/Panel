<?php

namespace App\Models\HostingSubscription;

use App\Jobs\HtaccessBuildErrorPage;
use App\Models\Customer;
use App\Models\Traits\ErrorPageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class ErrorPageBrowse extends Model
{
    use HasFactory, Sushi, ErrorPageTrait;

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
        static::hostingSubscriptionErrorPageBrowseBoot();
    }

    public static function hostingSubscriptionErrorPageBrowseBoot()
    {
        static::updating(function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();

            $hostingSubscriptionErrorPage = ErrorPage::where('hosting_subscription_id', $hostingSubscription->id)
                ->where('name', $model->name)
                ->first();

            if ($hostingSubscriptionErrorPage) {
                $hostingSubscriptionErrorPage->update([
                    'content' => $model->content
                ]);
            } else {

                $getErrorCode = function ($pageName) {
                    if (preg_match('/^\d+/', $pageName, $matches)) {
                        return $matches[0];
                    }
                    return null;
                };

                ErrorPage::create([
                    'hosting_subscription_id' => $hostingSubscription->id,
                    'name' => $model->name,
                    'error_code' => $getErrorCode($model->name),
                    'content' => $model->content,
                    'path' => $model->path
                ]);
            }
        });
    }

    public function getRows(): array
    {
        $errorPages = self::getErrorPages();
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $errorPagePath = "/home/{$hostingSubscription->system_username}/public_html";
        $errorPageBuild = new HtaccessBuildErrorPage(false, $hostingSubscription);
        return array_map(function ($errorPage, $index) use ($errorPagePath, $errorPageBuild, $hostingSubscription) {
            return [
                'id' => $index + 1,
                'name' => $errorPage,
                'content' => $errorPageBuild->getErrorPageContent($errorPage, $hostingSubscription) ?? '',
                'path' => $errorPagePath
            ];
        }, $errorPages, array_keys($errorPages));
    }
}
