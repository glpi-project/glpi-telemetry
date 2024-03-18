<?php

declare(strict_types=1);

namespace App\Controller;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OsFamilyController extends AbstractChartController
{
    #[Route('/os/family/{periodFilter}')]
    public function index(ChartPeriodFilter $periodFilter): JsonResponse
    {
        $result = $this->getPieChartData(ChartSerie::OsFamily, $periodFilter);

        return new JsonResponse($result);
    }
}
