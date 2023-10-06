<?php

namespace App\Controller;

use App\Interface\ViewControllerInterface;
use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class TopPluginController extends AbstractController implements ViewControllerInterface
{
    #[Route('/top/plugin', name: 'app_top_plugin')]
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

        $data = $telemetryRepository->getTopPlugin($startDate, $endDate);

        return $data;
    }
}
