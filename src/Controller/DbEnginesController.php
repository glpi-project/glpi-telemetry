<?php

declare(strict_types=1);

namespace App\Controller;

use App\Interface\ViewControllerInterface;
use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DbEnginesController extends AbstractController implements ViewControllerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/db/engines', name: 'app_db_engines')]
    public function index(Request $request, RefreshCacheService $refreshCacheService): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;

        $result = $refreshCacheService->refreshCache($filter, $forceUpdate, $this);

        return $this->json($result);
    }

    /**
     * @param array<string,string> $Dateparams
     * @return array<array<string,mixed>>
     */
    public function getData(array $Dateparams, TelemetryRepository $telemetryRepository): array
    {
        $startDate      = $Dateparams['startDate'];
        $endDate        = $Dateparams['endDate'];

        $data = $telemetryRepository->getDbEngines($startDate, $endDate);
        $chartData = $this->prepareChartData($data);

        return $chartData;
    }

    /**
     * @param array<array<string,mixed>> $data
     * @return array<array<string,mixed>>
     */
    public function prepareChartData(array $data): array
    {
        $chartData = [];

        foreach ($data as $entry) {
            $engine = ($entry['dbengine']);

            $chartData[] = [
            'name'  => $engine,
            'value' => $entry['nb_instances'],
            ];
        }

        $this->logger->info('chartData :', $chartData);

        return $chartData;
    }
}
