<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

include __DIR__ . '/ConvertProtectedMethodsNameToCamelCaseRector.php';
include __DIR__ . '/ConvertPrivateMethodsNameToCamelCaseRector.php';
include __DIR__ . '/ConvertPublicMethodsNameToCamelCaseRector.php';
include __DIR__ . '/ConvertLocalVariablesNameToCamelCaseRector.php';

$dirRoot = dirname(__DIR__);


$paths = [
    $dirRoot . '/app',
    $dirRoot . '/bootstrap',
    $dirRoot . '/config',
    $dirRoot . '/public',
    $dirRoot . '/resources',
    $dirRoot . '/routes',
    $dirRoot . '/tests',
];
if (isset($_SERVER['argv']) && is_array($_SERVER['argv'])) {
    foreach ($_SERVER['argv'] as $argKey=>$argValue) {
        if (strpos($argValue, '--file=') !== false) {
            unset($_SERVER['argv'][$argKey]);
            if (strpos($argValue, '/rector/') !== false) {
                $paths = [];
                continue;
            }
            $paths = [str_replace('--file=', '', $argValue)];
        }
    }
}

if (empty($paths)) {
    return;
}


return RectorConfig::configure()
    ->withPaths($paths)
    // uncomment to reach your current PHP version

    ->withSets([
        \RectorLaravel\Set\LaravelSetList::LARAVEL_CODE_QUALITY
    ])
        ->withRules([
        ConvertProtectedMethodsNameToCamelCaseRector::class,
        ConvertPrivateMethodsNameToCamelCaseRector::class,
        ConvertPublicMethodsNameToCamelCaseRector::class,
        ConvertLocalVariablesNameToCamelCaseRector::class,

    ]);

