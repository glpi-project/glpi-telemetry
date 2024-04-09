<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ChartDataStorage;
use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use App\Telemetry\ChartType;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:compute-charts-data',
    description: 'Compute charts data'
)]
class ComputeChartsData extends Command
{
    public function __construct(
        private ChartDataStorage $chartDataStorage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Compute total values for each period range
        $output->writeln('<info>Computing total values for predefined periods...</info>');

        $series = array_filter(
            ChartSerie::cases(),
            // Compute only series that are not displayed as monthly stacked bars
            fn(ChartSerie $serie) => $serie->getChartType() !== ChartType::MonthlyStackedBar
        );
        $periods = ChartPeriodFilter::cases();
        $progressBar = new ProgressBar($output, count($periods) * count($series));
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%%' . PHP_EOL . '%message%' . PHP_EOL);
        $progressBar->setMessage('');
        $progressBar->start();

        foreach ($periods as $period) {
            foreach ($series as $serie) {
                $progressBar->setMessage(
                    sprintf(
                        '<comment>Computing values for "%s" period for serie "%s"...</comment>',
                        $period->getLabel(),
                        $serie->getTitle(),
                    )
                );
                $progressBar->display();

                $this->chartDataStorage->computePeriodTotalValues($serie, $period);
            }
        }

        $progressBar->setMessage('<comment>Predefined period total values computation completed.</comment>');
        $progressBar->finish();

        // Compute monthly values
        $output->writeln('<info>Computing monthly values...</info>');

        $series = array_filter(
            ChartSerie::cases(),
            // Compute only series that are displayed as monthly stacked bars
            fn(ChartSerie $serie) => $serie->getChartType() === ChartType::MonthlyStackedBar
        );
        $start  = new DateTimeImmutable($this->chartDataStorage->getOldestTelemetryDate()->format('Y-m-01 00:00:00'));
        $end    = new DateTimeImmutable('-1 day');
        $diff   = $start->diff($end);
        $months = ((int) $diff->format('%y')) * 12 + ((int) $diff->format('%m'));

        $progressBar = new ProgressBar($output, $months * count($series));
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%%' . PHP_EOL . '%message%' . PHP_EOL);
        $progressBar->setMessage('');
        $progressBar->start();

        $currentMonth = $start;
        do {
            foreach ($series as $serie) {
                $progressBar->setMessage(
                    sprintf(
                        '<comment>Computing values from %s to %s for serie "%s"...</comment>',
                        $currentMonth->format('Y-m-d'),
                        $currentMonth->format('Y-m-t'),
                        $serie->getTitle(),
                    )
                );
                $progressBar->display();

                $this->chartDataStorage->computeMonthlyValues($serie, $currentMonth);

                $progressBar->advance();
            }

            $currentMonth = $currentMonth->modify('+ 1 month');
        } while ($currentMonth <= $end);

        $progressBar->setMessage('<comment>Monthly values computation completed.</comment>');
        $progressBar->finish();

        return Command::SUCCESS;
    }
}
