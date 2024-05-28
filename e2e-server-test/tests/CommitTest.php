<?php
require_once __DIR__.'/BaseTest.php';

class CommitTest extends BaseTest
{
    public function runTest()
    {
//        $command = "";
//        $command .= "rm -rf Panel \n";
//        $command .= "rm -rf /omega-panel \n";
//
//        $command .= "git clone https://github.com/PanelOmega/Panel.git \n";
//        $command .= "cd Panel \n";
//        $command .= "ls -la \n";
//        $command .= "git checkout ".$this->gitBranch." \n";
//
//        $command .= "mkdir /omega-panel \n";
//
//        $command .= "cp installers/ubuntu-22.04/install-partial/install_base.sh /omega-panel/install_base.sh \n";
//        $command .= "chmod +x /omega-panel/install_base.sh \n";
//
//        $command .= "cp installers/ubuntu-20.04/install-partial/install_web.sh /omega-panel/install_web.sh \n";
//        $command .= "chmod +x /omega-panel/install_web.sh \n";
//
//        $command .= "/omega-panel/install_base.sh \n";
//
//        dd($this->sshExec($command));

//        $this->sshWrite('git clone https://github.com/PanelOmega/Panel.git');
//        $this->sshWrite('cd Panel');
//        $this->sshWrite('ls -la');
//        //echo $this->sshExec('git checkout '.$this->gitBranch);
//
        $this->sshWrite('ls -la');

        dd($this->sshRead());


        dd(3);

    }

}
