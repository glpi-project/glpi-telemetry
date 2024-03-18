<?php

declare(strict_types=1);

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    /** @var array{APP_ENV: string, APP_DEBUG: mixed} $context */
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
