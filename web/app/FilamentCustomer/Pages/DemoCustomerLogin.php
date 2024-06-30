<?php

namespace App\FilamentCustomer\Pages;

use Filament\Pages\Auth\Login as BasePage;

class DemoCustomerLogin extends BasePage
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'customer@panelomega.com',
            'password' => 'customer',
            'remember' => true,
        ]);
    }
}
