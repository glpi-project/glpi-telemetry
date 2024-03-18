<?php

declare(strict_types=1);

namespace App\Controller;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GlpiDefaultLanguagesController extends AbstractChartController
{
    #[Route('/glpi/default/languages/{periodFilter}')]
    public function index(ChartPeriodFilter $periodFilter): JsonResponse
    {
        $result = $this->getPieChartData(ChartSerie::DefaultLanguage, $periodFilter);

        return new JsonResponse($result);
    }
}
