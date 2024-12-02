<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])->withRules([
        \Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector::class
    ]);
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    //->withTypeCoverageLevel(0);
