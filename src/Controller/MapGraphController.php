<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ReferenceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class MapGraphController extends AbstractController
{
    private LoggerInterface $logger;

    private CacheInterface $cache;

    public function __construct(LoggerInterface $logger, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    #[Route('/map/graph', name: 'app_map_graph')]
    public function index(Request $request, ReferenceRepository $referenceRepository): JsonResponse
    {
        $data = $referenceRepository->getReferencesCountByCountries();

        $this->logger->debug('data from getData');

        $countriesDataJson = file_get_contents(__DIR__ . '/../../vendor/mledoze/countries/dist/countries.json');
        $countriesData = json_decode($countriesDataJson, true);

        $transformedData = [];
        foreach ($countriesData as $country) {
            $transformedData[strtoupper($country['cca2'])] = [
                'name' => $country['name']['common'],
                'value' => 0,
            ];
        }
        foreach ($data as $isoa2 => $total) {
            $transformedData[strtoupper($isoa2)]['value'] = $total;
        }

        $transformedData = array_values($transformedData);
        return $this->json($transformedData);
    }

    #[Route('/map/countries', name: 'app_map_countries')]
    public function countries(): JsonResponse
    {
        $compiledGeoJson = $this->cache->get("countries.geo.json", function () {
            $this->logger->debug('Creating compiledGeoJson');

            $countriesDataJson = file_get_contents(__DIR__ . '/../../vendor/mledoze/countries/dist/countries.json');
            $countriesData = json_decode($countriesDataJson, true);

            $compiledGeoJson = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            foreach ($countriesData as $country) {
                $cca3 = strtolower($country['cca3']);

                if ($country['cca3'] === 'ATA') {
                    continue;
                }

                $geoJsonPath = __DIR__ . "/../../vendor/mledoze/countries/data/{$cca3}.geo.json";

                if (file_exists($geoJsonPath)) {
                    $geoJsonData = json_decode(file_get_contents($geoJsonPath), true);
                    if (isset($geoJsonData['features'])) {
                        foreach ($geoJsonData['features'] as &$feature) {
                            $feature['properties']['name'] = $country['name']['common'];
                        }
                        $compiledGeoJson['features'] = array_merge($compiledGeoJson['features'], $geoJsonData['features']);
                    }
                }
            }

            $this->logger->debug('CompiledGeoJson created from data');
            return json_encode($compiledGeoJson);
        });

        $this->logger->debug('CompiledGeoJson created from cache');

        return new JsonResponse($compiledGeoJson, json: true);
    }
}
