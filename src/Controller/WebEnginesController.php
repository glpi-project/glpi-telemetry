<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class WebEnginesController extends AbstractController
{
    public $webEngineData;

    #[Route('/web/engines', name: 'app_web_engines')]

    public function index(TelemetryRepository $telemetryRepository, Request $request, CacheInterface $cache): Response
    {

            $startDate = $request->query->get('startDate');
            $endDate = $request->query->get('endDate');

                $this->webEngineData = $cache->get('web_engine_data', function(ItemInterface $item) use ($telemetryRepository, $startDate, $endDate) {
                    // $item->expiresAfter(3600);

                        return $telemetryRepository->getWebEngines($startDate, $endDate);

                });

                        return $this->json($this->webEngineData);

            // $webengine_data = $telemetryRepository->getWebEngines($startDate,$endDate);

            // foreach($webengine_data as $data) {
            //     $arrObj[] = [
            //         'value' => $data["count"],
            //         'name'  => $data["web_engine"]
            //     ];
            // }

            // return $this->json($webengine_data);
    }
}
