<?php

declare(strict_types=1);

namespace App\Telemetry;

use DateTimeImmutable;

enum ChartPeriodFilter: string
{
    case LastTwelveMonths   = 'last-12-months';
    case LastFiveYears      = 'last-5-years';
    case Always             = 'always';

    public function getStartDate(): DateTimeImmutable
    {
        return match($this) {
            self::LastTwelveMonths  => new DateTimeImmutable('-1 year'),
            self::LastFiveYears     => new DateTimeImmutable('-5 years'),
            self::Always            => new DateTimeImmutable('2017-01-01'), // Telemetry data collection started in 2017
        };
    }

    public function getEndDate(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
