<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Interface\ViewControllerInterface;

class OsFamilyController extends AbstractController implements ViewControllerInterface
{
    #[Route('/os/family', name: 'app_os_family')]
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

        $data = $telemetryRepository->getOsFamily($startDate, $endDate);

        return $data;
    }
}
