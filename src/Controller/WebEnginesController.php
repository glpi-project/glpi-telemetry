<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebEnginesController extends AbstractController
{
    #[Route('/web/engines', name: 'app_web_engines')]
    public function index(TelemetryRepository $telemetryRepository, Request $request): Response
    {

            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');

            $webengine_data = $telemetryRepository->getWebEngines($startDate,$endDate);

            // foreach($webengine_data as $data) {
            //     $arrObj[] = [
            //         'value' => $data["count"],
            //         'name'  => $data["web_engine"]
            //     ];
            // }

            return $this->json($webengine_data);
    }
}
