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
        $testUsername = 'test' . rand(1000, 9999);
        $testEmail = 'test' . rand(1000, 9999) . '@test.com';
        $testPassword = 'test' . rand(1000, 9999);

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
        $testUsername = 'test' . rand(1000, 9999);
        $testEmail = 'test' . rand(1000, 9999) . '@test.com';
        $testPassword = 'test' . rand(1000, 9999);

        $testCreateUser = new User();
        $testCreateUser->name = $testUsername;
        $testCreateUser->email = $testEmail;
        $testCreateUser->password = $testPassword;
        $testCreateUser->save();

        $this->assertIsObject($testCreateUser);
        $this->assertDatabaseHas('users', [
            'id' => $testCreateUser->id
        ]);

        $testUpdateEmail = 'test' . rand(1000, 9999) . '@updated.com';
        $testUpdatePassword = 'test' . rand(1000, 9999) . 'updated';
        $testCreateUser->update([
            'email' => $testUpdateEmail,
            'password' => $testUpdatePassword
        ]);

        $this->assertEquals($testUpdateEmail, $testCreateUser->email);
        $this->assertTrue(Hash::check($testUpdatePassword, $testCreateUser->password));
    }

    public function testDeleteUser() {
        $testUsername = 'test' . rand(1000, 9999);
        $testEmail = 'test' . rand(1000, 9999) . '@test.com';
        $testPassword = 'test' . rand(1000, 9999);

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
