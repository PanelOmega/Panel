<?php

namespace App\FilamentCustomer\Pages;

use Filament\Pages\Page;

class PasswordAndSecurity extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.customer.pages.password-and-security';

    public string $mainTitle;
    public array $sections;

    public function mount(): void
    {
        $this->mainTitle = 'Password & Security';
        $this->sections = $this->getSections();
    }

    protected function getSections(): array
    {
        return [
            [
                'title' => 'Change Password',
                'helperTexts' => [
                    'Change your account password below. Password strength is important in web hosting; we strongly recommend using the Password Generator to create your password. Follow the tips below to keep your password safe.',
                    '<strong>Note:</strong> If you change your password, you will end your current session.'
                ]
            ],
            [
                'title' => 'Protect your password',
                'helperTexts' => [
                    'Don’t write down your password, memorize it. In particular, don’t write it down and leave it anywhere, and don’t place it in an unencrypted file! Use unrelated passwords for systems controlled by different organizations. Don’t give or share your password, in particular to someone claiming to be from computer support or a vendor unless you are sure they are who they say they are. Don’t let anyone watch you enter your password. Don’t enter your password on a computer you don’t trust. Use the password for a limited time and change it periodically.'
                ]
            ],
            [
                'title' => 'Choose a hard-to-guess password',
                'helperTexts' => [
                    'The system attempts to prevent particularly insecure passwords, but it is not foolproof.',
                    'Do not use words that are in a dictionary, names, or any personal information (for example, your birthday or phone number).',
                    'Avoid simple patterns. Instead, use UPPER and lower case letters, numbers, and symbols. Make certain that your password is at least eight characters long.',
                    'When you choose a new password, make certain that it is not related to your previous passwords.'
                ]
            ],
            [
                'title' => 'Enable Digest Authentication',
                'helperTexts' => [
                    'Windows Vista®, 7, and 8 require Digest Authentication for accessing your Web Disk over an unencrypted connection. If the server has an SSL certificate and you can connect via port 2078, you don’t need to enable this.'
                ]
            ]
        ];
    }


}
