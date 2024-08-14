<?php

namespace App\Services\HostingSubscription;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Server\Helpers\LinuxUser;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class HostingSubscriptionService
{
    /**
     * Create a new hosting subscription
     * @param string $domain
     * @param int $customerId
     * @param int $hostingPlanId
     * @param string $systemUsername
     * @param string $systemPassword
     * @return void
     */
    public function create(string $domain, int $customerId, int $hostingPlanId, string|null $systemUsername, string|null $systemPassword)
    {
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \Exception('Invalid domain');
        }

        $findDomain = Domain::where('domain', $domain)->first();
        if ($findDomain) {
            throw new \Exception('Domain already exists');
        }

        $findCustomer = Customer::where('id', $customerId)->first();
        if (!$findCustomer) {
            throw new \Exception('Customer not found');
        }

        $findHostingPlan = HostingPlan::where('id', $hostingPlanId)->first();
        if (!$findHostingPlan) {
            throw new \Exception('Hosting plan not found');
        }

        if (!empty($systemUsername)) {
            $linuxUser = LinuxUser::getUser($systemUsername);
            if (!empty($linuxUser)) {
                throw new \Exception('System username already exists');
            }
            if (empty($systemPassword)) {
                throw new \Exception('System password is required');
            }
        } else {
            $systemUsername = $this->_generateUsername($domain . $customerId);
            if ($this->_startsWithNumber($systemUsername)) {
                $systemUsername = $this->_generateUsername(Str::random(4));
            }
            $linuxUser = LinuxUser::getUser($systemUsername);
            if (!empty($linuxUser)) {
                $systemUsername = $this->_generateUsername($systemUsername . $customerId . Str::random(4));
            }
            $systemPassword = Str::random(14);
        }


        $createLinuxWebUserOutput = LinuxUser::createWebUser($systemUsername, $systemPassword);
        if (!isset($createLinuxWebUserOutput['success'])) {
            throw new \Exception('Failed to create system user');
        }
        if (!isset($createLinuxWebUserOutput['linuxUserId'])) {
            throw new \Exception('Failed to create system user');
        }

        $systemUserId = $createLinuxWebUserOutput['linuxUserId'];

        $hostingSubscription = new HostingSubscription();
        $hostingSubscription->domain = $domain;
        $hostingSubscription->customer_id = $customerId;
        $hostingSubscription->hosting_plan_id = $hostingPlanId;
        $hostingSubscription->system_username = $systemUsername;
        $hostingSubscription->system_password = $systemPassword;
        $hostingSubscription->system_user_id = $systemUserId;
        $hostingSubscription->save();

        $domain = new Domain();
        $domain->hosting_subscription_id = $hostingSubscription->id;
        $domain->domain = $hostingSubscription->domain;
        $domain->is_main = 1;
        $domain->status = Domain::STATUS_ACTIVE;
        $domain->save();

        if (($hostingSubscription->id > 0) && ($domain->id > 0)) {

            $recipient = auth()->user();
            $recipient->notify(
                Notification::make()
                    ->title('Hosting Subscription Created')
                    ->toDatabase(),
            );

            return [
                'success' => true,
                'message' => 'Hosting subscription created successfully',
                'hostingSubscription' => $hostingSubscription
            ];
        }

        throw new \Exception('Failed to create hosting subscription');
    }


    private function _startsWithNumber($string)
    {
        return strlen($string) > 0 && ctype_digit(substr($string, 0, 1));
    }

    private function _generateUsername($string)
    {
        $removedMultispace = preg_replace('/\s+/', ' ', $string);
        $sanitized = preg_replace('/[^A-Za-z0-9\ ]/', '', $removedMultispace);
        $lowercased = strtolower($sanitized);
        $lowercased = str_replace(' ', '', $lowercased);
        $lowercased = trim($lowercased);
        if (strlen($lowercased) > 10) {
            $lowercased = substr($lowercased, 0, 4);
        }

        $username = $lowercased . rand(1111, 9999) . Str::random(4);
        $username = strtolower($username);

        return $username;
    }
}
