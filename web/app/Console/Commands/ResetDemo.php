<?php

namespace app\Console\Commands;

use App\Models\Admin;
use App\Models\CronJob;
use App\Models\Customer;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Models\HostingSubscription\FtpAccount;
use App\Models\User;
use App\OmegaConfig;
use App\Server\Helpers\CloudLinux\CloudLinuxPHPHelper;
use App\Services\HostingSubscription\HostingSubscriptionService;
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

       // $findHostingSubscription = HostingSubscription::where('id',187)->first();
        //$this->installOpenCart($findHostingSubscription);

        $this->info('Resetting demo...');

        if (!OmegaConfig::get('APP_DEMO', false)) {
            $this->error('This command can only be run in demo environment');
            return;
        }

        $findHostingSubscriptions = HostingSubscription::all();
        foreach ($findHostingSubscriptions as $hostingSubscription) {
            $hostingSubscription->delete();
        }
        $findDomains = Domain::all();
        foreach ($findDomains as $domain) {
            $domain->delete();
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
        $ftpAccounts = FtpAccount::all();
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

        Auth::guard('customer')->login($customer);


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


        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            'vasil-levski.demo.panelomega.com',
            $customer->id,
            $hostingPlan->id,
            null,
            null
        );

        $wildCardDomain = '.omega.vanesa.ai';

        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            'wordpress'.$wildCardDomain,
            $customer->id,
            $hostingPlan->id,
            null,
            null
        );
        $this->installWordpress($createResponse['hostingSubscription']);


        $hostingSubscriptionService = new HostingSubscriptionService();
        $createResponse = $hostingSubscriptionService->create(
            'opencart'.$wildCardDomain,
            $customer->id,
            $hostingPlan->id,
            null,
            null
        );
        $this->installOpenCart($createResponse['hostingSubscription']);
