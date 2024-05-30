<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (! is_file('.env')) {
            file_put_contents('.env', 'APP_ENV=testing');
        }
    }
}
