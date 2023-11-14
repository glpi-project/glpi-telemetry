<?php

namespace App\Controller;

use App\Interface\ViewControllerInterface;
use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebEnginesController extends AbstractController implements ViewControllerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/web/engines', name: 'app_web_engines')]
    public function index(Request $request, RefreshCacheService $refreshCacheService): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;

        $result = $refreshCacheService->refreshCache($filter, $forceUpdate, $this);

        return $this->json($result);
    }

    public function getData(array $Dateparams, TelemetryRepository $telemetryRepository): array
    {
        $startDate      = $Dateparams['startDate'];
        $endDate        = $Dateparams['endDate'];

        $data = $telemetryRepository->getWebEngines($startDate, $endDate);
        $chartData = $this->prepareChartData($data);

        return $chartData;
    }

    public function prepareChartData(array $data): array
    {
        $chartData = [];

        foreach ($data as $entry) {
            $chartData[] = [
            'name'  => $entry['webengine'],
            'value' => $entry['nb_instance'],
            ];
        }

        $this->logger->info('chartData :', $chartData);
        return $chartData;
    }
}
