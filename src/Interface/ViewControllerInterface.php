<?php

namespace App\Interface;

use App\Repository\TelemetryRepository;
use App\Service\RefreshCacheService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface ViewControllerInterface
{
    public function index(Request $request, RefreshCacheService $refreshCacheService): JsonResponse;
    public function getData(array $dateParams, TelemetryRepository $telemetryRepository): array;
}
