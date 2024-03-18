<?php

declare(strict_types=1);

namespace App\Controller;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WebEnginesController extends AbstractChartController
{
    #[Route('/web/engines/{periodFilter}')]
    public function index(ChartPeriodFilter $periodFilter): JsonResponse
    {
        $result = $this->getPieChartData(ChartSerie::WebEngine, $periodFilter);

        return new JsonResponse($result);
    }
}
