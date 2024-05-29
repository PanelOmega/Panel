<?php
require_once __DIR__.'/BaseTest.php';

class CommitTest extends BaseTest
{
    public function runTest()
    {
        echo $this->sshRunCommand('rm -rf Panel');
        echo $this->sshRunCommand('rm -rf /omega-panel');
        echo $this->sshRunCommand('git clone https://github.com/PanelOmega/Panel.git');

        echo $this->sshRunCommand('cd Panel');
        echo $this->sshRunCommand('ls -la');
        echo $this->sshRunCommand('git checkout '.$this->gitBranch);

        echo $this->sshRunCommand('mkdir /omega-panel');

        echo $this->sshRunCommand('cp installers/ubuntu-22.04/install-partial/install_base.sh /omega-panel/install_base.sh');
        echo $this->sshRunCommand('chmod +x /omega-panel/install_base.sh');

        echo $this->sshRunCommand('cp installers/ubuntu-20.04/install-partial/install_web.sh /omega-panel/install_web.sh');
        echo $this->sshRunCommand('chmod +x /omega-panel/install_web.sh');

        echo $this->sshRunCommand('/omega-panel/install_base.sh');


    }

}
