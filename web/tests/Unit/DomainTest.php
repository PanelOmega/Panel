<?php

namespace Tests\Unit;

use App\Models\Domain;
use Tests\TestCase;
use Tests\Unit\Traits\HasDocker;

class DomainTest extends TestCase
{
    use HasDocker;

    /**
     * A basic unit test example.
     */
    public function testDomainCreation(): void
    {
        $this->installDocker();

        $domainDetails = [
            'domain' => 'example.com',
            'status' => 'active',
        ];

        $domain = new Domain();
        $domain->fill($domainDetails);
        $domain->save();

        $this->assertDatabaseHas('domains', $domainDetails);

    }
}
