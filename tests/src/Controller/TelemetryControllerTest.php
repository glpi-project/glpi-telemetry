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
     *          expectedResult: array{
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
            'expectedResult' => [
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
                                HTML,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Data with more than 15 values in tooltip
        yield [
            'storedData' => [
                ['name' => 'TARBALL', 'value' => 25000],
                ['name' => 'RPM',     'value' => 1000],
                ['name' => 'DOCKER',  'value' => 500],
                ['name' => 'CLOUD',   'value' => 35], // just above the 0.1% limit
                ['name' => 'GIT',     'value' => 30],
                ['name' => 'APT',     'value' => 25],
                ['name' => 'YUM',     'value' => 22],
                ['name' => 'A',       'value' => 15],
                ['name' => 'B',       'value' => 14],
                ['name' => 'C',       'value' => 12],
                ['name' => 'D',       'value' => 11],
                ['name' => 'E',       'value' => 10],
                ['name' => 'F',       'value' => 8],
                ['name' => 'G',       'value' => 7],
                ['name' => 'H',       'value' => 6],
                ['name' => 'I',       'value' => 1],
                ['name' => 'J',       'value' => 1],
                ['name' => 'K',       'value' => 1],
                ['name' => 'L',       'value' => 1],
                ['name' => 'M',       'value' => 1],
                ['name' => 'N',       'value' => 1],
                ['name' => 'O',       'value' => 1],
                ['name' => 'P',       'value' => 1],
                ['name' => 'Q',       'value' => 1],
                ['name' => 'R',       'value' => 1],
                ['name' => 'S',       'value' => 1],
            ],
            'expectedResult' => [
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
                            ['name' => 'GIT',     'value' => 30],
                            [
                                'name' => 'Other',
                                'value' => 130,
                                // tooltip contains all values below 0.1%, ordered by value DESC
                                // but only 10 first values are detailled, other are grouped
                                'tooltip' => <<<HTML
                                    <table class="table table-sm table-borderless">
                                        <tr><th colspan="3">Other</th></tr>
                                        <tr>
                                            <td class="text-nowrap">APT</td>
                                            <td class="text-end text-nowrap">0.09%</td>
                                            <td class="text-end text-nowrap">(25)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">YUM</td>
                                            <td class="text-end text-nowrap">0.08%</td>
                                            <td class="text-end text-nowrap">(22)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">A</td>
                                            <td class="text-end text-nowrap">0.06%</td>
                                            <td class="text-end text-nowrap">(15)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">B</td>
                                            <td class="text-end text-nowrap">0.05%</td>
                                            <td class="text-end text-nowrap">(14)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">C</td>
                                            <td class="text-end text-nowrap">0.04%</td>
                                            <td class="text-end text-nowrap">(12)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">D</td>
                                            <td class="text-end text-nowrap">0.04%</td>
                                            <td class="text-end text-nowrap">(11)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">E</td>
                                            <td class="text-end text-nowrap">0.04%</td>
                                            <td class="text-end text-nowrap">(10)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">F</td>
                                            <td class="text-end text-nowrap">0.03%</td>
                                            <td class="text-end text-nowrap">(8)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">G</td>
                                            <td class="text-end text-nowrap">0.03%</td>
                                            <td class="text-end text-nowrap">(7)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">H</td>
                                            <td class="text-end text-nowrap">0.02%</td>
                                            <td class="text-end text-nowrap">(6)</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap">Other</td>
                                            <td class="text-end text-nowrap">0.04%</td>
                                            <td class="text-end text-nowrap">(11)</td>
                                        </tr>
                                    </table>
                                HTML,
                            ],
                        ],
                    ],
                ],
            ],
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
            'expectedResult' => [
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
            ],
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
                ],
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
                    'data' => [10, 0],
                ],
                [
                    'name' => '10.0.7',
                    'data' => [5, 0],
                ],
                [
                    'name' => '10.0.8',
                    'data' => [10, 15],
                ],
                [
                    'name' => '10.0.9',
                    'data' => [0, 15],
                ],
            ],
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
                ],
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
            ],
        );
    }
}
