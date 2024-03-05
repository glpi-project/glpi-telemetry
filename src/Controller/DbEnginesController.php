<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Telemetry\ChartSerie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DbEnginesController extends AbstractChartController
{
    #[Route('/db/engines', name: 'app_db_engines')]
    public function index(Request $request): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $period         = $this->getPeriodFromFilter($filter);

        $start          = new \DateTime($period['startDate']);
        $end            = new \Datetime($period['endDate']);

        $res = $this->chartDataStorage->getMonthlyValues(ChartSerie::DbEngine, $start, $end);

        $result = $this->prepareDataForPieChart($res);

        return new JsonResponse($result);
    }
}
