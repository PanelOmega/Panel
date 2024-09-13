<?php

namespace tests\Unit\Models;

use App\Models\CronJob;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Server\Helpers\PHP;
use App\Services\HostingSubscription\HostingSubscriptionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Unit\Traits\HasDocker;
use Tests\Unit\Traits\HasPHP;

class CronJobTest extends TestCase
{
    use HasPHP;
    use HasDocker;
    use DatabaseTransactions;

    public function testCreateCronJob() {
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

        $testSchedule = '0 0 1 * *';
        $testCommand = '/usr/bin/php /var/www/html/project/scripts/test_script.php';
        $testUsername = $testHostingSubscription->system_username;

        $testCreateCronJob = new CronJob();
        $testCreateCronJob->schedule = $testSchedule;
        $testCreateCronJob->command = $testCommand;
        $testCreateCronJob->user = $testUsername;
        $testCreateCronJob->save();

        $this->assertIsObject($testCreateCronJob);
        $this->assertDatabaseHas('cron_jobs', [
            'id' => $testCreateCronJob->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);

        $testCommand = "crontab -u {$testCreateCronJob->user} -l";
        $testCheckOnJob = shell_exec($testCommand);

        $this->assertTrue(str_contains($testCheckOnJob, $testCreateCronJob->user));
        $this->assertTrue(str_contains($testCheckOnJob, $testCreateCronJob->schedule));
        $this->assertTrue(str_contains($testCheckOnJob, $testCreateCronJob->command));
    }

    public function testUpdateCronJob() {
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

        $testSchedule = '0 0 1 * *';
        $testCommand = '/usr/bin/php /var/www/html/project/scripts/test_script.php';
        $testUsername = $testHostingSubscription->system_username;

        $testCreateCronJob = new CronJob();
        $testCreateCronJob->schedule = $testSchedule;
        $testCreateCronJob->command = $testCommand;
        $testCreateCronJob->user = $testUsername;
        $testCreateCronJob->save();

        $this->assertIsObject($testCreateCronJob);
        $this->assertDatabaseHas('cron_jobs', [
            'id' => $testCreateCronJob->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);

        $testScheduleUpdate = '* 9-17 * * 1-5';
        $testCommandUpdate = '/usr/bin/php /var/www/html/project/scriptUpdate/test_script_update.php';
        $testCreateCronJob->update([
            'schedule' => $testScheduleUpdate,
            'command' => $testCommandUpdate,
        ]);

        $testCommand = "crontab -u {$testCreateCronJob->user} -l";
        $testCheckOnJob = shell_exec($testCommand);

        $this->assertTrue(str_contains($testCheckOnJob, $testCreateCronJob->user));
        $this->assertTrue(str_contains($testCheckOnJob, $testScheduleUpdate));
        $this->assertTrue(str_contains($testCheckOnJob, $testCommandUpdate));
    }

    public function testDeleteCronJob() {
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

        $testSchedule = '0 0 1 * *';
        $testCommand = '/usr/bin/php /var/www/html/project/scripts/test_script.php';
        $testUsername = $testHostingSubscription->system_username;

        $testCreateCronJob = new CronJob();
        $testCreateCronJob->schedule = $testSchedule;
        $testCreateCronJob->command = $testCommand;
        $testCreateCronJob->user = $testUsername;
        $testCreateCronJob->save();

        $this->assertIsObject($testCreateCronJob);

        $testCreateCronJob->delete();
        $this->assertDatabaseMissing('cron_jobs', [
            'id' => $testCreateCronJob->id,
            'hosting_subscription_id' => $testHostingSubscription->id,
        ]);

        $testCommand = "crontab -u {$testCreateCronJob->user} -l";
        $testCheckOnJob = shell_exec($testCommand);

        $this->assertTrue(str_contains($testCheckOnJob, $testCreateCronJob->user));
        $this->assertFalse(str_contains($testCheckOnJob, $testCreateCronJob->schedule));
        $this->assertFalse(str_contains($testCheckOnJob, $testCreateCronJob->command));
    }
}
