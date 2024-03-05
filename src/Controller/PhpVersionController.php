<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Service\ChartDataStorage;
use App\Telemetry\ChartSerie;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PhpVersionController extends AbstractChartController
{
    #[Route('/php/version', name: 'app_php_version')]
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $period         = $this->getPeriodFromFilter($filter);

        $start          = new \DateTime($period['startDate']);
        $end            = new \Datetime($period['endDate']);

        $res = $chartDataStorage->getMonthlyValues(ChartSerie::PhpInfos, $start, $end);

        $result = $this->prepareDataForPieChart($res);

        return new JsonResponse($result);
    }

    /**
     * @param array<string,string> $Dateparams
     * @return array<array<string,mixed>>
     */
    public function processData(array $data): array
    {
        $transformedData   = $this->transformDataForChart($data);
        $chartData         = $this->prepareChartData($transformedData);

        return $chartData;
    }

    /**
     * @param array<array<string,mixed>> $data
     * @return array{
     *     periods: array<int,string>,
     *     versions: array<int,string>,
     *     data: array<string,array<string,int>>
     * } $transformedData
     */
    public function transformDataForChart(array $data): array
    {
        $periods = [];
        $versions = [];
        $result = [];

        // Step 1: Loop through the data to fill the $periods and $versions arrays
        foreach ($data as $entry) {
            $monthYear = $entry['month_year'];
            $version = $entry['version'];
            $nbInstance = $entry['nb_instance'];

            // Add the period to the $periods array if it's not already there
            if (!in_array($monthYear, $periods)) {
                $periods[] = $monthYear;
            }

            // Add the version to the $versions array if it's not already there
            if (!in_array($version, $versions)) {
                $versions[] = $version;
            }

            /// Initialize the array for the period if necessary
            if (!isset($result[$monthYear])) {
                $result[$monthYear] = [];
            }

            // Add the users to the corresponding period and version
            $result[$monthYear][$version] = $nbInstance;
        }

        // Étape 2 : Trier les périodes
        usort($periods, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        // Step 2: Sort the periods
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
        $this->logger->debug('result :', $result);
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
