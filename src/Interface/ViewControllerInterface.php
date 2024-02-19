<?php

declare(strict_types=1);

namespace App\Interface;

use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface ViewControllerInterface
{
    public function index(Request $request, RefreshCacheService $refreshCacheService): JsonResponse;

    /**
     * @param array<string,string> $dateParams
     * @param TelemetryRepository $telemetryRepository
     * @return array<array<string,mixed>>
     */
    public function getData(array $dateParams, TelemetryRepository $telemetryRepository): array;
}
