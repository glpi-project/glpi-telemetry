<?php

namespace App\Service;

use App\Repository\TelemetryRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RefreshGlpiVersionCache
{
    public $glpiVersionData;
    private $telemetryRepository;
    private $cache;

    public function __construct(
        TelemetryRepository $telemetryRepository,
        CacheInterface $cache,
    ){
        $this->telemetryRepository = $telemetryRepository;
        $this->cache = $cache;
    }

    public function refreshCache($startDate, $endDate) {

        // if($startDate == 0 && $endDate == 0) {
        //     $endDate = date('y-m-d h:i:s');
        //     $startDate = date('y-m-d h:i:s', strtotime('-5 year'));
        // };

        return $this->cache->get('glpi_version_data', function(ItemInterface $item) use($startDate, $endDate) {

            return $this->telemetryRepository->getGlpiVersion($startDate, $endDate);

        });

    }
}