<?php

namespace tests\Unit\Server\Helpers;

use App\Server\Helpers\LinuxUser;
use Tests\TestCase;

class LinuxUserTest extends TestCase
{
    public static $lastCreatedUser;

    public function testCreateLinuxUser(): void
    {
        $username = 'testuser'.rand(1, 1000);
        $password = 'testpassword';
        $email = $username.'@email.com';

        LinuxUser::createUser($username, $password, $email);

        self::$lastCreatedUser = $username;

        $this->assertNotEmpty($username);

    }

    public function testGetLinuxUser(): void
    {
        $user = LinuxUser::getUser(self::$lastCreatedUser);
        $this->assertIsArray($user);

        $this->assertEquals(self::$lastCreatedUser, $user[0]);
    }

    public function testCreateWebUser(): void
    {
        $username = 'testuser'.rand(1, 1000);
        $password = 'testpassword';

        LinuxUser::createWebUser($username, $password);

        self::$lastCreatedUser = $username;

        $this->assertNotEmpty($username);

    }

    public function testGetWebUser(): void
    {
        $user = LinuxUser::getUser(self::$lastCreatedUser);
        $this->assertIsArray($user);

        $this->assertEquals(self::$lastCreatedUser, $user[0]);

        $this->assertTrue(is_dir('/home/'.self::$lastCreatedUser));
    }
}
