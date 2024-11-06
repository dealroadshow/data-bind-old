<?php

declare(strict_types=1);

use PhpCsFixer\Config;

/** @var Config $mainConfig */
$mainConfig = require __DIR__ . '/vendor/dealroadshow/phpcs-finsight-ruleset/Finsight/.php-cs-fixer.dist.php';

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/lib',
        __DIR__ . '/tests',
    ])->append([__FILE__]);

return (new Config())
    ->setRules(
        array_merge($mainConfig->getRules(), [])
    )
    ->setRiskyAllowed(
        $mainConfig->getRiskyAllowed()
    )
    ->setFinder($finder)
    ->setUsingCache(false);
