<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\AbstractChartController;
use PHPUnit\Framework\TestCase;

class AnyChartController extends AbstractChartController
{

}

class AbstractChartControllerTest extends TestCase
{
    private AnyChartController $controller;

    protected function setUp(): void
    {
        $this->controller = new AnyChartController();
    }

    public function testGetPeriodFromFilter(): void
    {
        $result = $this->controller->getPeriodFromFilter('lastYear');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('startDate', $result);
        $this->assertArrayHasKey('endDate', $result);
    }

    public function testPrepareDataForPieChart(): void
    {
        $data = [
            [
                ['name' => 'Chrome', 'total' => 10],
                ['name' => 'Firefox', 'total' => 5],
            ],
            [
                ['name' => 'Chrome', 'total' => 15],
                ['name' => 'Safari', 'total' => 10],
            ],
        ];

        $result = $this->controller->prepareDataForPieChart($data);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }
}