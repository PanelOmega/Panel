<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

include __DIR__ . '/rector/ConvertProtectedMethodsNameToCamelCaseRector.php';

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app/Models/Fail2BanWhitelistedIp.php',
//        __DIR__ . '/bootstrap',
//        __DIR__ . '/config',
//        __DIR__ . '/public',
//        __DIR__ . '/resources',
//        __DIR__ . '/routes',
//        __DIR__ . '/test-docker',
//        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version


        ->withRules([
        ConvertProtectedMethodsNameToCamelCaseRector::class,
        \Epifrin\RectorCustomRules\RectorRules\ConvertPrivateMethodsNameToCamelCaseRector::class,
        \Epifrin\RectorCustomRules\RectorRules\ConvertLocalVariablesNameToCamelCaseRector::class,
        \Epifrin\RectorCustomRules\RectorRules\ReplaceDoubleQuotesWithSingleRector::class,

    ]);

