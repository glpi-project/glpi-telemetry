<?php

declare(strict_types=1);

namespace App\Controller;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PhpVersionController extends AbstractChartController
{
    #[Route('/php/version/{periodFilter}')]
    public function index(ChartPeriodFilter $periodFilter): JsonResponse
    {
        $result = $this->getBarChartData(ChartSerie::PhpInfos, $periodFilter);

        return new JsonResponse($result);
    }
}
