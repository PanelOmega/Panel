<?php

namespace tests\Unit\Models\Traits;

use App\Models\Traits\ErrorPageTrait;
use Illuminate\Foundation\Testing\TestCase;

class ErrorPageTraitTest extends TestCase
{
    use ErrorPageTrait;

    public function testGetErrorPages() {
        $testExpected = [
            '400 (Bad request)',
            '401 (Authorization required)',
            '403 (Forbidden)',
            '404 (Not found)',
            '405 (Method not allowed)',
            '406 (Not acceptable)',
            '407 (Proxy authentication required)',
            '408 (Request timeout)',
            '409 (Conflict)',
            '410 (Gone)',
            '411 (Length required)',
            '412 (Precondition failed)',
            '413 (Request entity too Large)',
            '414 (Request URI too long)',
            '415 (Unsupported media type)',
            '416 (Requested range not satisfiable)',
            '417 (Expectation failed)',
            '422 (Unprocessable entity)',
            '423 (Locked)',
            '424 (Failed dependency)',
            '500 (Internal server error)',
            '501 (Not implemented)',
            '502 (Bad gateway)',
            '503 (Service unavailable)',
            '504 (Gateway timeout)',
            '505 (HTTP version not supported)',
            '506 (Variant also negotiates)',
            '507 (Insufficient storage)',
            '508 (Not expected)',
        ];

        $testResult = self::getErrorPages();

        $this->assertEquals($testExpected, $testResult);
    }

    public function testGetErrorPagesTags() {
        $testExpected = [
            '#echo var="HTTP_REFERER"' => 'Referring URL',
            '#echo var="REMOTE_ADDR"' => 'Visitor`s IP Address',
            '#echo var="REQUEST_URI"' => 'Requested URL',
            '#echo var="HTTP_HOST"' => 'Server Name',
            '#echo var="HTTP_USER_AGENT"' => 'Visitor`s Browser',
            '#echo var="REDIRECT_STATUS"' => 'Redirect Status Code'
        ];

        $testResult = self::getErrorPagesTags();

        $this->assertEquals($testExpected, $testResult);
    }
}
