<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Service\ChartDataStorage;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PhpVersionController extends AbstractChartController
{
    #[Route('/php/version', name: 'app_php_version')]
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse
    {
        $filter         = $request->query->get('filter');

        ['start' => $start, 'end' => $end] = $this->getPeriodFromFilter($filter);

        $res            = $chartDataStorage->getMonthlyValues(ChartSerie::PhpInfos, $start, $end);

        $result         = $this->prepareDataForBarChart($res);

        return new JsonResponse($result);
    }
}
