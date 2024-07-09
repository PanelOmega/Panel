<?php

namespace app\Console\Commands;

use App\Models\Admin;
use App\Models\CronJob;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Models\User;
use App\OmegaConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:reset-demo';

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
        $this->info('Resetting demo...');

        if (!OmegaConfig::get('APP_DEMO', false)) {
            $this->error('This command can only be run in demo environment');
            return;
        }

        $findHostingSubscriptions = HostingSubscription::all();
        foreach ($findHostingSubscriptions as $hostingSubscription) {
            $hostingSubscription->delete();
        }

        $findAdmins = Admin::all();
        foreach ($findAdmins as $admin) {
            $admin->delete();
        }

        $findUsers = User::all();
        foreach ($findUsers as $user) {
            $user->delete();
        }
        $findCustomers = Customer::all();
        foreach ($findCustomers as $customer) {
            $customer->delete();
        }
        $findHostingPlans = HostingPlan::all();
        foreach ($findHostingPlans as $hostingPlan) {
            $hostingPlan->delete();
        }

        $admin = new Admin();
        $admin->name = 'Admin';
        $admin->email = 'admin@panelomega.com';
        $admin->password = Hash::make('admin');
        $admin->save();

        $customer = new Customer();
        $customer->name = 'Vasil Levski';
        $customer->email = 'customer@panelomega.com';
        $customer->password = Hash::make('customer');
        $customer->save();

        $hostingPlan = new HostingPlan();
        $hostingPlan->name = 'Basic Plan';
        $hostingPlan->description = 'Basic hosting plan';
        $hostingPlan->save();

        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->domain = 'vasil-levski.demo.panelomega.com';
        $hostingSubscription->customer_id = $customer->id;
        $hostingSubscription->hosting_plan_id = $hostingPlan->id;
        $hostingSubscription->save();

        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->domain = 'wordpress.demo.panelomega.com';
        $hostingSubscription->customer_id = $customer->id;
        $hostingSubscription->hosting_plan_id = $hostingPlan->id;
        $hostingSubscription->save();

        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->domain = 'opencart.demo.panelomega.com';
        $hostingSubscription->customer_id = $customer->id;
        $hostingSubscription->hosting_plan_id = $hostingPlan->id;
        $hostingSubscription->save();

//        $findCronJob = CronJob::where('command', 'omega-shell omega:reset-demo')->first();
//        if (!$findCronJob) {
//            $cronJob = new CronJob();
//            $cronJob->schedule = '*/15 * * * *';
//            $cronJob->command = 'omega-shell omega:reset-demo';
//            $cronJob->user = 'root';
//            $cronJob->save();
//        }

        $this->info('Demo reset successfully');

    }
}
