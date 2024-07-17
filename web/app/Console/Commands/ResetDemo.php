<?php

namespace app\Console\Commands;

use App\Models\Admin;
use App\Models\CronJob;
use App\Models\Customer;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Models\HostingSubscriptionFtpAccount;
use App\Models\User;
use App\OmegaConfig;
use App\Server\Helpers\CloudLinux\CloudLinuxPHPHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

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
        $ftpAccounts = HostingSubscriptionFtpAccount::all();
        foreach ($ftpAccounts as $ftpAccount) {
            $ftpAccount->delete();
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

        $hostingPlan = new HostingPlan();
        $hostingPlan->name = 'Pro Plan';
        $hostingPlan->description = 'Pro hosting plan';
        $hostingPlan->save();

        $hostingPlan = new HostingPlan();
        $hostingPlan->name = 'Premium Plan';
        $hostingPlan->description = 'Premium hosting plan';
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

        $this->installWordpress($hostingSubscription);


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

    public function installWordpress($hostingSubscription)
    {
        $createDatabase = new Database();
        $createDatabase->hosting_subscription_id = $hostingSubscription->id;
        $createDatabase->database_name = 'wordpress';
        $createDatabase->save();

        $createDatabaseUser = new DatabaseUser();
        $createDatabaseUser->database_id = $createDatabase->id;
        $createDatabaseUser->username = 'wordpress';
        $createDatabaseUser->password = md5(rand(100000, 999999)) . time() . rand(100000, 999999);
        $createDatabaseUser->save();

        $wpCli = '/home/'.$hostingSubscription->system_username.'/wp-cli.phar';

        $log = '';
        if (!is_file($wpCli)) {
            $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar');
            $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- chmod +x wp-cli.phar');
        }

        $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- '.$wpCli.' core download --path=/home/'.$hostingSubscription->system_username.'/public_html');

        return $log;
    }
}
