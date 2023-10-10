<?php

namespace App\Controller;

use App\Interface\ViewControllerInterface;
use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class GlpiVersionController extends AbstractController implements ViewControllerInterface
{
    #[Route('/glpi/version', name: 'app_glpi_version')]
    public function index(Request $request, RefreshCacheService $refreshCacheService): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;

        $result = $refreshCacheService->refreshCache($filter, $forceUpdate, $this);

        return $this->json($result);
    }

    public function getData(array $dateParams, TelemetryRepository $telemetryRepository) : array
    {

        $startDate      = $dateParams['startDate'];
        $endDate        = $dateParams['endDate'];

        $data = $telemetryRepository->getGlpiVersion($startDate, $endDate);

        return $data;
    }

}

