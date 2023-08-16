<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OsFamilyController extends AbstractController
{
    #[Route('/os/family', name: 'app_os_family')]
    public function index(TelemetryRepository $telemetryRepository, Request $request): Response
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $osdata = $telemetryRepository->getOsFamily($startDate, $endDate);

        return $this->json($osdata);
    }
}
