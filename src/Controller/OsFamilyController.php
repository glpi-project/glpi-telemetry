<?php

namespace App\Controller;

use App\Service\RefreshOsFamilyCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OsFamilyController extends AbstractController
{
    public $osFamilyData;

    #[Route('/os/family', name: 'app_os_family')]
    public function index(Request $request, RefreshOsFamilyCache $refreshOsFamilyCache): JsonResponse
    {
        $startDate = $request->query->get('startDate');
        $endDate   = $request->query->get('endDate');
        $filter    = $request->query->get('filter');


        $result = $refreshOsFamilyCache->refreshCache($startDate, $endDate, $filter);

        return $this->json($result);
    }
}
