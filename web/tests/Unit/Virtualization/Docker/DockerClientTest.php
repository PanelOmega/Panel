<?php

namespace Tests\Unit\Virtualization\Docker;

use App\Virtualization\Docker\DockerClient;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;

class DockerClientTest extends TestCase
{
    use HasDocker;

    public static $lastCreatedContainerId;

    /**
     * A basic unit test example.
     */
    public function testDockerImagePull(): void
    {
        $this->installDocker();

        $dockerClient = new DockerClient();
        $pullStatus = $dockerClient->pullImage('nginx:latest');
        $this->assertEquals(200, $pullStatus['code']);
        $this->assertEquals('success', $pullStatus['status']);

    }

    public function testDockerCreateContainer()
    {
        $containerName = 'test' . rand(1000, 9999);
        $dockerClient = new DockerClient();
        $createContainer = $dockerClient->createContainer($containerName, [
            'Image' => 'nginx:latest',
        ]);
        $this->assertEquals(201, $createContainer['code']);
        $this->assertEquals('success', $createContainer['status']);
        $this->assertArrayHasKey('Id', $createContainer['response']);

        self::$lastCreatedContainerId = $createContainer['response']['Id'];

    }

    public function testDockerStartContainer()
    {

        $dockerClient = new DockerClient();
        $startContainer = $dockerClient->startContainer(self::$lastCreatedContainerId, []);
        $this->assertEquals(204, $startContainer['code']);
        $this->assertEquals('success', $startContainer['status']);
    }

    public function testDockerListContainers()
    {
        $dockerClient = new DockerClient();
        $listContainers = $dockerClient->listContainers();
        $this->assertIsArray($listContainers['response']);
    }

    public function testDockerRestartContainer()
    {
        $dockerClient = new DockerClient();
        $restartContainer = $dockerClient->restartContainer(self::$lastCreatedContainerId);
        $this->assertEquals(204, $restartContainer['code']);
        $this->assertEquals('success', $restartContainer['status']);
    }

    public function testDockerStopContainer()
    {
        $dockerClient = new DockerClient();
        $stopContainer = $dockerClient->stopContainer(self::$lastCreatedContainerId);
        $this->assertEquals(204, $stopContainer['code']);
        $this->assertEquals('success', $stopContainer['status']);
    }

    public function testDockerGetContainer()
    {
        $dockerClient = new DockerClient();
        $getContainer = $dockerClient->getContainer(self::$lastCreatedContainerId);
        $this->assertEquals(200, $getContainer['code']);
        $this->assertEquals('success', $getContainer['status']);
    }

    public function testDockerDeleteContainer()
    {
        $dockerClient = new DockerClient();
        $deleteContainer = $dockerClient->deleteContainer(self::$lastCreatedContainerId);

        $this->assertEquals(204, $deleteContainer['code']);
        $this->assertEquals('success', $deleteContainer['status']);
    }

    public function testDockerImageRemove()
    {
        $dockerClient = new DockerClient();
        $removeImage = $dockerClient->removeImage('nginx:latest');
        $this->assertEquals(200, $removeImage['code']);
        $this->assertEquals('success', $removeImage['status']);
    }
}
