<?php

namespace Tests\Unit\Virtualization;

use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;

class DockerApiTest extends TestCase
{
    use HasDocker;
    /**
     * A basic unit test example.
     */
    public function testDockerCreation(): void
    {
        $this->installDocker();



        dd(33);

    }
}
