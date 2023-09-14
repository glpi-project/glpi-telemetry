<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopPluginController extends AbstractController
{
    #[Route('/top/plugin', name: 'app_top_plugin')]
    public function index(TelemetryRepository $telemetryRepository, Request $request): Response
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $top_plugin = $telemetryRepository->getTopPlugin($startDate, $endDate);

        return $this->json($top_plugin);
    }
}
