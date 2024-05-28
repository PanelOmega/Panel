<?php
require_once __DIR__.'/BaseTest.php';

class CommitTest extends BaseTest
{
    public function runTest()
    {
        echo $this->exec('rm -rf Panel');
        echo $this->exec('git clone https://github.com/PanelOmega/Panel.git');
        echo $this->exec('cd Panel');
    }

}
