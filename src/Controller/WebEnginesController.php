<?php

namespace App\Controller;

use App\Service\RefreshWebEnginesCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebEnginesController extends AbstractController
{
    public $webEngineData;

    #[Route('/web/engines', name: 'app_web_engines')]

    public function index(Request $request, RefreshWebEnginesCache $refreshWebEnginesCache): JsonResponse
    {
        $startDate      = $request->query->get('startDate');
        $endDate        = $request->query->get('endDate');
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;

        $result = $refreshWebEnginesCache->refreshCache($startDate, $endDate, $filter, $forceUpdate);

        return $this->json($result);
    }
}
