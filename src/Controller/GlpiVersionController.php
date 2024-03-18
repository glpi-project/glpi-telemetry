<?php

declare(strict_types=1);

namespace App\Controller;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class GlpiVersionController extends AbstractChartController
{
    #[Route('/glpi/version/{periodFilter}')]
    public function index(ChartPeriodFilter $periodFilter): JsonResponse
    {
        $result = $this->getBarChartData(ChartSerie::GlpiVersion, $periodFilter);

        return new JsonResponse($result);
    }
}
