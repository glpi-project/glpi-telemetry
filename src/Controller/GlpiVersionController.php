<?php

namespace App\Controller;

use App\Interface\ViewControllerInterface;
use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class GlpiVersionController extends AbstractController implements ViewControllerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/glpi/version', name: 'app_glpi_version')]
    public function index(Request $request, RefreshCacheService $refreshCacheService): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;

        $result = $refreshCacheService->refreshCache($filter, $forceUpdate, $this);

        return $this->json($result);
    }

    public function getData(array $dateParams, TelemetryRepository $telemetryRepository): array
    {
        $startDate      = $dateParams['startDate'];
        $endDate        = $dateParams['endDate'];

        $data              = $telemetryRepository->getGlpiVersion($startDate, $endDate);
        $transformedData   = $this->transformDataForChart($data);
        $chartData         = $this->prepareChartData($transformedData);

        return $chartData;
    }

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

    public function prepareChartData(array $transformedData): array
    {
        $chartData = [
            'xAxis' => [
                'data' => $transformedData['periods']
            ],
            'series' => []
        ];

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
                $seriesData['data'][] = $transformedData['data'][$period][$version];
            }

            $chartData['series'][] = $seriesData;
        }

        return $chartData;
    }
}
