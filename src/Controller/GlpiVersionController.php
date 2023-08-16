<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GlpiVersionController extends AbstractController
{
    #[Route('/glpi/version', name: 'app_glpi_version')]
    public function index(TelemetryRepository $telemetryRepository, Request $request): Response
    {
            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');

            $vdata = $telemetryRepository->getGlpiVersion($startDate, $endDate);

            return $this->json($vdata);
    }
}
