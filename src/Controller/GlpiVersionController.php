<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Service\ChartDataStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Telemetry\ChartSerie;

class GlpiVersionController extends AbstractChartController
{
    #[Route('/glpi/version', name: 'app_glpi_version')]
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $period         = $this->getPeriodFromFilter($filter);

        $start          = new \DateTime($period['startDate']);
        $end            = new \DateTime($period['endDate']);

        $res = $chartDataStorage->getMonthlyValues(ChartSerie::GlpiVersion, $start, $end);

        $result = $this->processData($res);

        return new JsonResponse($result);
    }

    /**
     * @param array<string,mixed> $data
     * @return array<array<string,mixed>>
     */
    public function processData($data): array
    {
        $transformedData   = $this->transformDataForChart($data);
        $chartData         = $this->prepareChartData($transformedData);

        return $chartData;
    }

    /**
     * @param array<array<string,mixed>> $data
     * @return array{
     *   periods: array<int,string>,
     *   versions: array<int,string>,
     *   data: array<string,array<string,int>>
     * } $transformedData
     */
    public function transformDataForChart(array $data): array
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

                if (preg_match('/^(9|10)\.\d+$/', $version) !== 1) {
                    // ignore invalid versions (e.g. `1.6`, `V1`, ...)
                    // TODO Validate version against Github releases when telemetry data is received
                    continue;
                }

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

        return [
            'periods' => $periods,
            'versions' => $versions,
            'data' => $result
        ];
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
