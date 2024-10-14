<?php

namespace App\Models\Traits;

trait ZoneEditorDnssecTrait
{

    public static function getKeyTypeOptions(): array
    {
        $keyTypes = [];
        $types = [
            'ksk' => 'KSK (Key Signing Key)',
            'zsk' => 'ZSK (Zone Signing Key)'
        ];

        foreach ($types as $type => $label) {
            $keyTypes[$type] = $label;
        }

        return $keyTypes;
    }
}
