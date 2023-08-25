<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GlpiVersionController extends AbstractController
{
    public $glpiVersionData;

    #[Route('/glpi/version', name: 'app_glpi_version')]

    public function index(TelemetryRepository $telemetryRepository, Request $request, CacheInterface $cache): Response
    {
            $startDate = $request->query->get('startDate');
            $endDate   = $request->query->get('endDate');

                $this->glpiVersionData = $cache->get('glpi_version_data', function(ItemInterface $item) use($telemetryRepository, $startDate, $endDate) {
                    $item->expiresAfter(3600);

                        return $telemetryRepository->getGlpiVersion($startDate, $endDate);
                });

                        return $this->json($this->glpiVersionData);

        // $startDate = $request->query->get('startDate');
        // $endDate = $request->query->get('endDate');

        // $result = $telemetryRepository->getGlpiVersion($startDate, $endDate);

        // return $this->json($result);
    }

}

