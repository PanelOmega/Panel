<?php

namespace tests\Unit\Models;

use App\Models\Admin;
use Filament\Panel;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;

class AdminTest extends TestCase
{
    public function testCreateAdmin() {
        $testName = 'test' . uniqid();
        $testEmail = 'test' . uniqid() . '@panelomega-unit.com';
        $testPassword = 'test' . uniqid();

        $testCreateAdmin = new Admin();
        $testCreateAdmin->name = $testName;
        $testCreateAdmin->email = $testEmail;
        $testCreateAdmin->password = $testPassword;
        $testCreateAdmin->save();

        $this->assertIsObject($testCreateAdmin);
        $this->assertDatabaseHas('admins', [
            'id' => $testCreateAdmin->id
        ]);

        $this->assertTrue(Hash::check($testPassword, $testCreateAdmin->password));
    }

    public function testDeleteAdmin() {
        $testName = 'test' . uniqid();
        $testEmail = 'test' . uniqid() . '@panelomega-unit.com';
        $testPassword = 'test' . uniqid();

        $testCreateAdmin = new Admin();
        $testCreateAdmin->name = $testName;
        $testCreateAdmin->email = $testEmail;
        $testCreateAdmin->password = $testPassword;
        $testCreateAdmin->save();

        $this->assertIsObject($testCreateAdmin);

        $testCreateAdmin->delete();
        $this->assertDatabasemissing('admins', [
            'id' => $testCreateAdmin->id
        ]);
    }

    public function testCanAccessPanel() {

        $testName = 'test' . uniqid();
        $testEmail = 'test' . uniqid() . '@panelomega-unit.com';
        $testPassword = 'test' . uniqid();

        $testCreateAdmin = new Admin();
        $testCreateAdmin->name = $testName;
        $testCreateAdmin->email = $testEmail;
        $testCreateAdmin->password = $testPassword;
        $testCreateAdmin->save();

        $this->assertIsObject($testCreateAdmin);
        $this->assertDatabaseHas('admins', [
            'id' => $testCreateAdmin->id
        ]);

        $panel = new Panel();
        $this->assertTrue($testCreateAdmin->canAccessPanel($panel));
    }
}
