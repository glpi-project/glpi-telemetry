<?php

declare(strict_types=1);

namespace App\Controller;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractChartController extends AbstractController
{
    /**
     * Set a period based on the filter value
     *
     * @param string $filter
     * @return array{start: \DateTimeInterface, end: \DateTimeInterface}
     */
    public function getPeriodFromFilter(string $filter): array
    {
        $start = match($filter) {
            'lastYear' => new DateTimeImmutable('-1 year'),
            'fiveYear' => new DateTimeImmutable('-5 years'),
            'always'   => new DateTimeImmutable('-10 years'),
            default    => throw new \Exception('Invalid filter value')
        };
        $end = new DateTimeImmutable();

        return [
            'start' => $start,
            'end'   => $end,
        ];
    }

    /**
     * Process data to prepare it for the Echart pie chart
     *
     * @param array<string, array<int, array{name: string, total: int}>> $data
     * @return array<int, array{name: string, value: int}>
     */
    public function prepareDataForPieChart(array $data): array
    {
        $chartData = [];

        foreach ($data as $entries) {
            foreach($entries as $entry) {
                $index = array_search($entry['name'], array_column($chartData, 'name'));

                if ($index !== false) {
                    $chartData[(int) $index]['value'] += $entry['total'];
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
     * @param array<string, array<int, array{name: string, total: int}>> $data
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
     *         data: array<int, float>
     *     }>
     * }
     */
    public function prepareDataForBarChart(array $data): array
    {
        $months = array_keys($data);
        usort($months, fn(string $a, string $b) => strtotime($a) - strtotime($b));

        // Extract totals by month and series names
        $names          = [];
        $totalsByPeriod = array_fill_keys($months, 0);

        foreach ($data as $period => $entries) {
            foreach ($entries as $entry) {
                $totalsByPeriod[$period] += $entry['total'];

                if (!in_array($entry['name'], $names, true)) {
                    $names[] = $entry['name'];
                }
            }
        }

        sort($names, SORT_NATURAL);

        // Format series data
        $series = [];

        foreach ($names as $serieName) {
            $serieData = [];
            foreach ($data as $period => $entries) {
                $total = 0;

                foreach ($entries as $entry) {
                    if ($entry['name'] === $serieName) {
                        $total = $entry['total'];
                        break;
                    }
                }

                $serieData[] = $totalsByPeriod[$period] > 0
                    ? round(($total / $totalsByPeriod[$period]) * 100, 2)
                    : 0;
            }

            $series[] = [
                'name' => $serieName,
                'type' => 'bar',
                'stack' => 'total',
                'label' => [
                    'show' => false,
                ],
                'emphasis' => [
                    'focus' => 'series',
                ],
                'data' => $serieData,
            ];
        }

        return [
            'xAxis' => [
                'data' => $months,
            ],
            'series' => $series,
        ];
    }
}
