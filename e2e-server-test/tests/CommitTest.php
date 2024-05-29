<?php
require_once __DIR__.'/BaseTest.php';

class CommitTest extends BaseTest
{
    public function runTest()
    {
//        echo $this->sshRunCommand('rm -rf Panel');
//        echo $this->sshRunCommand('git clone https://github.com/PanelOmega/Panel.git');
//
        echo $this->sshRunCommand('cd Panel');
//        echo $this->sshRunCommand('ls -la');
//        echo $this->sshRunCommand('git checkout '.$this->gitBranch);
//
//        echo $this->sshRunCommand('chmod +x installers/ubuntu-22.04/install-partial/install_base.sh');
//        echo $this->sshRunCommand('chmod +x installers/ubuntu-20.04/install-partial/install_web.sh');

        // echo $this->sshRunCommand('./installers/ubuntu-22.04/install-partial/install_base.sh');
         echo $this->sshRunCommand('omega-php -v');


//        echo $this->sshRunCommand('mkdir -p /usr/local/omega/web/');
//        echo $this->sshRunCommand('cp -r web /usr/local/omega/web/');
//        echo $this->sshRunCommand('cd /usr/local/omega/web/');
//        echo $this->sshRunCommand('ls -la');
//
//        echo $this->sshRunCommand('wget https://getcomposer.org/download/latest-stable/composer.phar');
//        echo $this->sshRunCommand('COMPOSER_ALLOW_SUPERUSER=1 omega-php composer.phar install');
//
//        echo $this->sshRunCommand('/omega-panel/install_web.sh');
//


    }

}
