<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\Login as BasePage;

class DemoAdminLogin extends BasePage
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'admin@panelomega.com',
            'password' => 'admin',
            'remember' => true,
        ]);
    }
}
