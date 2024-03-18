<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ChartDataStorage;
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
    /**
     * Iteration size, in days.
     */
    private const ITERATION_SIZE = 30;

    public function __construct(
        private ChartDataStorage $chartDataStorage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = $this->chartDataStorage->getOldestDate();
        $end   = new DateTimeImmutable('-1 day');

        $progressBar = new ProgressBar(
            $output,
            (int) ceil((int) $start->diff($end)->format('%a') / self::ITERATION_SIZE)
        );
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%%' . PHP_EOL . '%message%' . PHP_EOL);
        $progressBar->setMessage('');
        $progressBar->start();

        $currentStart = $start;
        do {
            $currentEnd  = $currentStart->modify('+ ' . self::ITERATION_SIZE . ' days');
            if ($currentEnd > $end) {
                $currentEnd = $end;
            }

            $progressBar->setMessage(
                sprintf(
                    '<comment>Computing values from %s to %s...</comment>',
                    $currentStart->format('Y-m-d'),
                    $currentEnd->format('Y-m-d')
                )
            );
            $progressBar->display();

            $this->chartDataStorage->computeValues($currentStart, $currentEnd);

            $currentStart = $currentStart->modify('+ ' . (self::ITERATION_SIZE + 1) . ' days');
            $progressBar->advance();

        } while ($currentStart <= $end);

        $progressBar->setMessage('<info>Charts data computation completed.</info>');
        $progressBar->finish();

        return Command::SUCCESS;
    }
}
