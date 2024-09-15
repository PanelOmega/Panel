<?php

namespace tests\Unit\Models;

use App\Models\HostingPlan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;

class HostingPlanTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateHostingPlan() {
        $testAdditionalServices = [
            'microweber',
            'wordpress',
            'opencart'
        ];

        $testFeatures = [
            'ssl',
            'backup'
        ];

        $testLimitations = [
            'disk_space' => 100,
            'bandwidth' => 100,
            'daily_backups' => 1
        ];

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . uniqid();
        $testCreateHostingPlan->description = 'testDescription';
        $testCreateHostingPlan->disk_space = 1000;
        $testCreateHostingPlan->bandwidth = 1000;
        $testCreateHostingPlan->databases = rand(1, 5);
        $testCreateHostingPlan->ftp_accounts = rand(1, 5);
        $testCreateHostingPlan->email_accounts = rand(1, 5);
        $testCreateHostingPlan->subdomains = rand(1, 5);
        $testCreateHostingPlan->parked_domains = rand(1, 5);
        $testCreateHostingPlan->addon_domains = rand(1, 5);
        $testCreateHostingPlan->ssl_certificates = rand(1, 5);
        $testCreateHostingPlan->daily_backups = rand(1, 5);
        $testCreateHostingPlan->free_domain = 1;
        $testCreateHostingPlan->additional_services = $testAdditionalServices;
        $testCreateHostingPlan->features = $testFeatures;
        $testCreateHostingPlan->limitations = $testLimitations;
        $testCreateHostingPlan->default_server_application_type = 'apache_nodejs';
        $testCreateHostingPlan->save();

        $this->assertIsObject($testCreateHostingPlan);
        $this->assertDatabaseHas('hosting_plans', [
            'id' => $testCreateHostingPlan->id,
            'additional_services' => json_encode($testCreateHostingPlan->additional_services),
            'features' => json_encode($testCreateHostingPlan->features),
            'limitations' => json_encode($testCreateHostingPlan->limitations)
        ]);

        $this->assertEquals($testAdditionalServices, $testCreateHostingPlan->additional_services);
        $this->assertEquals($testFeatures, $testCreateHostingPlan->features);
        $this->assertEquals($testLimitations, $testCreateHostingPlan->limitations);
    }

    public function testUpdateHostingPlan() {
        $testAdditionalServices = [
            'microweber',
            'wordpress',
            'opencart'
        ];

        $testFeatures = [
            'ssl',
            'backup'
        ];

        $testLimitations = [
            'disk_space' => 100,
            'bandwidth' => 100,
            'daily_backups' => 1
        ];

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . uniqid();
        $testCreateHostingPlan->description = 'testDescription';
        $testCreateHostingPlan->disk_space = 1000;
        $testCreateHostingPlan->bandwidth = 1000;
        $testCreateHostingPlan->databases = rand(1, 5);
        $testCreateHostingPlan->ftp_accounts = rand(1, 5);
        $testCreateHostingPlan->email_accounts = rand(1, 5);
        $testCreateHostingPlan->subdomains = rand(1, 5);
        $testCreateHostingPlan->parked_domains = rand(1, 5);
        $testCreateHostingPlan->addon_domains = rand(1, 5);
        $testCreateHostingPlan->ssl_certificates = rand(1, 5);
        $testCreateHostingPlan->daily_backups = rand(1, 5);
        $testCreateHostingPlan->free_domain = 1;
        $testCreateHostingPlan->additional_services = $testAdditionalServices;
        $testCreateHostingPlan->features = $testFeatures;
        $testCreateHostingPlan->limitations = $testLimitations;
        $testCreateHostingPlan->default_server_application_type = 'apache_nodejs';
        $testCreateHostingPlan->save();

        $this->assertIsObject($testCreateHostingPlan);
        $this->assertDatabaseHas('hosting_plans', [
            'id' => $testCreateHostingPlan->id,
            'additional_services' => json_encode($testCreateHostingPlan->additional_services),
            'features' => json_encode($testCreateHostingPlan->features),
            'limitations' => json_encode($testCreateHostingPlan->limitations)
        ]);

        $testUpdateAdditionalServices = [
            'microweber'
        ];

        $testUpdateFeatures = [
            'ssl'
        ];

        $testUpdateLimitations = [];

        $testCreateHostingPlan->update([
            'additional_services' => $testUpdateAdditionalServices,
            'features' => $testUpdateFeatures,
            'limitations' => $testUpdateLimitations
        ]);

        $this->assertEquals($testUpdateAdditionalServices, $testCreateHostingPlan->additional_services);
        $this->assertEquals($testUpdateFeatures, $testCreateHostingPlan->features);
        $this->assertEmpty($testCreateHostingPlan->limitations);
    }

    public function testDeleteHostingPlan() {
        $testAdditionalServices = [
            'microweber',
            'wordpress',
            'opencart'
        ];

        $testFeatures = [
            'ssl',
            'backup'
        ];

        $testLimitations = [
            'disk_space' => 100,
            'bandwidth' => 100,
            'daily_backups' => 1
        ];

        $testCreateHostingPlan = new HostingPlan();
        $testCreateHostingPlan->name = 'test' . uniqid();
        $testCreateHostingPlan->description = 'testDescription';
        $testCreateHostingPlan->disk_space = 1000;
        $testCreateHostingPlan->bandwidth = 1000;
        $testCreateHostingPlan->databases = rand(1, 5);
        $testCreateHostingPlan->ftp_accounts = rand(1, 5);
        $testCreateHostingPlan->email_accounts = rand(1, 5);
        $testCreateHostingPlan->subdomains = rand(1, 5);
        $testCreateHostingPlan->parked_domains = rand(1, 5);
        $testCreateHostingPlan->addon_domains = rand(1, 5);
        $testCreateHostingPlan->ssl_certificates = rand(1, 5);
        $testCreateHostingPlan->daily_backups = rand(1, 5);
        $testCreateHostingPlan->free_domain = 1;
        $testCreateHostingPlan->additional_services = $testAdditionalServices;
        $testCreateHostingPlan->features = $testFeatures;
        $testCreateHostingPlan->limitations = $testLimitations;
        $testCreateHostingPlan->default_server_application_type = 'apache_nodejs';
        $testCreateHostingPlan->save();

        $this->assertIsObject($testCreateHostingPlan);
        $testCreateHostingPlan->delete();

        $this->assertDatabaseMissing('hosting_plans', [
            'id' => $testCreateHostingPlan->id,
            'additional_services' => json_encode($testCreateHostingPlan->additional_services),
            'features' => json_encode($testCreateHostingPlan->features),
            'limitations' => json_encode($testCreateHostingPlan->limitations)
        ]);
    }
}
