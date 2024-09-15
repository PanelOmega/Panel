<?php

namespace tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateUser() {
        $testUsername = 'test' . uniqid();
        $testEmail = 'test' . uniqid() . '@test.com';
        $testPassword = 'test' . uniqid();

        $testCreateUser = new User();
        $testCreateUser->name = $testUsername;
        $testCreateUser->email = $testEmail;
        $testCreateUser->password = $testPassword;
        $testCreateUser->save();

        $this->assertIsObject($testCreateUser);
        $this->assertDatabaseHas('users', [
            'id' => $testCreateUser->id
        ]);

        $this->assertTrue(Hash::check($testPassword, $testCreateUser->password));
    }

    public function testUpdateUser() {
        $testUsername = 'test' . uniqid();
        $testEmail = 'test' . uniqid() . '@test.com';
        $testPassword = 'test' . uniqid();

        $testCreateUser = new User();
        $testCreateUser->name = $testUsername;
        $testCreateUser->email = $testEmail;
        $testCreateUser->password = $testPassword;
        $testCreateUser->save();

        $this->assertIsObject($testCreateUser);
        $this->assertDatabaseHas('users', [
            'id' => $testCreateUser->id
        ]);

        $testUpdateEmail = 'test' . uniqid() . '@updated.com';
        $testUpdatePassword = 'test' . uniqid() . 'updated';
        $testCreateUser->update([
            'email' => $testUpdateEmail,
            'password' => $testUpdatePassword
        ]);

        $this->assertEquals($testUpdateEmail, $testCreateUser->email);
        $this->assertTrue(Hash::check($testUpdatePassword, $testCreateUser->password));
    }

    public function testDeleteUser() {
        $testUsername = 'test' . uniqid();
        $testEmail = 'test' . uniqid() . '@test.com';
        $testPassword = 'test' . uniqid();

        $testCreateUser = new User();
        $testCreateUser->name = $testUsername;
        $testCreateUser->email = $testEmail;
        $testCreateUser->password = $testPassword;
        $testCreateUser->save();

        $this->assertIsObject($testCreateUser);
        $testCreateUser->delete();

        $this->assertDatabaseMissing('users', [
            'id' => $testCreateUser->id
        ]);
    }
}
