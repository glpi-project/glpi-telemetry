<?php

declare(strict_types=1);

namespace App\Controller;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DbEnginesController extends AbstractChartController
{
    #[Route('/db/engines/{periodFilter}')]
    public function index(ChartPeriodFilter $periodFilter): JsonResponse
    {
        $result = $this->getPieChartData(ChartSerie::DbEngine, $periodFilter);

        return new JsonResponse($result);
    }
}
