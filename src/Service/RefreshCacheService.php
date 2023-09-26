<?php

namespace  App\Service;

use App\Repository\TelemetryRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RefreshCacheService
{
    public $startDate  = '';
    public $endDate    = '';
    private $telemetryRepository;
    private $cache;

    public function __construct(
        TelemetryRepository $telemetryRepository,
        CacheInterface $cache,
    ){
        $this->telemetryRepository = $telemetryRepository;
        $this->cache = $cache;
    }
    public function RefreshCache($filter, $vueName, $forceUpdate) {

        $this->assignStartDate($filter);

        if ($forceUpdate) {
            $this->cache->delete("{$vueName}{$filter}");
        }
    }

    public function assignStartDate($filter) {
        $this->startDate = match ($filter) {
            'lastYear' => date('y-m-d h:i:s', strtotime('-1 year')),
            'fiveYear' => date('y-m-d h:i:s', strtotime('-5 years')),
            'always'   => date('y-m-d h:i:s', strtotime('-10 years')),
        };
    }

    public function matchViewToRequest($vueName) {
        
    }
}