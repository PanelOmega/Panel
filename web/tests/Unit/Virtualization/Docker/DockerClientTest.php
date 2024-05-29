<?php

namespace Tests\Unit\Virtualization\Docker;

use App\Virtualization\Docker\DockerClient;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;

class DockerClientTest extends TestCase
{
    use HasDocker;
    /**
     * A basic unit test example.
     */
    public function testDockerCreation(): void
    {
  
        $this->installDocker();

        $dockerClient = new DockerClient();
        $createContainer = $dockerClient->createContainer([
            'Image' => 'nginx',
        ]);
        $this->assertEquals(201, $createContainer['code']);
        $this->assertEquals('success', $createContainer['status']);
        $this->assertArrayHasKey('Id', $createContainer['response']);

        $startContainer = $dockerClient->startContainer($createContainer['response']['Id'], []);
        $this->assertEquals(204, $startContainer['code']);
        $this->assertEquals('success', $startContainer['status']);

        $listContainers = $dockerClient->listContainers();
        $this->assertIsArray($listContainers['response']);

        foreach ($listContainers['response'] as $container) {

            $restartContainer = $dockerClient->restartContainer($container['Id']);
            $this->assertEquals(204, $restartContainer['code']);
            $this->assertEquals('success', $restartContainer['status']);

            $stopContainer = $dockerClient->stopContainer($container['Id']);
            $this->assertEquals(204, $stopContainer['code']);
            $this->assertEquals('success', $stopContainer['status']);

            $deleteContainer = $dockerClient->deleteContainer($container['Id']);
            $this->assertEquals(204, $deleteContainer['code']);
            $this->assertEquals('success', $deleteContainer['status']);

        }

    }
}
