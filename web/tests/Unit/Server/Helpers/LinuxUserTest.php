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

        $createUser = LinuxUser::createUser($username, $password, $email);

        $this->assertArrayHasKey('success', $createUser);

        self::$lastCreatedUser = $username;


    }

    public function testGetLinuxUser(): void
    {
        $user = LinuxUser::getUser(self::$lastCreatedUser);
        $this->assertIsArray($user);

        $this->assertEquals(self::$lastCreatedUser, $user[0]);
    }

    public function testCreateExistingLinuxUser()
    {
        $createUser = LinuxUser::createUser(self::$lastCreatedUser, 'testpassword', 'testemail@mail.com');

        $this->assertArrayHasKey('error', $createUser);
        $this->assertSame('User already exists', $createUser['error']);

    }

    public function testCreateLinuxWebUser(): void
    {
        $username = 'testuser'.rand(1, 1000);
        $password = 'testpassword';

        LinuxUser::createWebUser($username, $password);

        self::$lastCreatedUser = $username;

        $this->assertNotEmpty($username);

    }

    public function testGetLinuxWebUser(): void
    {
        $user = LinuxUser::getUser(self::$lastCreatedUser);
        $this->assertIsArray($user);

        $this->assertEquals(self::$lastCreatedUser, $user[0]);

        $this->assertTrue(is_dir('/home/'.self::$lastCreatedUser));
    }

    public function testCreateExistingLinuxWebUser()
    {
        $createUser = LinuxUser::createWebUser(self::$lastCreatedUser, 'testpassword');

        $this->assertArrayHasKey('error', $createUser);
        $this->assertSame('User already exists', $createUser['error']);

    }

    public function testDeleteLinuxWebUser()
    {
        $deleteUser = LinuxUser::deleteUser(self::$lastCreatedUser);
        $this->assertArrayHasKey('success', $deleteUser);

        $this->assertFalse(is_dir('/home/'.self::$lastCreatedUser));
    }
}
