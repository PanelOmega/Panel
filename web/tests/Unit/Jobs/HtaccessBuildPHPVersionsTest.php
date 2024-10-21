<?php

namespace tests\Unit\Jobs;

use App\Jobs\HtaccessBuildPHPVersions;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class HtaccessBuildPHPVersionsTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testHandle() {
        $testCustomerUsername = 'test' . uniqid();
        $testCreateCustomer = new Customer();
        $testCreateCustomer->name = $testCustomerUsername;
        $testCreateCustomer->email = $testCustomerUsername . '@mail.com';
        $testCreateCustomer->username = $testCustomerUsername;
        $testCreateCustomer->password = time() . uniqid();
        $testCreateCustomer->save();
        $this->assertDatabaseHas('customers', ['username' => $testCustomerUsername]);

        Auth::guard('customer')->login($testCreateCustomer);
        $this->installDocker();
        $this->installPHP();

        $testPhpVersion = PHP::getInstalledPHPVersions()[0]['full'];
        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . uniqid();
        $testCreateHostingPlan->default_server_application_type = 'apache_php';
        $testCreateHostingPlan->default_server_application_settings = [
            'php_version' => $testPhpVersion,
            'enable_php_fpm' => true,
        ];
        $testCreateHostingPlan->save();
        $this->assertDatabaseHas('hosting_plans', ['name' => $testCreateHostingPlan->name]);

        $testDomain = 'test' . uniqid() . '.demo.panelomega-unit.com';
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

        $testCreateDomain = new Domain();
        $testCreateDomain->domain = $testDomain;
        $testCreateDomain->save();

        $this->assertIsObject($testCreateDomain);
        $this->assertDatabaseHas('domains', [
            'id' => $testCreateDomain->id,
            'hosting_subscription_id' => $testHostingSubscription->id
        ]);

        $testUpdateDomain = 'test' . uniqid() . '.demo.panelomega-update.com';
        $testCreateDomain->update([
            'domain' => $testUpdateDomain
        ]);

        $testHtAccessFileRealPath = "/home/$testHostingSubscription->system_username/public_html/.htaccess";
        $this->assertFileExists($testHtAccessFileRealPath);
        $testPhpVersion = PHP::getPHPVersion($testCreateDomain->server_application_settings['php_version']);

        $testStartComment = '# Section managed by Panel Omega: Default PHP Programing Language, do not edit';
        $testEndComment = '# End section managed by Panel Omega: Default PHP Programing Language';
        $testExpectedResult = "{$testPhpVersion['fileType']} {$testPhpVersion['fileExtensions']}";

        $testResult = file_get_contents($testHtAccessFileRealPath);

        $this->assertTrue(str_contains($testResult, trim($testStartComment)));
        $this->assertTrue(str_contains($testResult, trim($testEndComment)));
        $this->assertTrue(str_contains($testResult, trim($testExpectedResult)));
    }

    public function testGetHtAccessFileConfig() {
        $testPhpVersion = [
            'fileType' => 'application/x-httpd-remi-php81',
            'fileExtensions' => '.php .php8 .phtml',
        ];

        $testHostingSubscriptionId = rand(1000, 9999);
        $this->assertTrue(View::exists('server.samples.apache.htaccess.php-versions-htaccess'));

        $testExpectedResult = view('server.samples.apache.htaccess.php-versions-htaccess', [
            'phpVersion' => $testPhpVersion
        ])->render();

        $testBuilder = new HtaccessBuildPHPVersions(false, $testHostingSubscriptionId, $testPhpVersion);
        $testResult = $testBuilder->getHtaccessFileConfig($testPhpVersion);

        $this->assertEquals($testExpectedResult, $testResult);
    }
}
