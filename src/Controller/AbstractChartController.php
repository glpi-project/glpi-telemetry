<?php

declare(strict_types=1);

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractChartController extends AbstractController
{
    /**
     * Set a period based on the filter value
     *
     * @param string $filter
     * @return array{start: \DateTime, end: \DateTime}
     */
    public function getPeriodFromFilter(string $filter): array
    {
        $start = match($filter) {
            'lastYear' => new DateTime('-1 year'),
            'fiveYear' => new DateTime('-5 years'),
            'always'   => new DateTime('-10 years'),
            default    => throw new \Exception('Invalid filter value')
        };
        $end = new DateTime();

        return [
            'start' => $start,
            'end'   => $end,
        ];
    }

    /**
     * Process data to prepare it for the Echart pie chart
     *
     * @param array<string,array<int,array{name:string,total:int}>> $data
     * @return array<array{name:string,value:int}>
     */
    public function prepareDataForPieChart(array $data): array
    {
        $chartData = [];

        foreach ($data as $entries) {

            foreach($entries as $entry) {
                $index = array_search($entry['name'], array_column($chartData, 'name'));

                if ($index !== false) {
                    $chartData[$index]['value'] += $entry['total'];
                } else {
                    $chartData[] = [
                        'name' => $entry['name'],
                        'value' => $entry['total'],
                    ];
                }
            }
        }

        return $chartData;
    }

    /**
     * Process data to prepare it for the Echart bar chart
     *
     * @param array<string,array<int,array{name:string,total:int}>> $data
     * @return array{
     *     xAxis: array{
     *         data: array<int, string>
     *     },
     *     series: array<int, array{
     *         name: string,
     *         type: string,
     *         stack: string,
     *         label: array{
     *             show: bool
     *         },
     *         emphasis: array{
     *             focus: string
     *         },
     *         data: array<int, float|int>
     *     }>
     * }
     */
    public function prepareDataForBarChart(array $data): array
    {
        $periods = [];
        $versions = [];
        $result = [];

        foreach ($data as $monthYear => $entries) {
            if (!in_array($monthYear, $periods)) {
                $periods[] = $monthYear;
            }

            foreach ($entries as $entry) {
                $version = $entry['name'];
                $nbInstance = $entry['total'];

                if (!in_array($version, $versions)) {
                    $versions[] = $version;
                }
                $result[$monthYear][$version] = $nbInstance;
            }
        }

        usort($periods, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        sort($versions);

        foreach ($periods as $period) {
            foreach ($versions as $version) {
                if (!isset($result[$period][$version])) {
                    $result[$period][$version] = 0;
                }
            }

            ksort($result[$period]);
        }

        $preparedData = [
            'periods' => $periods,
            'versions' => $versions,
            'data' => $result
        ];

        $chartData = [
            'xAxis' => [
                'data' => $preparedData['periods']
            ],
            'series' => []
        ];

        $totalInstancesPerPeriod = [];
        foreach ($preparedData['data'] as $period => $versions) {
            $totalInstancesPerPeriod[$period] = array_sum($versions);
        }

        foreach ($preparedData['versions'] as $version) {
            $seriesData = [
                'name' => $version,
                'type' => 'bar',
                'stack' => 'total',
                'label' => [
                    'show' => false,
                ],
                'emphasis' => [
                    'focus' => 'series',
                ],
                'data' => []
            ];

            foreach ($preparedData['periods'] as $period) {
                $percentage = $totalInstancesPerPeriod[$period] > 0
                    ? round(($preparedData['data'][$period][$version] / $totalInstancesPerPeriod[$period]) * 100, 2)
                    : 0;
                $seriesData['data'][] = $percentage;
            }

            $chartData['series'][] = $seriesData;
        }

        return $chartData;
    }
}
