<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TelemetryController extends AbstractController
{
    #[Route('/telemetry', name: 'app_telemetry')]
    public function index(): Response
    {
        return $this->render('telemetry/index.html.twig', [
            'controller_name' => 'TelemetryController',
        ]);
    }
}
