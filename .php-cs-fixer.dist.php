<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/lib',
        __DIR__.'/tests'
    ])->append([__FILE__]);

$config = new PhpCsFixer\Config('FinsightConfig');

return $config
    ->setRules(
        [
            '@PSR12' => true
        ]
    )
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setUsingCache(false);
