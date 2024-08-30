<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

include __DIR__ . '/rector/ConvertProtectedMethodsNameToCamelCaseRector.php';
include __DIR__ . '/rector/ConvertPrivateMethodsNameToCamelCaseRector.php';
include __DIR__ . '/rector/ConvertLocalVariablesNameToCamelCaseRector.php';

return RectorConfig::configure()
    ->withPaths([
//        __DIR__ . '/app',
        __DIR__ . '/app/Models/Fail2BanWhitelistedIp.php',
//        __DIR__ . '/bootstrap',
//        __DIR__ . '/config',
//        __DIR__ . '/public',
//        __DIR__ . '/resources',
//        __DIR__ . '/routes',
//        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version


    ->withSets([
        \RectorLaravel\Set\LaravelSetList::LARAVEL_CODE_QUALITY,
        \RectorLaravel\Set\LaravelSetList::LARAVEL_110,
    ])
        ->withRules([
        ConvertProtectedMethodsNameToCamelCaseRector::class,
        ConvertPrivateMethodsNameToCamelCaseRector::class,
        ConvertLocalVariablesNameToCamelCaseRector::class,

    ]);

