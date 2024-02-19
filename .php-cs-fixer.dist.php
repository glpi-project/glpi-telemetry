<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['node_modules', 'var', 'vendor'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0'           => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
;
