<?php

declare(strict_types=1);

use FriendsOfTwig\Twigcs;
use FriendsOfTwig\Twigcs\Ruleset\Official;

$finder = Twigcs\Finder\TemplateFinder::create()->in(__DIR__.'/templates');

return Twigcs\Config\Config::create()
    ->addFinder($finder)
    ->setRuleSet(Official::class)
;
