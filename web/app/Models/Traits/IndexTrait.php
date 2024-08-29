<?php

namespace App\Models\Traits;

trait IndexTrait
{
    public static function getIndexesIndexTypes()
    {
        $types = [];
        $indexTypes = [
            'Inherit' => 'Inherit',
            'No Indexing' => 'No Indexing',
            'Filename Only' => 'Show Filename Only',
            'Filename And Description' => 'Show Filename And Description',
        ];

        foreach ($indexTypes as $name => $type) {
            $types[$name] = $type;
        }

        return $types;
    }
}
