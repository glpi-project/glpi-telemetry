<?php

namespace  App\Service;

use App\Interface\ViewControllerInterface;
use App\Repository\TelemetryRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class RefreshCacheService
{
    public $startDate  = '';
    public $endDate    = '';
    private $telemetryRepository;
    private $cache;
    private $logger;

    public function __construct(
        TelemetryRepository $telemetryRepository,
        CacheInterface $cache,
        LoggerInterface $logger
    )
    {
        $this->telemetryRepository = $telemetryRepository;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function refreshAllCaches(string $filter, bool $forceUpdate): bool
    {
        $this->logger->debug('refreshAllCaches called');
            //récupérer toutes les classes dans le dossier Controller
            //ignorer les fichiers . et .. et .gitignore
            //pour chacune vérifier qu'elle implémente l'interface ViewControllerInterface
            //pour chacune appeler la méthode refreshCache avec les bons paramètres
            //retourner true si tout s'est bien passé, false sinon
            $controllerDir = __DIR__ . '/../Controller';
            $controllerFiles = scandir($controllerDir);
            $controllerFiles = array_filter($controllerFiles, function ($file) {
                return !in_array($file, ['.', '..', '.gitignore']);
            });

            $this->logger->debug('controllerFiles :', $controllerFiles);

            foreach ($controllerFiles as $controllerFile) {
                try {
                    $controllerName = str_replace('.php', '', $controllerFile);
                    $this->logger->debug($controllerName);

                    $controllerClass = "App\\Controller\\$controllerName";
                    $this->logger->debug("Trying to create instance of: $controllerClass");

                    $controller = new $controllerClass($this->logger);
                    $this->logger->debug("Instance created successfully: " . get_class($controller));

                    if ($controller instanceof ViewControllerInterface) {
                        $this->logger->debug($filter . $forceUpdate ."". get_class($controller));
                        $this->refreshCache($filter, $forceUpdate, $controller);
                    }
                } catch (Exception $e) {
                    $this->logger->error('Error refreshing cache for controller ' . $controllerName . ': ' . $e->getMessage());
                }
            }

            return true;

    }
    public function refreshCache($filter, $forceUpdate, $controller)
    {
        $this->logger->debug('refreshCache called'. $filter. ' '. $forceUpdate);
        $vueName = strtolower(get_class($controller));

        if ($forceUpdate) {
            $this->cache->delete("{$vueName}{$filter}");
            $this->logger->info('cache deleted :' . "{$vueName}{$filter}");
        }

        $data = $this->cache->get("{$vueName}{$filter}", function () use ($filter, $controller) {
            $this->setPeriod($filter);

            $dateParams = ['startDate' => $this->startDate, 'endDate' => $this->endDate];

            $data = $controller->getData($dateParams, $this->telemetryRepository);
            $this->logger->debug('data retreived from DB :', $data);

            return $data;
        });

        $this->logger->debug('data retreived from cache ' . "{$vueName}{$filter}");
        return $data;
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