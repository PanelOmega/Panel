<?php
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

class BaseTest
{
    private $serverIp;
    private $privateSSHKeyFile;

    private $sshConnection;

    public function __construct($params)
    {
        $this->serverIp = $params['serverIp'];
        $this->privateSSHKeyFile = $params['privateSSHKeyFile'];

        $this->sshConnection = new SSH2($this->serverIp);
        $sshKey = PublicKeyLoader::load(file_get_contents($this->privateSSHKeyFile));

        if (!$this->sshConnection->login('root', $sshKey)) {
            throw new Exception('Login failed');
        }
    }

    public function exec($command)
    {
        return $this->sshConnection->exec($command);
    }

}
