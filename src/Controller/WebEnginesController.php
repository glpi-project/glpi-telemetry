<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractChartController;
use App\Service\ChartDataStorage;
use App\Telemetry\ChartSerie;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebEnginesController extends AbstractChartController
{
    #[Route('/web/engines', name: 'app_web_engines')]
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $period         = $this->getPeriodFromFilter($filter);

        $start          = new \DateTime($period['startDate']);
        $end            = new \Datetime($period['endDate']);

        $res = $chartDataStorage->getMonthlyValues(ChartSerie::WebEngine, $start, $end);

        $result = $this->prepareDataForPieChart($res);

        return new JsonResponse($result);
    }

}
