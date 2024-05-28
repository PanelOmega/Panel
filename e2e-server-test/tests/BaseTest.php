<?php
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\File\ANSI;

class BaseTest
{
    private $serverIp;
    private $privateSSHKeyFile;

    private $sshConnection;

    protected $gitBranch;
    protected $gitRepoUrl;
    protected $gitCommit;

    public function __construct($params)
    {
        $this->serverIp = $params['serverIp'];
        $this->privateSSHKeyFile = $params['privateSSHKeyFile'];
        $this->gitBranch = $params['gitBranch'];
        $this->gitRepoUrl = $params['gitRepoUrl'];
        $this->gitCommit = $params['gitCommit'];

        $this->sshConnection = new SSH2($this->serverIp);
        $sshKey = PublicKeyLoader::load(file_get_contents($this->privateSSHKeyFile));

        if (!$this->sshConnection->login('root', $sshKey)) {
            throw new Exception('Login failed');
        }
    }
    public function sshExec($command)
    {
        return $this->sshConnection->exec($command);
    }

    public function sshWrite($command)
    {
        $this->sshConnection->write($command . PHP_EOL);
    }

    public function sshRead()
    {
        return $this->sshConnection->read();
    }

}
