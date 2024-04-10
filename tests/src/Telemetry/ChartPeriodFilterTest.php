<?php

declare(strict_types=1);

namespace App\Tests\Telemetry;

use App\Telemetry\ChartPeriodFilter;
use App\Tests\KernelTestCase;
use DateTimeImmutable;

class ChartPeriodFilterTest extends KernelTestCase
{
    /**
     * @return array<array{filter: ChartPeriodFilter}>
     */
    public static function caseProvider(): iterable
    {
        foreach (ChartPeriodFilter::cases() as $case) {
            yield ['filter' => $case];
        }
    }

    /**
     * @dataProvider caseProvider
     */
    public function testGetStartDate(ChartPeriodFilter $filter): void
    {
        self::assertInstanceOf(DateTimeImmutable::class, $filter->getStartDate());
    }

    /**
     * @dataProvider caseProvider
     */
    public function testGetEndDate(ChartPeriodFilter $filter): void
    {
        self::assertInstanceOf(DateTimeImmutable::class, $filter->getEndDate());
    }

}
