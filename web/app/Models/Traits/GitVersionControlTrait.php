<?php

namespace App\Models\Traits;

use App\GitClient;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingSubscription;

trait GitVersionControlTrait
{
    public static function getDirectoryPath()
    {
        $hostingSunscription = Customer::getHostingSubscriptionSession();
        return "/home/{$hostingSunscription->system_username}";
    }

    public function getRepoBranches()
    {
        $path = "{$this->domain->domain_root}/{$this->dir}";
        $currentDir = getcwd();
        chdir($path);

        $findDomain = Domain::find($this->domain_id);
        if (!$findDomain) {
            $this->status = self::STATUS_FAILED;
            $this->status_message = 'Domain not found';
            $this->save();
            return;
        }

        $findHostingSubscription = HostingSubscription::find($findDomain->hosting_subscription_id);
        if (!$findHostingSubscription) {
            $this->status = self::STATUS_FAILED;
            $this->status_message = 'Hosting Subscription not found';
            $this->save();
            return;
        }

        $privateKeyFile = null;
        $getSSHKey = $this->_getSSHKey($this->git_ssh_key_id, $findHostingSubscription);
        if (isset($getSSHKey['privateKeyFile'])) {
            $privateKeyFile = $getSSHKey['privateKeyFile'];
        }

        $gitSSHUrl = GitClient::parseGitUrl($this->url);
        if (!isset($gitSSHUrl['provider'])) {
            $this->status = self::STATUS_FAILED;
            $this->status_message = 'Provider not found';
            $this->save();
            return;
        }

        if($privateKeyFile) {
            $url = 'git@' . $gitSSHUrl['provider'] . ':' . $gitSSHUrl['owner'] . '/' . $gitSSHUrl['name'] . '.git';
        } else {
            $url = $this->url;
        }

        shell_exec('GIT_TERMINAL_PROMPT=0');

        $command = $privateKeyFile
            ? "git -c core.sshCommand=\"ssh -i {$privateKeyFile}\" ls-remote --heads {$url}"
            : "git ls-remote --heads {$url}";

        $remoteBranches = shell_exec($command) ?: '';

        $localBranches = shell_exec('git branch') ?: '';

        $branches = [];

        foreach (explode("\n", $remoteBranches) as $line) {
            $parts = explode("\t", $line);
            if (!empty($parts[1])) {
                $branch = str_replace('refs/heads/', '', $parts[1]);
                $branches[$branch] = $branch;
            }
        }

        foreach (explode("\n", $localBranches) as $line) {
            $branch = trim(str_replace('*', '', $line));
            if (!empty($branch)) {
                $branches[$branch] = $branch;
            }
        }

        chdir($currentDir);
        return $branches;
    }
}
