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

class InstallModeController extends AbstractChartController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/install/mode', name: 'app_install_mode')]
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse
    {
        $filter         = $request->query->get('filter');
        $period         = $this->getPeriodFromFilter($filter);

        $start          = $period['startDate'];
        $end            = $period['endDate'];

        $res = $chartDataStorage->getMonthlyValues(ChartSerie::InstallMode, $start, $end);

        $result = $this->prepareDataForPieChart($res);

        return new JsonResponse($result);
    }
}
