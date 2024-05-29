<?php
require_once __DIR__.'/BaseTest.php';

class CommitTest extends BaseTest
{
    public function runTest()
    {
        $this->sshExec('git clone https://github.com/PanelOmega/Panel.git', true, 8000);

        $this->sshExec('cd Panel && git checkout '.$this->gitBranch, true);

        $this->sshExec('chmod +x Panel/installers/ubuntu-22.04/install-partial/install_base.sh');
        $this->sshExec('chmod +x Panel/installers/ubuntu-20.04/install-partial/install_web.sh');

        $this->sshExec('./Panel/installers/ubuntu-22.04/install-partial/install_base.sh', true, 8000);

        $this->sshExec('cp -r Panel/web/ /usr/local/omega/web/',true);

        $this->sshExec('cd /usr/local/omega/web/ && wget https://getcomposer.org/download/latest-stable/composer.phar', true);
        $this->sshExec('cd /usr/local/omega/web/ && COMPOSER_ALLOW_SUPERUSER=1 omega-php composer.phar install', true, 8000);

        $this->sshExec('./Panel/installers/ubuntu-20.04/install-partial/install_web.sh', true, 8000);

          $testPassed = true;
          $this->sshExec('cd /usr/local/omega/web/ && omega-php artisan test', function ($data) use(&$testPassed) {
                echo "\033[0;34m " . $data . " \033[0m";
                if (str_contains($data, 'FAIL  Tests')) {
                    $testPassed = false;
                }
                if (str_contains($data, 'FAILED  Tests')) {
                    $testPassed = false;
                }
          }, 8000);

        return [
            'testPassed' => $testPassed
        ];
    }

}
