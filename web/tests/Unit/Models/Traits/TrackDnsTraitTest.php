<?php

namespace Tests\Unit\Models\Traits;

use App\Models\Traits\TrackDnsTrait;
use Illuminate\Foundation\Testing\TestCase;

class TrackDnsTraitTest extends TestCase
{
    use TrackDnsTrait;

    public function testGetDomainAddressesWithValidDomain()
    {
        $testDomain = 'domain.com';
        $testResult = self::getDomainAddresses($testDomain);
        $this->assertNotEmpty($testResult);
        $testHasAddress = !empty(preg_grep('/has address/', $testResult));
        $testMailHandled = !empty(preg_grep('/mail is handled by/', $testResult));

        $this->assertTrue($testHasAddress);
        $this->assertTrue($testMailHandled);
    }

    public function testGetDomainAddressesWithInvalidDomain()
    {
        $testDomain = 'domain.com.' . uniqid();
        $testResult = self::getDomainAddresses($testDomain);
        $this->assertEmpty($testResult);
    }

    public function testGetDomainZoneInformationWithValidDomain()
    {
        $testDomain = 'domain.com';
        $testResult = self::getDomainZoneInformation($testDomain);
        $this->assertNotEmpty($testResult);
        $testHasARecord = !empty(preg_grep('/\sIN\s+A\s/', $testResult));
        $testHasNSRecord = !empty(preg_grep('/\sIN\s+NS\s/', $testResult));
        $testHasSOARecord = !empty(preg_grep('/\sIN\s+SOA\s/', $testResult));
        $testHasMXRecord = !empty(preg_grep('/\sIN\s+MX\s/', $testResult));
        $testHasTXTRecord = !empty(preg_grep('/\sIN\s+TXT\s/', $testResult));

        $this->assertTrue($testHasARecord);
        $this->assertTrue($testHasNSRecord);
        $this->assertTrue($testHasSOARecord);
        $this->assertTrue($testHasMXRecord);
        $this->assertTrue($testHasTXTRecord);
    }

    public function testGetDomainZoneInformationWithInvalidDomain()
    {
        $testDomain = 'domain.com.' . uniqid();
        $testResult = self::getDomainZoneInformation($testDomain);
        $this->assertEmpty($testResult);
    }

    public function testGetTracerouteWithValidDomain()
    {
        $testDomain = 'domain.com';
        $testCommand = "traceroute {$testDomain}";
        $expectedResult = shell_exec($testCommand);
        $this->assertNotNull($expectedResult);

        $testResult = self::getTraceroute($testDomain);
        $this->assertArrayNotHasKey('error', $testResult);
        $this->assertArrayHasKey('host', $testResult);
        $this->assertTrue(str_contains($testResult['host'], $testDomain));
    }

    public function testGetTracerouteWithInvalidDomain()
    {
        $testDomain = 'domain.com.' . uniqid();
        $testCommand = "traceroute {$testDomain}";
        $expectedResult = shell_exec($testCommand);
        $this->assertNull($expectedResult);

        $testResult = self::getTraceroute($testDomain);
        $this->assertArrayHasKey('error', $testResult);
        $this->assertEquals('No output from traceroute', $testResult['error']);
    }
}
