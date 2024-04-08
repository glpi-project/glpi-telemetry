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
        $chartDataStorage = $this->createMock(ChartDataStorage::class);
        $chartDataStorage->method('getMonthlyValues')
            ->willReturn(
                [
                    "2024-01" => [
                        ['name' => 'TARBALL', 'total' => 10000],
                        ['name' => 'DOCKER',  'total' => 5000],
                    ],
                    "2024-02" => [
                        ['name' => 'TARBALL', 'total' => 15000],
                        ['name' => 'RPM',     'total' => 1000],
                        ['name' => 'GIT',     'total' => 2],
                    ],
                ]
            )
        ;
        $controller = new TelemetryController($chartDataStorage);
        $result = $controller->getPieChartData(ChartSerie::InstallMode, ChartPeriodFilter::Always);

        self::assertEquals(
            $result,
            [
                'title' => [
                    'text' => 'Installation modes',
                ],
                'series' => [
                    [
                        'data' => [
                            ['name' => 'TARBALL', 'value' => 25000],
                            ['name' => 'DOCKER',  'value' => 5000],
                            ['name' => 'RPM',     'value' => 1000],
                            ['name' => 'Other', 'value' => 2, 'tooltip' =>
                            <<<HTML
                            <table class='table table-sm table-borderless'><tr><th colspan='3'>Other</th></tr>
                                <tr>
                                    <td class="text-nowrap">GIT</td>
                                    <td class="text-end text-nowrap">0.01%</td>
                                    <td class="text-end text-nowrap">(2)</td>
                                </tr></table>
                            HTML,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function testGetBarChartData(): void
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

        $controller = new TelemetryController($chartDataStorage);
        $result = $controller->getBarChartData(ChartSerie::GlpiVersion, ChartPeriodFilter::Always);

        $expected = [
            'title' => [
                'text' => 'GLPI versions',
            ],
            'xAxis' => [
                'data' => ['2024-01', '2024-02'],
            ],
            'series' => [
                [
                    'name' => '10.0.6',
                    'data' => [10, 0]
                ],
                [
                    'name' => '10.0.7',
                    'data' => [5, 0]
                ],
                [
                    'name' => '10.0.8',
                    'data' => [10, 15]
                ],
                [
                    'name' => '10.0.9',
                    'data' => [0, 15]
                ],
            ]
        ];

        self::assertIsArray($result);
        self::assertEquals($expected, $result);
    }

    public function testGetNightingaleRoseChartData(): void
    {
        $chartDataStorage = $this->createMock(ChartDataStorage::class);
        $chartDataStorage->method('getMonthlyValues')
            ->willReturn(
                [
                    "2024-01" => [
                        ['name' => 'formcreator',   'total' => 132],
                        ['name' => 'datainjection', 'total' => 21],
                        ['name' => 'costs',         'total' => 97],
                        ['name' => 'moreticket',    'total' => 13],
                        ['name' => 'genericobject', 'total' => 89],
                        ['name' => 'fields',        'total' => 52],
                        ['name' => 'glpiinventory', 'total' => 31],
                        ['name' => 'scim',          'total' => 42],
                        ['name' => 'sccm',          'total' => 19],
                        ['name' => 'oauthimap',     'total' => 87],
                        ['name' => 'reports',       'total' => 56],
                        ['name' => 'pdf',           'total' => 72],
                    ],
                    "2024-02" => [
                        ['name' => 'formcreator',   'total' => 124],
                        ['name' => 'datainjection', 'total' => 42],
                        ['name' => 'costs',         'total' => 78],
                        ['name' => 'genericobject', 'total' => 16],
                        ['name' => 'fields',        'total' => 15],
                        ['name' => 'scim',          'total' => 75],
                        ['name' => 'sccm',          'total' => 12],
                        ['name' => 'oauthimap',     'total' => 64],
                        ['name' => 'reports',       'total' => 24],
                        ['name' => 'pdf',           'total' => 98],
                        ['name' => 'cmdb',          'total' => 154],
                        ['name' => 'jamf',          'total' => 64],
                    ],
                ]
            )
        ;
        $controller = new TelemetryController($chartDataStorage);
        $result = $controller->getNightingaleRoseChartData(ChartSerie::TopPlugin, ChartPeriodFilter::Always);

        self::assertEquals(
            $result,
            [
                'title' => [
                    'text' => 'Top plugins',
                ],
                'series' => [
                    [
                        'data' => [
                            ['name' => 'formcreator',   'value' => 256],
                            ['name' => 'costs',         'value' => 175],
                            ['name' => 'pdf',           'value' => 170],
                            ['name' => 'cmdb',          'value' => 154],
                            ['name' => 'oauthimap',     'value' => 151],
                            ['name' => 'scim',          'value' => 117],
                            ['name' => 'genericobject', 'value' => 105],
                            ['name' => 'reports',       'value' => 80],
                            ['name' => 'fields',        'value' => 67],
                            ['name' => 'jamf',          'value' => 64],
                        ],
                    ],
                ],
            ]
        );
    }
}
