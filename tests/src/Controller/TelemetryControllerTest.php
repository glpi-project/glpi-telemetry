<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\TelemetryController;
use App\Service\ChartDataStorage;
use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use App\Tests\KernelTestCase;

class TelemetryControllerTest extends KernelTestCase
{
    /**
     * @return array<
     *      array{
     *          storedData: array<int, array{name: string, value: int}>,
     *          expectedValues: array{
     *              title: array{text: string},
     *              series: array<int, array{data: array<int, array{name: string, value: int, tooltip?: string}>}>
     *          }
     *      }
     *  >
     */
    public static function pieChartDataProvider(): iterable
    {
        // Data with tooltip
        yield [
            'storedData' => [
                ['name' => 'TARBALL', 'value' => 25000],
                ['name' => 'RPM',     'value' => 1000],
                ['name' => 'DOCKER',  'value' => 500],
                ['name' => 'CLOUD',   'value' => 35], // just above the 0.1% limit
                ['name' => 'GIT',     'value' => 12],
                ['name' => 'APT',     'value' => 10],
                ['name' => 'YUM',     'value' => 2],
            ],
            'expectedValues' => [
                'title' => [
                    'text' => 'Installation modes',
                ],
                'series' => [
                    [
                        'data' => [
                            ['name' => 'TARBALL', 'value' => 25000],
                            ['name' => 'RPM',     'value' => 1000],
                            ['name' => 'DOCKER',  'value' => 500],
                            ['name' => 'CLOUD',   'value' => 35],
                            [
                                'name' => 'Other',
                                'value' => 24,
                                // tooltip contains all values below 0.1%, ordered by value DESC
                                'tooltip' => <<<HTML
                                    <table class="table table-sm table-borderless">
                                        <tr><th colspan="3">Other</th></tr>
                                        <tr>
                                            <td class="text-nowrap">GIT</td>
                                            <td class="text-end text-nowrap">0.05%</td>
                                            <td class="text-end text-nowrap">(12)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">APT</td>
                                            <td class="text-end text-nowrap">0.04%</td>
                                            <td class="text-end text-nowrap">(10)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">YUM</td>
                                            <td class="text-end text-nowrap">0.01%</td>
                                            <td class="text-end text-nowrap">(2)</td>
                                        </tr>
                                    </table>
                                HTML
                            ],
                        ],
                    ],
                ],
            ]
        ];

        // Data without tooltip
        yield [
            'storedData' => [
                ['name' => 'TARBALL', 'value' => 125],
                ['name' => 'RPM',     'value' => 100],
                ['name' => 'CLOUD',   'value' => 35],
                ['name' => 'YUM',     'value' => 20],
                ['name' => 'GIT',     'value' => 15],
                ['name' => 'APT',     'value' => 10],
            ],
            'expectedValues' => [
                'title' => [
                    'text' => 'Installation modes',
                ],
                'series' => [
                    [
                        'data' => [
                            ['name' => 'TARBALL', 'value' => 125],
                            ['name' => 'RPM',     'value' => 100],
                            ['name' => 'CLOUD',   'value' => 35],
                            ['name' => 'YUM',     'value' => 20],
                            ['name' => 'GIT',     'value' => 15],
                            ['name' => 'APT',     'value' => 10],
                        ],
                    ],
                ],
            ]
        ];
    }

    /**
     * @dataProvider pieChartDataProvider
     *
     * @param array<string, array<int, array{name: string, value: int}>> $storedData
     * @param array{
     *      title: array{text: string},
     *      series: array<int, array{data: array<int, array{name: string, value: int, tooltip?: string}>}>
     *  } $expectedResult
     */
    public function testGetPieChartData(array $storedData, array $expectedResult): void
    {
        $chartDataStorage = $this->createMock(ChartDataStorage::class);
        $chartDataStorage->method('getPeriodTotalValues')->willReturn($storedData);

        $controller = new TelemetryController($chartDataStorage);
        $result = $controller->getPieChartData(ChartSerie::InstallMode, ChartPeriodFilter::Always);

        self::assertEquals($expectedResult, $result);
    }

    public function testGetMonthlyStackedBarChartData(): void
    {
        $chartDataStorage = $this->createMock(ChartDataStorage::class);
        $chartDataStorage->method('getMonthlyValues')
            ->willReturn(
                [
                    "2024-01" => [
                        ['name' => '10.0.6', 'value' => 10],
                        ['name' => '10.0.8', 'value' => 10],
                        ['name' => '10.0.7', 'value' => 5],
                    ],
                    "2024-02" => [
                        ['name' => '10.0.8', 'value' => 15],
                        ['name' => '10.0.9', 'value' => 15],
                    ],
                ]
            )
        ;

        $controller = new TelemetryController($chartDataStorage);
        $result = $controller->getMonthlyStackedBarChartData(ChartSerie::GlpiVersion, ChartPeriodFilter::Always);

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
        $chartDataStorage->method('getPeriodTotalValues')
            ->willReturn(
                [
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
                    ['name' => 'glpiinventory', 'value' => 31],
                    ['name' => 'datainjection', 'value' => 21],
                    ['name' => 'sccm',          'value' => 19],
                    ['name' => 'moreticket',    'value' => 13],
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
