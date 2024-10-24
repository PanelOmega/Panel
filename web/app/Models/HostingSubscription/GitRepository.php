<?php

namespace App\Models\HostingSubscription;

use App\GitClient;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\HostingSubscription;
use App\Models\Scopes\CustomerDomainScope;
use App\Models\Traits\GitVersionControlTrait;
use App\ShellApi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitRepository extends Model
{
    use HasFactory, GitVersionControlTrait;

    public $timestamps = true;

    const STATUS_PENDING = 'pending';

    const STATUS_CREATING = 'creating';

    const STATUS_CREATED = 'created';

    const STATUS_CLONING = 'cloning';
    const STATUS_CLONED = 'cloned';
    const STATUS_FAILED = 'failed';

    const STATUS_PULLING = 'pulling';

    const STATUS_PUSHING = 'pushing';

    const STATUS_PUSHED = 'pushed';

    const STATUS_UP_TO_DATE = 'up_to_date';

    protected $fillable = [
        'name',
        'url',
        'branch',
        'tag',
        'author',
        'clone_from',
        'last_commit_hash',
        'last_commit_message',
        'last_commit_date',
        'status',
        'status_message',
        'dir',
        'domain_id',
        'git_ssh_key_id',
        'deployment_script',
        'quick_deploy'
    ];

    protected $table = 'hosting_subscription_git_repositories';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CustomerDomainScope());
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $hostingSubscription = Customer::getHostingSubscriptionSession();
            $domainId = Domain::where('hosting_subscription_id', $hostingSubscription->id)->pluck('id')->first();
            $model->domain_id = $domainId;

            if (!GitSshKey::where('hosting_subscription_id', $hostingSubscription->id)->exists()) {
                GitSshKey::create([
                    'hosting_subscription_id' => $hostingSubscription->id,
                ]);
            }
        });

        static::created(function ($model) {
            $model->url ? $model->clone() : $model->createGitDirectory();
        });

        static::deleting(function ($model) {
            $projectDir = $model->domain->domain_root . '/' . $model->dir;
            ShellApi::safeDelete($projectDir, [
                $model->domain->domain_root . '/',
            ]);
        });
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    private function _getSSHKey($gitSshKeyId, $findHostingSubscription)
    {
        $gitSSHKey = GitSshKey::where('id', $gitSshKeyId)
            ->where('hosting_subscription_id', $findHostingSubscription->id)
            ->first();

        if ($gitSSHKey) {
            $sshPath = '/home/'.$findHostingSubscription->system_username .'/.ssh';
            $privateKeyFile = $sshPath.'/id_rsa_'. $gitSSHKey->id;
            $publicKeyFile = $sshPath.'/id_rsa_'.$gitSSHKey->id.'.pub';

            if (!is_dir($sshPath)) {
                shell_exec('mkdir -p ' . $sshPath);
            }

            shell_exec('chown '.$findHostingSubscription->system_username.':'.$findHostingSubscription->system_username.' -R ' . $sshPath);
            shell_exec('chmod 0700 ' . $sshPath);

            if (!file_exists($privateKeyFile)) {
                file_put_contents($privateKeyFile, $gitSSHKey->private_key);
            }

            shell_exec('chown '.$findHostingSubscription->system_username.':'.$findHostingSubscription->system_username.' ' . $privateKeyFile);
            shell_exec('chmod 0400 ' . $privateKeyFile);

            if (!file_exists($publicKeyFile)) {
                file_put_contents($publicKeyFile, $gitSSHKey->public_key);
            }

            shell_exec('chown '.$findHostingSubscription->system_username.':'.$findHostingSubscription->system_username.' ' . $publicKeyFile);
            shell_exec('chmod 0400 ' . $publicKeyFile);


            return [
                'privateKeyFile' => $privateKeyFile,
                'publicKeyFile' => $publicKeyFile,
            ];
        }
    }

    public function pull()
    {
        $this->status = self::STATUS_PULLING;
        $this->save();

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

        $projectDir = $findDomain->domain_root . '/' . $this->dir;

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

        $cloneUrl = 'git@'.$gitSSHUrl['provider'].':'.$gitSSHUrl['owner'].'/'.$gitSSHUrl['name'].'.git';

        $shellFile = $findDomain->domain_root . '/git/tmp/git-pull-' . $this->id . '.sh';
        $shellLog = $findDomain->domain_root . '/git/tmp/git-action-' . $this->id . '.log';

        shell_exec('mkdir -p ' . dirname($shellFile));
        shell_exec('chown '.$findHostingSubscription->system_username.':'.$findHostingSubscription->system_username.' -R ' . dirname(dirname($shellFile)));

        $shellContent = view('filament-customer.pages.git-version-control.git.pull-repo-user', [
            'gitProvider' => $gitSSHUrl['provider'],
            'systemUsername' => $findHostingSubscription->system_username,
            'gitRepositoryId' => $this->id,
            'cloneUrl' => $cloneUrl,
            'projectDir' => $projectDir,
            'privateKeyFile' => $privateKeyFile,
            'selfFile' => $shellFile,
            'deploymentScript'=>$this->deployment_script
        ])->render();

        file_put_contents($shellFile, $shellContent);


        $gitExecutorTempPath = storage_path('app/git/tmp');
        shell_exec('mkdir -p ' . $gitExecutorTempPath);

        $gitExecutorShellFile = $gitExecutorTempPath . '/git-pull-' . $this->id . '.sh';
        $gitExecutorShellFileLog = $gitExecutorTempPath . '/git-pull-' . $this->id . '.log';

        $gitExecutorContent = view('filament-customer.pages.git-version-control.git.git-executor', [
            'gitProvider' => $gitSSHUrl['provider'],
            'shellFile' => $shellFile,
            'shellLog' => $shellLog,
            'systemUsername' => $findHostingSubscription->system_username,
            'selfFile' => $gitExecutorShellFile,
            'afterCommand' => 'omega-php /usr/local/omega/web/artisan omega:git-repository-mark-as-pulled '.$this->id,
        ])->render();

        file_put_contents($gitExecutorShellFile, $gitExecutorContent);

        shell_exec('chmod +x ' . $gitExecutorShellFile);
        shell_exec('bash ' . $gitExecutorShellFile . ' >> ' . $gitExecutorShellFileLog . ' &');
    }

    public function clone()
    {
        $this->status = self::STATUS_CLONING;
        $this->save();

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

        $projectDir = $findDomain->domain_root . '/' . $this->dir;

        shell_exec('git config --global --add safe.directory ' . $projectDir);

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

        if ($privateKeyFile) {
            $cloneUrl = 'git@'.$gitSSHUrl['provider'].':'.$gitSSHUrl['owner']
                .'/'.$gitSSHUrl['name'].'.git';
        } else {
            $cloneUrl = 'https://'.$gitSSHUrl['provider'].'/'.$gitSSHUrl['owner']
                .'/'.$gitSSHUrl['name'].'.git';
        }

        $shellFile = $findDomain->domain_root . '/git/tmp/git-clone-' . $this->id . '.sh';
        $shellLog = $findDomain->domain_root . '/git/tmp/git-action-' . $this->id . '.log';

        shell_exec('mkdir -p ' . dirname($shellFile));
        shell_exec('chown '.$findHostingSubscription->system_username.':'.$findHostingSubscription->system_username.' -R ' . dirname(dirname($shellFile)));

        $shellContent = view('filament-customer.pages.git-version-control.git.clone-repo-user', [
            'gitProvider' => $gitSSHUrl['provider'],
            'systemUsername' => $findHostingSubscription->system_username,
            'gitRepositoryId' => $this->id,
            'cloneUrl' => $cloneUrl,
            'projectDir' => $projectDir,
            'privateKeyFile' => $privateKeyFile,
            'selfFile' => $shellFile,
            'deploymentScript' => ''
        ])->render();

        file_put_contents($shellFile, $shellContent);


        $gitExecutorTempPath = storage_path('app/git/tmp');
        shell_exec('mkdir -p ' . $gitExecutorTempPath);

        $gitExecutorShellFile = $gitExecutorTempPath . '/git-clone-' . $this->id . '.sh';
        $gitExecutorShellFileLog = $gitExecutorTempPath . '/git-clone-' . $this->id . '.log';

        $gitExecutorContent = view('filament-customer.pages.git-version-control.git.git-executor', [
            'gitProvider' => $gitSSHUrl['provider'],
            'shellFile' => $shellFile,
            'shellLog' => $shellLog,
            'systemUsername' => $findHostingSubscription->system_username,
            'selfFile' => $gitExecutorShellFile,
            'afterCommand' => 'omega-php /usr/local/omega/web/artisan omega:git-repository-mark-as-cloned ' . $this->id,
        ])->render();

        file_put_contents($gitExecutorShellFile, $gitExecutorContent);

        shell_exec('chmod +x ' . $gitExecutorShellFile);
        shell_exec('bash ' . $gitExecutorShellFile . ' >> ' . $gitExecutorShellFileLog . ' &');
    }

    public function push()
    {
        $this->status = self::STATUS_PUSHING;
        $this->save();

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

        $projectDir = $findDomain->domain_root . '/' . $this->dir;

        shell_exec("chown -R {$findHostingSubscription->system_username}:{$findHostingSubscription->system_username} {$projectDir}/.git");

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

        $cloneUrl = 'git@' . $gitSSHUrl['provider'] . ':' . $gitSSHUrl['owner'] . '/' . $gitSSHUrl['name'] . '.git';

        $shellFile = $findDomain->domain_root . '/git/tmp/git-push-' . $this->id . '.sh';
        $shellLog = $findDomain->domain_root . '/git/tmp/git-action-' . $this->id . '.log';

        shell_exec('mkdir -p ' . dirname($shellFile));
        shell_exec('chown ' . $findHostingSubscription->system_username . ':' . $findHostingSubscription->system_username . ' -R ' . dirname(dirname($shellFile)));

        $remoteName = 'origin';
        $branch = $this->branch;

        $shellContent = view('filament-customer.pages.git-version-control.git.push-repo-user', [
            'gitProvider' => $gitSSHUrl['provider'],
            'systemUsername' => $findHostingSubscription->system_username,
            'systemEmail' => $findHostingSubscription->system_username . '@' . $findHostingSubscription->domain,
            'gitRepositoryId' => $this->id,
            'cloneUrl' => $cloneUrl,
            'projectDir' => $projectDir,
            'privateKeyFile' => $privateKeyFile,
            'selfFile' => $shellFile,
            'remoteName' => $remoteName,
            'branch' => $branch,
            'deploymentScript' => $this->deployment_script ?? null
        ])->render();
        file_put_contents($shellFile, $shellContent);

        $gitExecutorTempPath = storage_path('app/git/tmp');
        shell_exec('mkdir -p ' . $gitExecutorTempPath);

        $gitExecutorShellFile = $gitExecutorTempPath . '/git-push-' . $this->id . '.sh';
        $gitExecutorShellFileLog = $gitExecutorTempPath . '/git-push-' . $this->id . '.log';

        $gitExecutorContent = view('filament-customer.pages.git-version-control.git.git-executor', [
            'gitProvider' => $gitSSHUrl['provider'],
            'shellFile' => $shellFile,
            'shellLog' => $shellLog,
            'systemUsername' => $findHostingSubscription->system_username,
            'selfFile' => $gitExecutorShellFile,
            'afterCommand' => ' ' . $this->id,
        ])->render();

        file_put_contents($gitExecutorShellFile, $gitExecutorContent);

        shell_exec('chmod +x ' . $gitExecutorShellFile);
        shell_exec('bash ' . $gitExecutorShellFile . ' >> ' . $gitExecutorShellFileLog . ' &');

    }

    public function getLog()
    {
        $findDomain = Domain::find($this->domain_id);
        if (!$findDomain) {
            return 'Domain not found';
        }

        $shellLog = $findDomain->domain_root . '/git/tmp/git-action-' . $this->id . '.log';
        if (file_exists($shellLog)) {
            $content = file_get_contents($shellLog);
            return nl2br($content);
        }

        return 'No logs';
    }

    public function setRepoData(): void
    {
        $path = "{$this->domain->domain_root}/{$this->dir}";
        shell_exec("omega-shell omega:git-config-add-dir {$path}");

        $currentDir = getcwd();
        sleep(5);
        chdir($path);

        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD'));

        $author = trim(shell_exec("git log -1 --pretty=format:%an origin/{$branch}"));

        !empty($author) ? $author .= ' <' . shell_exec("git log -1 --pretty=format:%ae origin/{$branch}") . '>' : '';

        $lastCommitDate = trim(shell_exec('git log -1 --pretty=format:%cd'));

        $dateTime = new \DateTime($lastCommitDate);
        $date = !empty($author) ? $dateTime->format('M d, Y h:i:s A') : '';

        $lastCommitHash = shell_exec("git log -1 --pretty=format:%H origin/{$branch}");
        $lastCommitMessage = shell_exec("git log -1 --pretty=format:%s origin/{$branch}");

        $cloneFrom = trim(shell_exec('git config --get remote.origin.url'));
        $statusShort = trim(shell_exec('git status --short'));

        $statusLong = str_contains(trim(shell_exec('git status')), 'Changes not staged')
            ? trim(shell_exec('git status | grep -A 4 "Changes not staged for commit"'))
            : trim(shell_exec('git status'));

        $this->update([
            'name' => $this->name,
            'branch' => $branch,
            'author' => $author,
            'clone_from' => $cloneFrom,
            'last_commit_hash' => $lastCommitHash,
            'last_commit_message' => $lastCommitMessage,
            'last_commit_date' => $date,
            'status' => $statusShort,
            'status_message' => $statusLong,
        ]);
        chdir($currentDir);
    }

    public function createGitDirectory()
    {
        $this->status = self::STATUS_CREATING;
        $this->save();

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

        $projectDir = $findDomain->domain_root . '/' . $this->dir;

        $commands = [
            'mkdir -p ' . $projectDir,
            'git init ' . $projectDir,
            'chown ' . $findHostingSubscription->system_username . ':' . $findHostingSubscription->system_username . ' -R ' . dirname(dirname($projectDir)),
            'chmod 700 ' . $projectDir . ' && chmod 711 ' . dirname($projectDir) . ' && chmod 700 ' . $projectDir . '/.git',
            'git config --global --add safe.directory ' . $projectDir
        ];

        foreach ($commands as $command) {
            shell_exec($command);
        }

        $this->status = self::STATUS_CREATED;
        $this->status_message = 'Directory created!';
        $this->save();
    }
}
