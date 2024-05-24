<?php
namespace App\Virtualization\Docker;

class DockerClient
{
    private $socketPath;
    private $socket;

    public function __construct($socketPath = '/var/run/docker.sock')
    {
        $this->socketPath = $socketPath;
        $this->socket = curl_init();
    }

    private function request($method, $endpoint, $data = null)
    {
        $url = "http://localhost$endpoint";

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_UNIX_SOCKET_PATH => $this->socketPath,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ];

        $options[CURLOPT_POSTFIELDS] = json_encode($data);

        curl_setopt_array($this->socket, $options);

        $response = curl_exec($this->socket);
        $httpCode = curl_getinfo($this->socket, CURLINFO_HTTP_CODE);

        $status = 'unknown';
        if ($httpCode >= 200 && $httpCode < 300) {
            $status = 'success';
        } elseif ($httpCode >= 400 && $httpCode < 500) {
            $status = 'client_error';
        } elseif ($httpCode >= 500) {
            $status = 'server_error';
        }

        return [
            'status'=>$status,
            'response' => json_decode($response, true),
            'code' => $httpCode,
        ];
    }

    public function listContainers()
    {
        return $this->request('GET', '/containers/json');
    }

    public function createContainer($containerConfig)
    {
        return $this->request('POST', '/containers/create', $containerConfig);
    }

    public function startContainer($containerId, $containerConfig = null)
    {
       return $this->request('POST', "/containers/$containerId/start", $containerConfig);
    }

    public function stopContainer($containerId)
    {
        return $this->request('POST', "/containers/$containerId/stop");
    }

    public function deleteContainer($containerId)
    {
        return $this->request('DELETE', "/containers/$containerId");
    }

    public function __destruct()
    {
        curl_close($this->socket);
    }
}
