<?php

namespace tests\Unit\Models;

use App\Models\ApiKey;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;

class ApiKeyTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateApiKeyWithWhitelistedApi() {
        $testName = 'test' . uniqid();
        $testWhitelistedIps = ['127.0.0.1'];

        $testCreateApyKey = new ApiKey();
        $testCreateApyKey->name = $testName;
        $testCreateApyKey->enable_whitelisted_ips = true;
        $testCreateApyKey->whitelisted_ips = $testWhitelistedIps;
        $testCreateApyKey->is_active = true;
        $testCreateApyKey->save();

        $this->assertIsObject($testCreateApyKey);
        $this->assertDatabaseHas('api_keys', [
            'id' => $testCreateApyKey->id,
            'whitelisted_ips' => json_encode($testCreateApyKey->whitelisted_ips),
        ]);

        $this->assertEquals(64, strlen($testCreateApyKey->api_key));
        $this->assertEquals(64, strlen($testCreateApyKey->api_secret));
        $this->assertIsBool($testCreateApyKey->enable_whitelisted_ips);
        $this->assertIsBool($testCreateApyKey->is_active);
        $this->assertTrue(is_array($testCreateApyKey->whitelisted_ips));
    }

    public function testCreateApiKeyWithoutWhitelistedIp() {
        $testName = 'test' . uniqid();

        $testCreateApyKey = new ApiKey();
        $testCreateApyKey->name = $testName;
        $testCreateApyKey->enable_whitelisted_ips = false;
        $testCreateApyKey->is_active = true;
        $testCreateApyKey->save();

        $this->assertIsObject($testCreateApyKey);
        $this->assertDatabaseHas('api_keys', [
            'id' => $testCreateApyKey->id
        ]);

        $this->assertEquals(64, strlen($testCreateApyKey->api_key));
        $this->assertEquals(64, strlen($testCreateApyKey->api_secret));
        $this->assertIsBool($testCreateApyKey->enable_whitelisted_ips);
        $this->assertIsBool($testCreateApyKey->is_active);
        $this->assertEmpty($testCreateApyKey->whitelisted_ips);
    }

    public function testDeleteApiKey() {
        $testName = 'test' . uniqid();
        $testWhitelistedIps = ['127.0.0.1'];

        $testCreateApyKey = new ApiKey();
        $testCreateApyKey->name = $testName;
        $testCreateApyKey->enable_whitelisted_ips = true;
        $testCreateApyKey->whitelisted_ips = $testWhitelistedIps;
        $testCreateApyKey->is_active = true;
        $testCreateApyKey->save();

        $this->assertIsObject($testCreateApyKey);

        $testCreateApyKey->delete();
        $this->assertDatabaseMissing('api_keys', [
            'id' => $testCreateApyKey->id,
            'whitelisted_ips' => json_encode($testCreateApyKey->whitelisted_ips),
        ]);
    }
}
