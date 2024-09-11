<?php

namespace tests\Unit\Models\HostingSubscription;
use App\Jobs\HtaccessBuildErrorPage;
use App\Jobs\Traits\ErrorCodeDefaultContentTrait;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription\ErrorPage;
use App\Models\HostingSubscription\ErrorPageBrowse;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class ErrorPageTest extends TestCase
{
    use HasDocker;
    use HasPHP;
    use DatabaseTransactions;

    public function testCreateErrorPageWithContent() {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            $testDomain,
            $testCreateCustomer->id,
            $testCreateHostingPlan->id,
            null,
            null
        );
        $this->assertTrue($createResponse['success']);
        $testHostingSubscription = $createResponse['hostingSubscription'];
        $this->assertNotEmpty($testHostingSubscription);
        Session::put('hosting_subscription_id', $testHostingSubscription->id);

        $testErrorPageBrowse = new ErrorPageBrowse();
        $testErrorPageNameArr = $testErrorPageBrowse->getErrorPages();
        $this->assertNotEmpty($testErrorPageNameArr);

        $testErrorPageName = $testErrorPageNameArr[rand(0, 28)];

        $testGetErrorCode = function ($pageName) {
            if (preg_match('/^\d+/', $pageName, $matches)) {
                return $matches[0];
            }
            return null;
        };

        $testErrorPageContent = '<h1>TestErrorPage</h1>';

        $testErrorPagePath = "/home/{$testHostingSubscription->system_username}/public_html";

        $testCreateErrorPage = new ErrorPage();
        $testCreateErrorPage->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateErrorPage->name = $testErrorPageName;
        $testCreateErrorPage->error_code = $testGetErrorCode($testErrorPageName);
        $testCreateErrorPage->content = $testErrorPageContent;
        $testCreateErrorPage->path = $testErrorPagePath;
        $testCreateErrorPage->save();

        $this->assertIsObject($testCreateErrorPage);
        $this->assertDatabaseHas('hosting_subscription_error_pages', [
            'id' => $testCreateErrorPage->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testErrorPageData = [
            'error_code' => $testCreateErrorPage->error_code,
            'path' => $testErrorPagePath,
            'content' => $testCreateErrorPage->content
        ];

        $testHtaccessBuildErrorPage = new HtaccessBuildErrorPage(false, $testHostingSubscription->id, $testErrorPageData);

        $testGetErrorPageContent = $testHtaccessBuildErrorPage->getErrorPageContent($testCreateErrorPage->name ,$testHostingSubscription);
        $testErrorPageDefaultContent = $testHtaccessBuildErrorPage->getErrorCodeDefaultContent($testCreateErrorPage->error_code);
        $this->assertNotEquals($testErrorPageDefaultContent, $testGetErrorPageContent);
        $testErrorPageFilePath = "{$testCreateErrorPage->path}/{$testCreateErrorPage->error_code}.shtml";
        $this->assertTrue(file_exists($testErrorPageFilePath));
        $testGetErrorDocuments = $testHtaccessBuildErrorPage->getAllErrorDocuments($testCreateErrorPage->hosting_subscription_id, $testCreateErrorPage->path);
        $this->assertNotEmpty($testGetErrorDocuments);
        $this->assertTrue(str_contains($testGetErrorDocuments[0], $testCreateErrorPage->error_code));
        $testHtaccessView = $testHtaccessBuildErrorPage->getHtaccessErrorCodesConfig($testGetErrorDocuments);
        $testErrorPageSystemFilePath = "{$testCreateErrorPage->path}/.htaccess";
        $testSystemFileContent = file_get_contents($testErrorPageSystemFilePath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
    }

    public function testCreateErrorPageWithoutContent() {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            $testDomain,
            $testCreateCustomer->id,
            $testCreateHostingPlan->id,
            null,
            null
        );
        $this->assertTrue($createResponse['success']);
        $testHostingSubscription = $createResponse['hostingSubscription'];
        $this->assertNotEmpty($testHostingSubscription);
        Session::put('hosting_subscription_id', $testHostingSubscription->id);

        $testErrorPageBrowse = new ErrorPageBrowse();
        $testErrorPageNameArr = $testErrorPageBrowse->getErrorPages();
        $this->assertNotEmpty($testErrorPageNameArr);

        $testErrorPageName = $testErrorPageNameArr[rand(0, 28)];

        $testGetErrorCode = function ($pageName) {
            if (preg_match('/^\d+/', $pageName, $matches)) {
                return $matches[0];
            }
            return null;
        };

        $testErrorPagePath = "/home/{$testHostingSubscription->system_username}/public_html";

        $testCreateErrorPage = new ErrorPage();
        $testCreateErrorPage->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateErrorPage->name = $testErrorPageName;
        $testCreateErrorPage->error_code = $testGetErrorCode($testErrorPageName);
        $testCreateErrorPage->content = '';
        $testCreateErrorPage->path = $testErrorPagePath;
        $testCreateErrorPage->save();

        $this->assertIsObject($testCreateErrorPage);
        $this->assertDatabaseHas('hosting_subscription_error_pages', [
            'id' => $testCreateErrorPage->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testErrorPageData = [
            'error_code' => $testCreateErrorPage->error_code,
            'path' => $testErrorPagePath,
            'content' => $testCreateErrorPage->content
        ];

        $testHtaccessBuildErrorPage = new HtaccessBuildErrorPage(false, $testHostingSubscription->id, $testErrorPageData);

        $testGetErrorPageContent = $testHtaccessBuildErrorPage->getErrorPageContent($testCreateErrorPage->name ,$testHostingSubscription);
        $testErrorPageDefaultContent = $testHtaccessBuildErrorPage->getErrorCodeDefaultContent($testCreateErrorPage->error_code);
        $this->assertEquals($testErrorPageDefaultContent, $testGetErrorPageContent);

        $testHtaccessBuildErrorPage->addErrorPageToSystem($testErrorPageData);
        $testErrorPageFilePath = "{$testCreateErrorPage->path}/{$testCreateErrorPage->error_code}.shtml";
        $this->assertTrue(file_exists($testErrorPageFilePath));
        $testGetErrorDocuments = $testHtaccessBuildErrorPage->getAllErrorDocuments($testCreateErrorPage->hosting_subscription_id, $testCreateErrorPage->path);
        $this->assertNotEmpty($testGetErrorDocuments);
        $this->assertTrue(str_contains($testGetErrorDocuments[0], $testCreateErrorPage->error_code));
        $testHtaccessView = $testHtaccessBuildErrorPage->getHtaccessErrorCodesConfig($testGetErrorDocuments);
        $testErrorPageSystemFilePath = "{$testCreateErrorPage->path}/.htaccess";
        $testSystemFileContent = file_get_contents($testErrorPageSystemFilePath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
    }

    public function testUpdateErrorPage() {
        $testCustomerUsername = 'test' . rand(1000, 9999);
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . rand(1000, 9999);
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $this->assertNotEmpty($testPhpVersion);

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . rand(1000, 9999);
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . rand(1000, 9999) . '.demo.panelomega-unit.com';
        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            $testDomain,
            $testCreateCustomer->id,
            $testCreateHostingPlan->id,
            null,
            null
        );
        $this->assertTrue($createResponse['success']);
        $testHostingSubscription = $createResponse['hostingSubscription'];
        $this->assertNotEmpty($testHostingSubscription);
        Session::put('hosting_subscription_id', $testHostingSubscription->id);

        $testErrorPageBrowse = new ErrorPageBrowse();
        $testErrorPageNameArr = $testErrorPageBrowse->getErrorPages();
        $this->assertNotEmpty($testErrorPageNameArr);

        $testErrorPageName = $testErrorPageNameArr[rand(0, 28)];

        $testGetErrorCode = function ($pageName) {
            if (preg_match('/^\d+/', $pageName, $matches)) {
                return $matches[0];
            }
            return null;
        };

        $testErrorPageContent = '<h1>TestErrorPage</h1>';

        $testErrorPagePath = "/home/{$testHostingSubscription->system_username}/public_html";

        $testCreateErrorPage = new ErrorPage();
        $testCreateErrorPage->hosting_subscription_id = $testHostingSubscription->id;
        $testCreateErrorPage->name = $testErrorPageName;
        $testCreateErrorPage->error_code = $testGetErrorCode($testErrorPageName);
        $testCreateErrorPage->content = $testErrorPageContent;
        $testCreateErrorPage->path = $testErrorPagePath;
        $testCreateErrorPage->save();

        $this->assertIsObject($testCreateErrorPage);
        $this->assertDatabaseHas('hosting_subscription_error_pages', [
            'id' => $testCreateErrorPage->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testErrorPageData = [
            'error_code' => $testCreateErrorPage->error_code,
            'path' => $testErrorPagePath,
            'content' => $testCreateErrorPage->content
        ];

        $testCreateErrorPage->update([
            'content' => '<h1>TestErrorPageUpdatedContent</h1>'
        ]);

        $testHtaccessBuildErrorPage = new HtaccessBuildErrorPage(false, $testHostingSubscription->id, $testErrorPageData);
        $testGetErrorPageContent = $testHtaccessBuildErrorPage->getErrorPageContent($testCreateErrorPage->name ,$testHostingSubscription);
        $this->assertNotEquals($testGetErrorPageContent, $testErrorPageContent);

        $testHtaccessBuildErrorPage->addErrorPageToSystem($testErrorPageData);
        $testErrorPageFilePath = "{$testCreateErrorPage->path}/{$testCreateErrorPage->error_code}.shtml";
        $this->assertTrue(file_exists($testErrorPageFilePath));
        $testGetErrorDocuments = $testHtaccessBuildErrorPage->getAllErrorDocuments($testCreateErrorPage->hosting_subscription_id, $testCreateErrorPage->path);
        $this->assertNotEmpty($testGetErrorDocuments);
        $this->assertTrue(str_contains($testGetErrorDocuments[0], $testCreateErrorPage->error_code));
        $testHtaccessView = $testHtaccessBuildErrorPage->getHtaccessErrorCodesConfig($testGetErrorDocuments);
        $testErrorPageSystemFilePath = "{$testCreateErrorPage->path}/.htaccess";
        $testSystemFileContent = file_get_contents($testErrorPageSystemFilePath);
        $this->assertTrue(str_contains(trim($testSystemFileContent), trim($testHtaccessView)));
    }
}
