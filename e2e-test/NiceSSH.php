<?php

class NiceSSH
{

    // SSH Host

    public $ssh_host = 'myserver.example.com';

    // SSH Port

    public $ssh_port = 22;

    // SSH Server Fingerprint

    public $ssh_server_fp = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    // SSH Username

    public $ssh_auth_user = 'username';

    // SSH Public Key File

    public $ssh_auth_pub = '/home/username/.ssh/id_rsa.pub';

    // SSH Private Key File

    public $ssh_auth_priv = '/home/username/.ssh/id_rsa';

    // SSH Private Key Passphrase (null == no passphrase)

    public $ssh_auth_pass;

    // SSH Connection

    private $connection;


    public function connect()
    {

        if (!($this->connection = ssh2_connect($this->ssh_host, $this->ssh_port))) {

            throw new Exception('Cannot connect to server');

        }

        $fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);

        if (strcmp($this->ssh_server_fp, $fingerprint) !== 0) {

            throw new Exception('Unable to verify server identity!');

        }

        if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass)) {

            throw new Exception('Autentication rejected by server');

        }

    }

    public function exec($cmd)
    {

        if (!($stream = ssh2_exec($this->connection, $cmd))) {

            throw new Exception('SSH command failed');

        }

        stream_set_blocking($stream, true);

        $data = "";

        while ($buf = fread($stream, 4096)) {

            $data .= $buf;

        }

        fclose($stream);

        return $data;

    }

    public function disconnect()
    {

        $this->exec('echo "EXITING" && exit;');

        $this->connection = null;

    }

    public function __destruct()
    {

        $this->disconnect();

    }

}

?>
