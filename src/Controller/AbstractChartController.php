<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ChartDataStorage;
use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractChartController extends AbstractController
{
    public function __construct(protected ChartDataStorage $chartDataStorage) {}

    /**
     * Get the Echart pie chart data for the given serie.
     *
     * @return array<int, array{name: string, value: int}>
     */
    public function getPieChartData(ChartSerie $serie, ChartPeriodFilter $periodFilter): array
    {
        $monthlyValues = $this->chartDataStorage->getMonthlyValues(
            $serie,
            $periodFilter->getStartDate(),
            $periodFilter->getEndDate()
        );

        $chartData = [];
        foreach ($monthlyValues as $entries) {
            foreach($entries as $entry) {
                $index = array_search($entry['name'], array_column($chartData, 'name'), true);

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
     * Get the Echart bar chart data for the given serie.
     *
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
    public function getBarChartData(ChartSerie $serie, ChartPeriodFilter $periodFilter): array
    {
        $monthlyValues = $this->chartDataStorage->getMonthlyValues(
            $serie,
            $periodFilter->getStartDate(),
            $periodFilter->getEndDate()
        );

        $months = array_keys($monthlyValues);
        sort($months, SORT_NATURAL);

        // Extract totals by month and series names
        $names         = [];
        $totalsByMonth = array_fill_keys($months, 0);

        foreach ($monthlyValues as $month => $entries) {
            foreach ($entries as $entry) {
                $totalsByMonth[$month] += $entry['total'];

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
            foreach ($monthlyValues as $month => $entries) {
                $total = 0;

                foreach ($entries as $entry) {
                    if ($entry['name'] === $serieName) {
                        $total = $entry['total'];
                        break;
                    }
                }

                $serieData[] = $totalsByMonth[$month] > 0
                    ? round(($total / $totalsByMonth[$month]) * 100, 2)
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
