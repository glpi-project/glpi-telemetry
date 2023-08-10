<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebEnginesController extends AbstractController
{
    #[Route('/web/engines', name: 'app_web_engines')]
    public function index(TelemetryRepository $telemetryRepository, Request $request, Response $response): Response
    {

        if ($request) {
            $data = $request->toArray();
            $startDate = $data['startDate'];
            $endDate = $data['endDate'];
            var_dump($startDate);
            var_dump($endDate);

            $webengine_data = $telemetryRepository->getWebEngines($startDate,$endDate);

            $response->setContent('Request ok');
            $response->send();
        } else {
            $response->setContent('Request failed');
            $response->send();
        }

        return $this->render('telemetry/webengine.html.twig', [
            'weupdatedata' => $webengine_data,
        ]);
    }
}
