<?php

namespace App\Controller;

use App\Service\RefreshTopPluginCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopPluginController extends AbstractController
{
    #[Route('/top/plugin', name: 'app_top_plugin')]
    public function index(Request $request, RefreshTopPluginCache $refreshTopPluginCache): Response
    {
        $startDate      = $request->query->get('startDate');
        $endDate        = $request->query->get('endDate');
        $filter         = $request->query->get('filter');
        $forceUpdate    = false;

        $result = $refreshTopPluginCache->refreshCache($startDate, $endDate, $filter, $forceUpdate);

        return $this->json($result);
    }
}
