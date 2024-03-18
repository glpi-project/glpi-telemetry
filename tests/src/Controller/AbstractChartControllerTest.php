<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\AbstractChartController;
use App\Service\ChartDataStorage;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use App\Telemetry\ChartSerie;
use App\Telemetry\ChartPeriodFilter;

class AbstractChartControllerTest extends TestCase
{
    private AbstractChartController $controller;

    protected function setUp(): void
    {
        $chartDataStorage = $this->createMock(ChartDataStorage::class);
        $chartDataStorage->method('getMonthlyValues')
            ->willReturn(
                [
                    "2024-01" => [
                        ['name' => '10.0.6', 'total' => 10],
                        ['name' => '10.0.7', 'total' => 5],
                        ['name' => '10.0.8', 'total' => 10],
                    ],
                    "2024-02" => [
                        ['name' => '10.0.8', 'total' => 15],
                        ['name' => '10.0.9', 'total' => 15],
                    ],
                ]
            )
        ;
        $this->controller = new class ($chartDataStorage) extends AbstractChartController {};
    }

    public function testGetPieChartData(): void
    {
        $result = $this->controller->getPieChartData(ChartSerie::GlpiVersion, ChartPeriodFilter::Always);

        self::assertEquals(
            $result,
            [
                ['name' => '10.0.6', 'value' => 10],
                ['name' => '10.0.7', 'value' => 5],
                ['name' => '10.0.8', 'value' => 25],
                ['name' => '10.0.9', 'value' => 15],
            ]
        );
    }

    public function testGetBarChartData(): void
    {
        $result = $this->controller->getBarChartData(ChartSerie::GlpiVersion, ChartPeriodFilter::Always);

        $expected = [
            'xAxis' => [
                'data' => ['2024-01', '2024-02']
            ],
            'series' => [
                [
                    'name' => '10.0.6',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => ['show' => false],
                    'emphasis' => ['focus' => 'series'],
                    'data' => [40, 0]
                ],
                [
                    'name' => '10.0.7',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => ['show' => false],
                    'emphasis' => ['focus' => 'series'],
                    'data' => [20, 0]
                ],
                [
                    'name' => '10.0.8',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => ['show' => false],
                    'emphasis' => ['focus' => 'series'],
                    'data' => [40, 50]
                ],
                [
                    'name' => '10.0.9',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => ['show' => false],
                    'emphasis' => ['focus' => 'series'],
                    'data' => [0, 50]
                ],
            ]
        ];

        self::assertEquals($result, $expected);
    }
}
