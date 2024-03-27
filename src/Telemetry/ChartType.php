<?php

declare(strict_types=1);

namespace App\Telemetry;

enum ChartType: string
{
    case Bar = 'bar';
    case Pie = 'pie';
    case NightingaleRose = 'nightingale-rose';

}
