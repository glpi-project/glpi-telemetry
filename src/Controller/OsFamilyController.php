<?php

namespace App\Controller;

use App\Repository\TelemetryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class OsFamilyController extends AbstractController
{
    public $osFamilyData;

    #[Route('/os/family', name: 'app_os_family', stateless: true)]
    public function index(TelemetryRepository $telemetryRepository, Request $request, CacheInterface $cache): Response
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

            $this->osFamilyData = $cache->get('os_family_data', function(ItemInterface $item) use($telemetryRepository, $startDate, $endDate) {
                // $item->expiresAfter(3600);

                return $telemetryRepository->getOsFamily($startDate, $endDate);
            });

                return $this->json($this->osFamilyData);
    }
}
