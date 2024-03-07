<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Service\ChartDataStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Telemetry\ChartSerie;

class GlpiVersionController extends AbstractChartController
{
    #[Route('/glpi/version', name: 'app_glpi_version')]
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $period         = $this->getPeriodFromFilter($filter);
        $controllerName = 'GlpiVersionController';

        $start          = new \DateTime($period['startDate']);
        $end            = new \DateTime($period['endDate']);

        $res = $chartDataStorage->getMonthlyValues(ChartSerie::GlpiVersion, $start, $end);

        $result = $this->prepareDataForBarChart($res, $controllerName);

        return new JsonResponse($result);
    }
}
