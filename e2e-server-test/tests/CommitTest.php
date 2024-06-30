<?php
require_once __DIR__ . '/BaseTest.php';

class CommitTest extends BaseTest
{
    public function runTest()
    {
        if ($this->os == 'AlmaLinux-9.4') {
            $this->sshExec('yum install git -y', true, 8000);
        } else {
            $this->sshExec('apt-get install git -y', true, 8000);
        }

        $this->sshExec('git clone https://github.com/PanelOmega/Panel.git', true, 8000);

        $this->sshExec('cd Panel && git checkout dev', true);


        $this->sshExec('ls -la', true);

        $this->sshExec('chmod +x Panel/installers/'.strtolower($this->os).'/install-partial/install_base.sh dev');
        $this->sshExec('chmod +x Panel/installers/ubuntu-20.04/install-partial/install_web.sh dev');

        $this->sshExec('./Panel/installers/'.strtolower($this->os).'/install-partial/install_base.sh dev', true, 8000);

        $this->sshExec('cp -r Panel/web/ /usr/local/omega/web/', true);

        $this->sshExec('cd /usr/local/omega/web/ && wget https://getcomposer.org/download/latest-stable/composer.phar', true);
        $this->sshExec('cd /usr/local/omega/web/ && COMPOSER_ALLOW_SUPERUSER=1 omega-php composer.phar install', true, 8000);

        $this->sshExec('./Panel/installers/ubuntu-20.04/install-partial/install_web.sh dev', true, 8000);
        
        $testPassed = true;
        $this->sshExec('cd /usr/local/omega/web/ && omega-php artisan test', function ($data) use (&$testPassed) {
            echo "\033[0;34m " . $data . " \033[0m";
            if (str_contains($data, 'FAIL  Tests')) {
                $testPassed = false;
            }
            if (str_contains($data, 'FAIL  tests')) {
                $testPassed = false;
            }
            if (str_contains($data, 'FAILED  Tests')) {
                $testPassed = false;
            }
            if (str_contains($data, 'FAILED  tests')) {
                $testPassed = false;
            }
            if (str_contains($data, 'ErrorException')) {
                $testPassed = false;
            }
        }, 8000);

        return [
            'testPassed' => $testPassed
        ];
    }

}
