<?php

declare(strict_types=1);

namespace App\Tests\Telemetry;

use App\Telemetry\ChartPeriodFilter;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ChartPeriodFilterTest extends TestCase
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
        $this->assertInstanceOf(DateTimeImmutable::class, $filter->getStartDate());
    }

    /**
     * @dataProvider caseProvider
     */
    public function testGetEndDate(ChartPeriodFilter $filter): void
    {
        $this->assertInstanceOf(DateTimeImmutable::class, $filter->getEndDate());
    }

}
