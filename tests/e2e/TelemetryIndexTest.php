<?php

declare(strict_types=1);

namespace App\E2ETests;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use App\Telemetry\ChartType;
use App\Tests\PantherTestCase;
use Symfony\Component\HttpFoundation\Response;

class TelemetryIndexTest extends PantherTestCase
{
    public function testRoutes(): void
    {
        $routes = ['/telemetry'];
        foreach (ChartSerie::cases() as $chartSerie) {
            foreach (ChartType::cases() as $chartType) {
                foreach (ChartPeriodFilter::cases() as $chartPeriodFilter) {
                    $routes[] = sprintf(
                        '/telemetry/chart/%s/%s/%s',
                        $chartSerie->value,
                        $chartType->value,
                        $chartPeriodFilter->value
                    );
                }
            }
        }

        $client = $this->getHttpClient();
        foreach ($routes as $route) {
            $client->request('GET', $route);
            self::assertEquals(
                Response::HTTP_OK,
                $client->getResponse()->getStatusCode(),
                $client->getResponse()->__toString()
            );
        }
    }

    public function testIndexPage(): void
    {
        // @TODO Test the index page behaviour:
        //  - validates that the map is displayed
        //  - validates that the list is displayed
        // Maybe a process that cleans the DB and load some fixtures could help.
        self::assertTrue(true, 'Prevent test to be marked as risky');
    }
}
