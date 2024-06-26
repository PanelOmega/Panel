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

    protected $codecovToken;

    protected $os;

    protected $hostname;
    protected $username;

    public function __construct($params)
    {
        $this->serverIp = $params['serverIp'];
        $this->privateSSHKeyFile = $params['privateSSHKeyFile'];
        $this->gitBranch = $params['gitBranch'];
        $this->gitRepoUrl = $params['gitRepoUrl'];
        $this->gitCommit = $params['gitCommit'];
        $this->codecovToken = $params['codecovToken'];
        $this->os = $params['os'];

        $this->sshConnection = new SSH2($this->serverIp);
        $sshKey = PublicKeyLoader::load(file_get_contents($this->privateSSHKeyFile));

        if (!$this->sshConnection->login('root', $sshKey)) {
            throw new Exception('Login failed');
        }

        $this->hostname = trim($this->sshExec('hostname'));
        $this->username = trim($this->sshExec('whoami'));

    }
    public function sshExec($command, $callback = false, $timeout = null)
    {
        if ($timeout) {
            $this->sshTimeout($timeout);
        }
        if ($callback === false) {
            $callback = null;
        }
        if ($callback === true) {
            $callback = function ($data) {
                echo "\033[0;32m " . $data . " \033[0m";
            };
        }
        return $this->sshConnection->exec($command, $callback);
    }

    public function sshRunCommand($command)
    {
        $this->sshWrite($command);
        return $this->sshRead();
    }

    public function sshKeepAlive()
    {
        $this->sshConnection->setKeepAlive(400);
    }

    public function sshTimeout($timeout): void
    {
        $this->sshConnection->setTimeout($timeout);
    }

    private function sshWrite($command)
    {
        $this->sshConnection->write($command . PHP_EOL);
    }

    private function sshRead()
    {
        return $this->sshConnection->read($this->username . '@'.$this->hostname.':~$');
    }

}
