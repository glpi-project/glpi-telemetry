<?php

namespace  App\Service;

use App\Repository\TelemetryRepository;
use Exception;
use Symfony\Contracts\Cache\CacheInterface;

class RefreshCacheService
{
    public $startDate  = '';
    public $endDate    = '';
    private $telemetryRepository;
    private $cache;

    public function __construct(
        TelemetryRepository $telemetryRepository,
        CacheInterface $cache,
    )
    {
        $this->telemetryRepository = $telemetryRepository;
        $this->cache = $cache;
    }

    public function refreshAllCaches(string $filter, string $forceUpdate): bool
    {
            //lister le contenu du dossier controller (directory iterator)
            //vérifier que c'est bien une classe + implémente interface ViewControllerInterface
            // pour chacune appeler la fonction refreshCache()
    }
    public function refreshCache($filter, $forceUpdate, $controller)
    {

        $vueName = strtolower(get_class($controller));

        if ($forceUpdate) {
            $this->cache->delete("{$vueName}{$filter}");
        }

        return $this->cache->get("{$vueName}{$filter}", function () use ($filter, $controller) {
            $this->setPeriod($filter);
            $dateParams = ['startDate' => $this->startDate, 'endDate' => $this->endDate];
            return $controller->getData($dateParams, $this->telemetryRepository);
        });
    }

    public function setPeriod(string $filter): string
    {
        $this->endDate = date("y-m-d h:i:s");

        try {
            $this->startDate = match($filter) {
                'lastYear' => date('y-m-d h:i:s', strtotime('-1 year')),
                'fiveYear' => date('y-m-d h:i:s', strtotime('-5 years')),
                'always'   => date('y-m-d h:i:s', strtotime('-10 years'))
            };
            return $this->startDate;
        }
        catch(Exception $e) {
            $error_msg = $e->getMessage();
            return $error_msg;
        }

    }
}