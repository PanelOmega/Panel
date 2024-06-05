<?php

namespace tests\Unit\Models;

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

        $randomDomain = 'test' . rand(1, 1000) . '.com';

        $domainDetails = [
            'domain' => $randomDomain,
        ];

        $domain = new Domain();
        $domain->fill($domainDetails);
        $domain->save();

        $this->assertDatabaseHas('domains', $domainDetails);

    }
}
