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

class GlpiDefaultLanguagesController extends AbstractController implements ViewControllerInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/glpi/default/languages', name: 'app_glpi_default_languages')]
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

        $data = $telemetryRepository->getDefaultLanguages($startDate, $endDate);
        $chartData = $this->prepareChartData($data);

        return $chartData;
    }
    public function prepareChartData(array $data): array
    {
        $chartData = [];

        foreach ($data as $entry) {
            $chartData[] = [
            'name'  => $entry['language'],
            'value' => $entry['nb_instances'],
            ];
        }

        $this->logger->info('chartData :', $chartData);
        return $chartData;
    }
}
