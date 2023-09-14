<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RefreshGlpiVersionCache;
use Symfony\Component\HttpFoundation\Request;

class GlpiVersionController extends AbstractController
{
    public $glpiVersionData;

    #[Route('/glpi/version', name: 'app_glpi_version')]


    public function index(Request $request, RefreshGlpiVersionCache $refreshGlpiVersionCache): JsonResponse
    {
        $startDate = $request->query->get('startDate');
        $endDate   = $request->query->get('endDate');
        $filter    = $request->query->get('filter');


        $result = $refreshGlpiVersionCache->refreshCache($startDate, $endDate, $filter);

        return $this->json($result);
    }

}

