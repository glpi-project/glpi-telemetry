<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TelemetryController extends AbstractController
{
    #[Route('/telemetry', name: 'app_telemetry')]
    public function index(TelemetryRepository $telemetryRepository): Response
    {
        // $endDate = date('Y-m-d');
        // $startDate = date('Y-m-d', strtotime('-5 years'));

        // $v_data     = $telemetryRepository->getGlpiVersion();
        // $we_data    = $telemetryRepository->getWebEngines($startDate,$endDate);
        // $os_data    = $telemetryRepository->getOsFamily();
        $php_data   = $telemetryRepository->getPhpInfos();
        // $top_plugin = $telemetryRepository->getTopPlugin();

        return $this->render('telemetry/index.html.twig', [
            'controller_name' => 'controller-name',
            // 'vdata'           => $v_data,
            // 'wedata'          => $we_data,
            // 'osdata'          => $os_data,
            'phpdata'         => $php_data,
            // 'topplugin'       => $top_plugin
        ]);
    }
}
