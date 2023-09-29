<?php

namespace App\Controller;

use App\Interface\ViewControllerInterface;
use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RefreshGlpiVersionCache;
use Symfony\Component\HttpFoundation\Request;

class GlpiVersionController extends AbstractController implements ViewControllerInterface
{
    public $glpiVersionData;

    #[Route('/glpi/version', name: 'app_glpi_version')]


    public function index(Request $request, RefreshCacheService $refreshCacheService): JsonResponse
    {
        // $startDate      = $request->query->get('startDate');
        // $endDate        = $request->query->get('endDate');
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;
        //$vueName        = 'glpi_version_';

        $result = $refreshCacheService->refreshCache($filter, $forceUpdate, $this);

        return $this->json($result);
    }

    public function getData(Request $request, TelemetryRepository $telemetryRepository) : array {

        $startDate      = $request->query->get('startDate');
        $endDate        = $request->query->get('endDate');

        $data = $telemetryRepository->getGlpiVersion($startDate, $endDate);

        return $data;
    }

}

