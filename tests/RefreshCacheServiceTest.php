<?php

use App\Controller\TelemetryController;
use PHPUnit\Framework\TestCase;
use App\Service\RefreshCacheService;
use App\Repository\TelemetryRepository;

class RefreshCacheServiceTest extends TestCase
{
    public function testRefreshCache()
    {
        //créer un mock de telemetryController
        //créer un mock de telemetryRepository
        //définir au TelemetryControllerMock que la méthode getData() retourne un array de valeurs simples
        //créer une instance de RefreshCacheService
        //lui passer le mock de telemetryController ?
        //appeler la méthode refreshCache avec les paramètres $filter = 'lastYear', $forceUpdate = true et $telemetryControllerMock
        //dans la méthode, définir l'array $dateParams avec les bonnes valeurs selon $filter
        //appeler la méthode getData() du telemetryControllerMock avec les bons paramètres
        //vérifier que la méthode getData a bien été appelée avec les bons paramètres
        //vérifier que les données ont bien été mises en cache
        //vérifier que la clé de cache est bien la bonne
    }
}
