<?php

namespace App\Models\Traits;

trait ZoneEditorDnssecTrait
{

    public static function generateKeys(string $domain, string $algorithm, array $types) {
        $path = "/home/";
        $commands = [];
        foreach ($types as $type => $length) {
            $commands[] = "dnssec-keygen -a $algorithm -b $length -f $type $domain";
        }

        foreach($commands as $command) {
            shell_exec($command);
        }


    }

    public static function getKeyTypeOptions(): array
    {
        $keyTypes = [];
        $types = [
            'KSK' => 'KSK (Key Signing Key)',
            'ZSK' => 'ZSK (Zone Signing Key)'
        ];

        foreach ($types as $type => $label) {
            $keyTypes[$type] = $label;
        }

        return $keyTypes;
    }

    public static function getCustomizeSetup() {
        $setUp = [];

        $setOptions = [
            'classic' => 'Classic',
            'simple' => 'Simple'
        ];

        foreach ($setOptions as $option => $label) {
            $setUp[$option] = $label;
        }

        return $setUp;
    }

    public static function getCustomizeAlgorithm() {
        $algorithm = [];
        $algorithmTypes = [
            'RSASHA256' => 'RSA/SHA-256 (Algorithm 8)',
            'RSASHA512' => 'RSA/SHA-512 (Algorithm 10)',
            'ECDSAP256SHA256' => 'ECDSA Curve P-256 with SHA-256 (Algorithm 13)',
            'ECDSAP384SHA384' => 'ECDSA Curve P-384 with SHA-384 (Algorithm 14)'
        ];

        foreach($algorithmTypes as $type => $label) {
            $algorithm[$type] = $label;
        }

        return $algorithm;
    }

    public static function getCustomizeStatuses() {
        $statuses = [];
        $customStatus = [
            'active' => 'Active',
            'notActive' => 'Not Active',
        ];

        foreach($customStatus as $status => $label) {
            $statuses[$status] = $label;
        }

        return $statuses;
    }
}
