<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractChartController extends AbstractController
{
    /**
     * @param string $filter
     * @return array<string,string>
     */
    public function getPeriodFromFilter(string $filter): array
    {
        $period  = [];
        $endDate = date("y-m-d");

        try {
            $startDate = match($filter) {
                'lastYear' => date('y-m-d', strtotime('-1 year')),
                'fiveYear' => date('y-m-d', strtotime('-5 years')),
                'always'   => date('y-m-d', strtotime('-10 years')),
                default    => throw new \Exception("Invalid filter value")
            };
            $period = ['startDate' => $startDate, 'endDate' => $endDate];
            return $period;
        } catch(\Exception $e) {
            $error_msg = $e->getMessage();
            $error = ['error' => $error_msg];
            return $error;
        }
    }

    /**
     * @param array<array<string,mixed>> $data
     * @return array<array<string,mixed>>
     */
    public function prepareDataForPieChart(array $data): array
    {
        $chartData = [];

        foreach ($data as $entries) {
            // monthly entries
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
     * @param array<array<string,mixed>> $data
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

        // Step 1: Loop through the data to fill the $periods and $versions arrays
        foreach ($data as $monthYear => $entries) {
            // Add the period to the $periods array if it's not already there
            if (!in_array($monthYear, $periods)) {
                $periods[] = $monthYear;
            }

            foreach ($entries as $entry) {
                $version = $entry['name'];
                $nbInstance = $entry['total'];

                // Add the version to the $versions array if it's not already there
                if (!in_array($version, $versions)) {
                    $versions[] = $version;
                }

                // Add the users to the corresponding period and version
                $result[$monthYear][$version] = $nbInstance;
            }
        }

        // Step 2: Sort the periods
        usort($periods, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        // Step 3: Sort the versions
        sort($versions);

        // Step 4: Fill in missing values with 0
        foreach ($periods as $period) {
            foreach ($versions as $version) {
                if (!isset($result[$period][$version])) {
                    $result[$period][$version] = 0;
                }
            }
            // Sort the versions within each period
            ksort($result[$period]);
        }

        $preparedData = [
            'periods' => $periods,
            'versions' => $versions,
            'data' => $result
        ];

        return $this->prepareChartData($preparedData);
    }

    /**
     * @param array{
     *     periods: array<int, string>,
     *     versions: array<int, string>,
     *     data: array<string, array<string, int>>
     * } $transformedData
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
    public function prepareChartData(array $transformedData): array
    {
        $chartData = [
            'xAxis' => [
                'data' => $transformedData['periods']
            ],
            'series' => []
        ];

        // Calculer le total des instances pour chaque période
        $totalInstancesPerPeriod = [];
        foreach ($transformedData['data'] as $period => $versions) {
            $totalInstancesPerPeriod[$period] = array_sum($versions);
        }

        foreach ($transformedData['versions'] as $version) {
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

            foreach ($transformedData['periods'] as $period) {
                $percentage = $totalInstancesPerPeriod[$period] > 0
                    ? round(($transformedData['data'][$period][$version] / $totalInstancesPerPeriod[$period]) * 100, 2)
                    : 0;
                $seriesData['data'][] = $percentage;
            }

            $chartData['series'][] = $seriesData;
        }

        return $chartData;
    }
}
