<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\TelemetryController;
use App\Service\ChartDataStorage;
use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TelemetryControllerTest extends WebTestCase
{
    private TelemetryController $controller;
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
        $this->controller = new TelemetryController($chartDataStorage);
    }
    public function testTelemetryRoute(): void
    {
        $client     = static::createClient();
        $client->request('GET', '/telemetry');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testInvalidHeaderPost(): void
    {
        $client = static::createClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'text/html']);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString($content, '{"error":"Bad request"}');
    }

    public function testInvalidJsonPost(): void
    {
        $client = static::createClient();
        $client->request('POST', '/telemetry', [], [], ['CONTENT_TYPE' => 'application/json'], '{"test": "test"}');
        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        self::assertJsonStringEqualsJsonString($content, '{"error":"Bad request"}');
    }

    public function testGetPieChartData(): void
    {
        $result = $this->controller->getPieChartData(ChartSerie::GlpiVersion, ChartPeriodFilter::Always);

        self::assertIsArray($result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('series', $result);
    }

    public function testGetBarChartData(): void
    {
        $result = $this->controller->getBarChartData(ChartSerie::GlpiVersion, ChartPeriodFilter::Always);

        $expected = [
            'title' => [
                'text' => 'Number of unique GLPI instances reported by version',
            ],
            'xAxis' => [
                'data' => ['2024-01', '2024-02']
            ],
            'series' => [
                [
                    'name' => '10.0.6',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => [
                        'show' => false
                    ],
                    'emphasis' => [
                        'focus' => 'series'
                    ],
                    'data' => [40, 0]
                ],
                [
                    'name' => '10.0.7',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => [
                        'show' => false
                    ],
                    'emphasis' => [
                        'focus' => 'series'
                    ],
                    'data' => [20, 0]
                ],
                [
                    'name' => '10.0.8',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => [
                        'show' => false
                    ],
                    'emphasis' => [
                        'focus' => 'series'
                    ],
                    'data' => [40, 50]
                ],
                [
                    'name' => '10.0.9',
                    'type' => 'bar',
                    'stack' => 'total',
                    'label' => [
                        'show' => false
                    ],
                    'emphasis' => [
                        'focus' => 'series'
                    ],
                    'data' => [0, 50]
                ],
            ]
        ];

        self::assertIsArray($result);
        self::assertEquals($expected, $result);
    }
}
