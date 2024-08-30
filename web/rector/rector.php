<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

include __DIR__ . '/ConvertProtectedMethodsNameToCamelCaseRector.php';
include __DIR__ . '/ConvertPrivateMethodsNameToCamelCaseRector.php';
include __DIR__ . '/ConvertLocalVariablesNameToCamelCaseRector.php';

$dirRoot = dirname(__DIR__);

return RectorConfig::configure()
    ->withPaths([
        $dirRoot . '/app/UniversalDatabaseExecutor.php',
//        $dirRoot . '/app/Models',
//        $dirRoot . '/bootstrap',
//        $dirRoot . '/config',
//        $dirRoot . '/public',
//        $dirRoot . '/resources',
//        $dirRoot . '/routes',
//        $dirRoot . '/tests',
    ])
    // uncomment to reach your current PHP version


    ->withSets([
        \RectorLaravel\Set\LaravelSetList::LARAVEL_CODE_QUALITY
    ])
        ->withRules([
        ConvertProtectedMethodsNameToCamelCaseRector::class,
        ConvertPrivateMethodsNameToCamelCaseRector::class,
        ConvertLocalVariablesNameToCamelCaseRector::class,

    ]);

