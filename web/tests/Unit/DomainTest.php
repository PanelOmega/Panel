<?php

namespace Tests\Unit;

use App\Models\Domain;
use Tests\TestCase;

class DomainTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testDomainCreation(): void
    {
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
