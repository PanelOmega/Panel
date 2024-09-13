<?php

namespace tests\Unit\Models\Traits;

use App\Models\Traits\IndexTrait;
use Illuminate\Foundation\Testing\TestCase;

class IndexTraitTest extends TestCase
{
    use IndexTrait;
    public function testGetIndexesIndexTypes() {
        $testExpected = [
            'Inherit' => 'Inherit',
            'No Indexing' => 'No Indexing',
            'Filename Only' => 'Show Filename Only',
            'Filename And Description' => 'Show Filename And Description',
        ];

        $testResult = self::getIndexesIndexTypes();
        $this->assertEquals($testExpected, $testResult);
    }
}
