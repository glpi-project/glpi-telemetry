<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Telemetry;
use App\Telemetry\ChartType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ChartDataStorage;
use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;

class TelemetryController extends AbstractController
{
    public ChartDataStorage $chartDataStorage;
    public function __construct(ChartDataStorage $chartDataStorage)
    {
        $this->chartDataStorage = $chartDataStorage;
    }

    #[Route('/telemetry', name: 'app_telemetry_post', methods: ['POST'])]
    public function post(
        #[MapRequestPayload(serializationContext:['json_decode_associative' => false])]
        ?Telemetry $telemetry,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($telemetry === null) {
            return $this->json(['error' => 'Bad request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($telemetry);
            $entityManager->flush();
            return $this->json(['message' => 'OK']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/telemetry', name: 'app_telemetry')]
    public function index(): Response
    {
        return $this->render(
            'telemetry/index.html.twig',
            [
                'controller_name' => 'controller-name',
            ]
        );
    }

    #[Route('/telemetry/chart/{serie}/{type}/{periodFilter}')]
    public function chart(ChartSerie $serie, ChartType $type, ChartPeriodFilter $periodFilter): JsonResponse
    {
        $data = [];
        switch ($type) {
            case ChartType::Bar:
                $data = $this->getBarChartData($serie, $periodFilter);
                break;
            case ChartType::Pie:
                $data = $this->getPieChartData($serie, $periodFilter);
                break;
            case ChartType::NightingaleRose:
                $data = $this->getNightingaleRoseChartData($serie, $periodFilter);
                break;
        }

        return $this->json($data);
    }

    /**
     * Get the Echart pie chart data for the given serie.
     *
     * @param ChartSerie $serie
     * @param ChartPeriodFilter $periodFilter
     *
     * @return array{
     *   title: array{
     *     text: string
     *   },
     *   series: array{
     *     array{
     *      data: array<int, array{name: string, value: int}>
     *     }
     *   }
     * }
    */
    public function getPieChartData(ChartSerie $serie, ChartPeriodFilter $periodFilter): array
    {
        $monthlyValues = $this->chartDataStorage->getMonthlyValues(
            $serie,
            $periodFilter->getStartDate(),
            $periodFilter->getEndDate()
        );

        $chartData = [];
        foreach ($monthlyValues as $entries) {
            foreach($entries as $entry) {
                $index = array_search($entry['name'], array_column($chartData, 'name'), true);

                if ($index !== false) {
                    $chartData[$index]['value'] += $entry['total'];
                } else {
                    $chartData[] = [
                        'name' => $entry['name'],
                        'value' => $entry['total'],
                    ];
                }
            }
        }

        return [
            'title'  => [
                'text' => $serie->getTitle()
            ],
            'series' => [
                [
                    'data' => $chartData
                ]
            ]
        ];
    }

    /**
     * Get the Echart bar chart data for the given serie.
     *
     * @param ChartSerie $serie
     * @param ChartPeriodFilter $periodFilter
     *
     * @return array{
     *     title: array{
     *          text: string
     *     },
     *     xAxis: array{
     *         data: array<int, string>
     *     },
     *     series: array<int, array{
     *         name: string,
     *         type: string,
     *         stack: string,
     *         label: array{
     *             show: bool
     *         },
     *         emphasis: array{
     *             focus: string
     *         },
     *         data: array<int, float>
     *     }>
     * }
     */
    public function getBarChartData(ChartSerie $serie, ChartPeriodFilter $periodFilter): array
    {
        $monthlyValues = $this->chartDataStorage->getMonthlyValues(
            $serie,
            $periodFilter->getStartDate(),
            $periodFilter->getEndDate()
        );

        $months = array_keys($monthlyValues);
        sort($months, SORT_NATURAL);

        // Extract totals by month and series names
        $names         = [];
        $totalsByMonth = array_fill_keys($months, 0);

        foreach ($monthlyValues as $month => $entries) {
            foreach ($entries as $entry) {
                $totalsByMonth[$month] += $entry['total'];

                if (!in_array($entry['name'], $names, true)) {
                    $names[] = $entry['name'];
                }
            }
        }

        sort($names, SORT_NATURAL);

        // Format series data
        $series = [];

        foreach ($names as $serieName) {
            $serieData = [];
            foreach ($monthlyValues as $month => $entries) {
                $total = 0;

                foreach ($entries as $entry) {
                    if ($entry['name'] === $serieName) {
                        $total = $entry['total'];
                        break;
                    }
                }

                $serieData[] = $totalsByMonth[$month] > 0
                    ? round(($total / $totalsByMonth[$month]) * 100, 2)
                    : 0;
            }

            $series[] = [
                'name' => $serieName,
                'data' => $serieData,
            ];
        }

        return [
            'title' =>
            [
                'text' => $serie->getTitle()
            ],
            'xAxis' => [
                'data' => $months,
            ],
            'series' => $series,
        ];
    }

    /**
     * Get the Echart nightingale rose chart data for the given serie.
     * Sort the result by value in descending order.
     * Filter to retreive only the top 10 plugins.
     *
     * @param ChartSerie $serie
     * @param ChartPeriodFilter $periodFilter
     *
     * @return array{
     *  title: array{
     *      text: string
     *  },
     *  series: array{
     *      array{
     *          data: array<int, array{
     *              value: int,
     *              name: string
     *          }>
     *      }}
     *  }
     */
    public function getNightingaleRoseChartData(ChartSerie $serie, ChartPeriodFilter $periodFilter): array
    {
        $monthlyValues = $this->chartDataStorage->getMonthlyValues(
            $serie,
            $periodFilter->getStartDate(),
            $periodFilter->getEndDate()
        );

        $chartData = [];
        foreach ($monthlyValues as $entries) {
            foreach($entries as $entry) {
                $index = array_search($entry['name'], array_column($chartData, 'name'), true);

                if ($index !== false) {
                    $chartData[$index]['value'] += $entry['total'];
                } else {
                    $chartData[] = [
                        'value' => $entry['total'],
                        'name'  => $entry['name'],
                    ];
                }
            }
        }

        usort(
            $chartData,
            function (array $a, array $b): int {
                return $b['value'] - $a['value'];
            }
        );

        $filteredArray = array_slice($chartData, 0, 10);

        return [
            'title'  => [
                'text' => $serie->getTitle()
            ],
            'series' => [
                [
                    'data' => $filteredArray
                ]
            ]
        ];
    }
}
