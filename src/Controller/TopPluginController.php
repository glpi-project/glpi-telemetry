<?php

declare(strict_types=1);

namespace App\Controller;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TopPluginController extends AbstractChartController
{
    #[Route('/top/plugin/{periodFilter}')]
    public function index(ChartPeriodFilter $periodFilter): JsonResponse
    {
        $result = $this->getPieChartData(ChartSerie::TopPlugin, $periodFilter);

        usort($result, function ($a, $b) {
            return $b['value'] - $a['value'];
        });

        $topTenPlugin = array_slice($result, 0, 10);

        return new JsonResponse($topTenPlugin);
    }
}
