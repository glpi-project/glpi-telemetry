<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ChartDataStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

#[AsCommand(
    name: 'app:data-storage-update',
    description: 'update data storage',
    hidden: false,
    aliases: ['app:data-storage-update']
)]

class DataStorageUpdate extends Command
{
    public function __construct(
        private ChartDataStorage $chartDataStorage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $startDate = $this->chartDataStorage->getOldestDate();
            $endDate = date('Y-m-d', strtotime('-1 day'));

            $output->writeln('<info>Data storage update started ' . $startDate . '</info>');

            $start = new \DateTime($startDate);
            $end  = new \DateTime('-1 day');

            $iterationSize = 30;
            $diff = (int)$start->diff($end)->format('%a');

            $progressBar = new ProgressBar($output, (int)ceil($diff / $iterationSize));
            $progressBar->start();

            $currentStart = clone $start;
            do {
                $currentEnd  = (clone $currentStart)->modify('+ ' . $iterationSize . ' days');
                if ($currentEnd > $end) {
                    $currentEnd = $end;
                }

                $this->chartDataStorage->computeValues($currentStart, $currentEnd);

                $currentStart->modify('+ ' . ($iterationSize + 1) . ' days');
                $progressBar->advance();

            } while ($currentStart <= $end);

            $output->writeln('<info>Data storage update completed</info>');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<info>Data storage update failed</info>');
            return Command::FAILURE;
        }
    }
}
