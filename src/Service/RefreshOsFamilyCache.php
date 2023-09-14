<?php

namespace App\Service;

use App\Repository\TelemetryRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RefreshOsFamilyCache
{
    private $telemetryRepository;
    private $cache;

    public function __construct(
        TelemetryRepository $telemetryRepository,
        CacheInterface $cache,
    ){
        $this->telemetryRepository = $telemetryRepository;
        $this->cache = $cache;
    }

    public function refreshCache($startDate, $endDate, $filter) {

        if($startDate == 0 && $endDate == 0) {
            $endDate = date('y-m-d h:i:s');
            $startDate = date('y-m-d h:i:s', strtotime('-1 year'));
        };
        return $this->cache->get("os_family_{$filter}", function(ItemInterface $item) use($startDate, $endDate) {
            // $item->expiresAfter(60);
            return $this->telemetryRepository->getGlpiVersion($startDate, $endDate);
        });

    }
}