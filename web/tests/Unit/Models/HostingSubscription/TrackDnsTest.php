<?php

namespace Tests\Unit\Models\HostingSubscription;

use App\Models\HostingSubscription\TrackDns;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Session;

class TrackDnsTest extends TestCase
{
    public function testTrackDnsVaildDomain()
    {
        $testDomain = 'domain.com';
        Session::put('host', $testDomain);
        $this->assertTrue(Session::has('host'));
        $this->assertTrue(Session::get('host') == $testDomain);

        $testTraceData = TrackDns::getTraceroute($testDomain);
        $this->assertNotEmpty($testTraceData);

        $testTraceRows = array_map(function ($line, $index) use($testDomain) {
            return [
                'id' => $index + 1,
                'trace' => $line
            ];
        }, array_slice($testTraceData, 1), array_keys(array_slice($testTraceData, 1)));

        TrackDns::create();
        $testModel = new TrackDns();
        $testRows = $testModel->getRows();

        $this->assertEquals(count($testTraceRows), count($testRows));
    }

    public function testTrackDnsInvaildDomain()
    {
        $testDomain = 'domain.com.' . uniqid();
        Session::put('host', $testDomain);
        $this->assertTrue(Session::has('host'));
        $this->assertTrue(Session::get('host') == $testDomain);

        $testTraceData = TrackDns::getTraceroute($testDomain);
        $this->assertArrayHasKey('error', $testTraceData);

        $this->assertEquals('No output from traceroute', $testTraceData['error']);
    }

    public function testGetHostData()
    {
        $testDomain = 'domain.com';
        Session::put('host', $testDomain);
        $this->assertTrue(Session::has('host'));
        $this->assertTrue(Session::get('host') == $testDomain);

        TrackDns::create();
        $testHostData = TrackDns::getHostData();
        $this->assertNotEmpty($testHostData);
        $this->assertTrue(strpos($testHostData, $testDomain) !== false);
    }
}
