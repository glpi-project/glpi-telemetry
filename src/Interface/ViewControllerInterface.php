<?php

declare(strict_types=1);

namespace App\Interface;

use App\Repository\TelemetryRepository;
use App\Service\ChartDataStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface ViewControllerInterface
{
    public function index(Request $request, ChartDataStorage $chartDataStorage): JsonResponse;

    /**
     * @param array<string,string> $data
     * @return array<array<string,mixed>>
     */
    public function processData(array $data): array;
}
