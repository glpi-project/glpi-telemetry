<?php

declare(strict_types=1);

namespace App\Controller;

class AbstractChartController
{
    public function getPeriodFromFilter(string $filter): array
    {
        $period  = [];
        $endDate = date("y-m-d");

        try {
            $startDate = match($filter) {
                'lastYear' => date('y-m-d', strtotime('-1 year')),
                'fiveYear' => date('y-m-d', strtotime('-5 years')),
                'always'   => date('y-m-d', strtotime('-10 years')),
                default    => throw new \Exception("Invalid filter value")
            };
            $period = ['startDate' => $startDate, 'endDate' => $endDate];
            return $period;
        } catch(\Exception $e) {
            $error_msg = $e->getMessage();
            $error = ['error' => $error_msg];
            return $error;
        }
    }

    public function prepareDataForPieChart(array $data): array
    {
        $chartData = [];
        foreach($data as $entry) {
            $index = array_search($entry['name'], array_column($chartData, 'name'));

            if ($index !== false) {
                $chartData[$index]['value'] += $entry['total'];
            } else {
                $chartData[] = [
                    'name' => $entry['name'],
                    'value' => $entry['total'],
                ];
            }
        }

        return $chartData;
    }
}