//
//        $hostingSubscription = new HostingSubscription();
//        $hostingSubscription->domain = 'presta-shop.demo.panelomega.com';
//        $hostingSubscription->customer_id = $customer->id;
//        $hostingSubscription->hosting_plan_id = $hostingPlan->id;
//        $hostingSubscription->save();
//        $this->installPrestaShop($hostingSubscription);



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

    public function installPrestaShop($hostingSubscription)
    {
        $createDatabase = new Database();
        $createDatabase->hosting_subscription_id = $hostingSubscription->id;
        $createDatabase->database_name = 'prestashop';
        $createDatabase->save();

        $createDatabaseUser = new DatabaseUser();
        $createDatabaseUser->database_id = $createDatabase->id;
        $createDatabaseUser->username = 'prestashop';
        $createDatabaseUser->password = md5(rand(100000, 999999)) . time();
        $createDatabaseUser->save();

        $databaseName = $createDatabase->database_name_prefix . $createDatabase->database_name;
        $databaseUser = $createDatabaseUser->username_prefix . $createDatabaseUser->username;

        shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/public_html/*');

        shell_exec('wget https://github.com/PrestaShop/PrestaShop/archive/refs/heads/8.0.x.zip');
        shell_exec('mv 8.0.x.zip /home/'.$hostingSubscription->system_username.'/prestashop.zip');
        shell_exec('unzip /home/'.$hostingSubscription->system_username.'/prestashop.zip -d /home/'.$hostingSubscription->system_username.'/');

        //  Rsync
        shell_exec('rsync -av /home/'.$hostingSubscription->system_username.'/PrestaShop-8.0.x/ /home/'.$hostingSubscription->system_username.'/public_html/');

        // Remove old files
        shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/prestashop.zip');

        // Change owner
        shell_exec('chown -R '.$hostingSubscription->system_username.':'.$hostingSubscription->system_username.' /home/'.$hostingSubscription->system_username.'/public_html/');
        shell_exec('chmod -R 755 /home/'.$hostingSubscription->system_username.'/public_html/');

        $psAdminUser = 'ps-admin';
        $psAdminUserPass = substr(md5(rand(100000, 999999).time()).rand(100000, 999999), 0, 20);

        // Install composer
        shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- composer install -d /home/'.$hostingSubscription->system_username.'/public_html/');


        $log = '';

        $commands = [];
        $commands[] = 'sudo -u '.$hostingSubscription->system_username.' -i -- php /home/'.$hostingSubscription->system_username.'/public_html/install-dev/index_cli.php ';
        $commands[] = '--domain='.$hostingSubscription->domain;
        $commands[] = '--db_server=localhost';
        $commands[] = '--db_name='.$databaseName;
        $commands[] = '--db_user='.$databaseUser;
        $commands[] = '--db_password='.$createDatabaseUser->password;
        $commands[] = '--prefix=ps_';
        $commands[] = '--email='.$psAdminUser.'@panelomega.com';
        $commands[] = '--password='.$psAdminUserPass;
        $commands[] = '--name=prestashop';
        $commands[] = '--country=BG';
        $commands[] = '--language=bg';
        $commands[] = '--timezone=Europe/Sofia';

        $execCommand = implode(' ', $commands);
        $log .= shell_exec($execCommand);

        dd([
            'log' => $log,
            'commands' => $execCommand,
        ]);

        // Remove install directory
      //  shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/public_html/install-dev/');
        shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/PrestaShop-8.0.x/');

        dd([
            'databaseName' => $databaseName,
            'databaseUser' => $databaseUser,
        ]);

    }

    public function installOpenCart($hostingSubscription)
    {
        $createDatabase = new Database();
        $createDatabase->hosting_subscription_id = $hostingSubscription->id;
        $createDatabase->database_name = 'opencart';
        $createDatabase->save();

        $createDatabaseUser = new DatabaseUser();
        $createDatabaseUser->database_id = $createDatabase->id;
        $createDatabaseUser->username = 'opencart';
        $createDatabaseUser->password = md5(rand(100000, 999999)) . time();
        $createDatabaseUser->save();

        $databaseName = $createDatabase->database_name_prefix . $createDatabase->database_name;
        $databaseUser = $createDatabaseUser->username_prefix . $createDatabaseUser->username;

        shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/public_html/*');

        shell_exec('wget https://github.com/opencart/opencart/archive/refs/heads/master.zip -O /home/'.$hostingSubscription->system_username.'/opencart.zip');
        shell_exec('unzip /home/'.$hostingSubscription->system_username.'/opencart.zip -d /home/'.$hostingSubscription->system_username.'/');

       //  Rsync
        shell_exec('rsync -av /home/'.$hostingSubscription->system_username.'/opencart-master/upload/ /home/'.$hostingSubscription->system_username.'/public_html/');

        // Remove old files
        shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/opencart.zip');
        shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/opencart-master');

        // Rename config files
        shell_exec('mv /home/'.$hostingSubscription->system_username.'/public_html/config-dist.php /home/'.$hostingSubscription->system_username.'/public_html/config.php');
        shell_exec('mv /home/'.$hostingSubscription->system_username.'/public_html/admin/config-dist.php /home/'.$hostingSubscription->system_username.'/public_html/admin/config.php');

        // Change owner
        shell_exec('chown -R '.$hostingSubscription->system_username.':'.$hostingSubscription->system_username.' /home/'.$hostingSubscription->system_username.'/public_html/');
        shell_exec('chmod -R 755 /home/'.$hostingSubscription->system_username.'/public_html/');
        shell_exec('chmod -R 777 /home/'.$hostingSubscription->system_username.'/public_html/system/storage');
        shell_exec('chmod -R 777 /home/'.$hostingSubscription->system_username.'/public_html/image');

        $ocAdminUser = 'oc-admin';
        $ocAdminUserPass = substr(md5(rand(100000, 999999).time()).rand(100000, 999999), 0, 20);

        $log = '';

        $commands = [];
        $commands[] = 'sudo -u '.$hostingSubscription->system_username.' -i -- php /home/'.$hostingSubscription->system_username.'/public_html/install/cli_install.php install ';
        $commands[] = '--username '.$ocAdminUser;
        $commands[] = '--email '.$ocAdminUser.'@panelomega.com';
        $commands[] = '--password '.$ocAdminUserPass;
        $commands[] = '--http_server http://'.$hostingSubscription->domain.'/';
        $commands[] = '--db_driver mysqli';
        $commands[] = '--db_hostname localhost';
        $commands[] = '--db_username '.$databaseUser;
        $commands[] = '--db_password '.$createDatabaseUser->password;
        $commands[] = '--db_database '.$databaseName;
        $commands[] = '--db_port 3306';
        $commands[] = '--db_prefix oc_';

        $execCommand = implode(' ', $commands);
        $log .= shell_exec($execCommand);

        // Remove install directory
        shell_exec('rm -rf /home/'.$hostingSubscription->system_username.'/public_html/install/');

        return $log;
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
        $createDatabaseUser->password = md5(rand(100000, 999999)) . time();
        $createDatabaseUser->save();

        $databaseName = $createDatabase->database_name_prefix . $createDatabase->database_name;
        $databaseUser = $createDatabaseUser->username_prefix . $createDatabaseUser->username;

        $wpCli = '/home/'.$hostingSubscription->system_username.'/wp-cli.phar';

        $log = '';
        if (!is_file($wpCli)) {
            $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar');
            $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- chmod +x wp-cli.phar');
        }

        // Download Wordpress
        $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- '.$wpCli.' core download --path=/home/'.$hostingSubscription->system_username.'/public_html');

        // Create wp-config.php
        $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- '.$wpCli.' config create --path=/home/'.$hostingSubscription->system_username.'/public_html --dbname='.$databaseName.' --dbuser='.$databaseUser.' --dbpass='.$createDatabaseUser->password.' --url='.$hostingSubscription->domain);


        $wpAdminUser = 'admin';
        $wpAdminUserPass = md5(rand(100000, 999999).time()).rand(100000, 999999);

        $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- '.$wpCli.' core install --path=/home/'.$hostingSubscription->system_username.'/public_html --title=PanelOmegaWordpress --admin_user='.$wpAdminUser.' --admin_password='.$wpAdminUserPass.' --admin_email='.$wpAdminUser.'@panelomega.com --url='.$hostingSubscription->domain);

        $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- '.$wpCli.' option update home http://'.$hostingSubscription->domain .' --path=/home/'.$hostingSubscription->system_username.'/public_html');
        $log .= shell_exec('sudo -u '.$hostingSubscription->system_username.' -i -- '.$wpCli.' option update siteurl http://'.$hostingSubscription->domain .' --path=/home/'.$hostingSubscription->system_username.'/public_html');

        return $log;
    }
}
