<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateCustomerAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:create-customer-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating customer account...');

        $name = $this->ask('Enter name');
        $email = $this->ask('Enter email');
        $password = $this->secret('Enter password');

        try {
            $findByEmail = \App\Models\Customer::where('email', $email)->first();
            if ($findByEmail) {
                $this->error('Customer account with this email already exists');

                return;
            }
            $admin = new \App\Models\Customer();
            $admin->name = $name;
            $admin->email = $email;
            $admin->password = Hash::make($password);
            $admin->save();
        } catch (\Exception $e) {
            $this->error('Failed to create customer account');
            $this->error($e->getMessage());

            return;
        }

        $this->info('Customer account created successfully');
    }
}
