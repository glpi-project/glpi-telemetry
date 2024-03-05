<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TopPluginController extends AbstractChartController
{
    #[Route('/top/plugin', name: 'app_top_plugin')]
    public function index(Request $request): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $period         = $this->getPeriodFromFilter($filter);

        $start          = new \DateTime($period['startDate']);
        $end            = new \DateTime($period['endDate']);

        $res = $this->chartDataStorage->getMonthlyValues(ChartSerie::TopPlugin, $start, $end);

        $result = $this->prepareDataForPieChart($res);

        return new JsonResponse($result);
    }
}
