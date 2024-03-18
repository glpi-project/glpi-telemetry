<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Service\ChartDataStorage;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TopPluginController extends AbstractChartController
{
    #[Route('/top/plugin', name: 'app_top_plugin')]
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse
    {
        $filter         = $request->query->get('filter');

        ['start' => $start, 'end' => $end] = $this->getPeriodFromFilter($filter);

        $res            = $chartDataStorage->getMonthlyValues(ChartSerie::TopPlugin, $start, $end);

        $result         = $this->prepareDataForPieChart($res);

        usort($result, function ($a, $b) {
            return $b['value'] - $a['value'];
        });

        $topTenPlugin = array_slice($result, 0, 10);

        return new JsonResponse($topTenPlugin);
    }
}
