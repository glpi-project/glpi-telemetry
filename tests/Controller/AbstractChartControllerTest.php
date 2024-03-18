<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\AbstractChartController;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class AnyChartController extends AbstractChartController {}

class AbstractChartControllerTest extends TestCase
{
    private AnyChartController $controller;

    protected function setUp(): void
    {
        $this->controller = new AnyChartController();
    }

    public function testGetPeriodFromFilter(): void
    {
        $result = $this->controller->getPeriodFromFilter('lastYear');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('start', $result);
        $this->assertInstanceOf(DateTimeInterface::class, $result['start']);
        $this->assertArrayHasKey('end', $result);
        $this->assertInstanceOf(DateTimeInterface::class, $result['end']);
    }

    public function testPrepareDataForPieChart(): void
    {
        $data = [
            "2024-01" => [
                ['name' => 'Chrome', 'total' => 10],
                ['name' => 'Firefox', 'total' => 5],
                ['name' => 'Safari', 'total' => 10],
            ],
            "2024-02" => [
                ['name' => 'Chrome', 'total' => 15],
            ],
        ];

        $result = $this->controller->prepareDataForPieChart($data);

        $this->assertEquals(
            $result,
            [
                ['name' => 'Chrome', 'value' => 25],
                ['name' => 'Firefox', 'value' => 5],
                ['name' => 'Safari', 'value' => 10],
            ]
        );
    }
    public function testPrepareDataForBarChart(): void
    {
        $data = [
            '2022-01' => [
                ['name' => 'Chrome', 'total' => 10],
                ['name' => 'Firefox', 'total' => 5],
                ['name' => 'Safari', 'total' => 10],
            ],
            '2022-02' => [
                ['name' => 'Chrome', 'total' => 15],
                ['name' => 'Safari', 'total' => 10],
                ['name' => 'Firefox', 'total' => 5],
            ],
        ];

        $result = $this->controller->prepareDataForBarChart($data);

        $expected = [
            'xAxis' => [
                'data' => ['2022-01', '2022-02']
            ],
            'series' => [
                [
                    'name' => 'Chrome',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => ['show' => false],
                    'emphasis' => ['focus' => 'series'],
                    'data' => [40, 50]
                ],
                [
                    'name' => 'Firefox',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => ['show' => false],
                    'emphasis' => ['focus' => 'series'],
                    'data' => [20, 16.67]
                ],
                [
                    'name' => 'Safari',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => ['show' => false],
                    'emphasis' => ['focus' => 'series'],
                    'data' => [40, 33.33]
                ],
            ]
        ];

        $this->assertEquals($result, $expected);
    }
}
