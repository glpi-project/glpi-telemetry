<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['node_modules', 'var', 'vendor'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2.0' => true,
    ])
    ->setFinder($finder)
;
