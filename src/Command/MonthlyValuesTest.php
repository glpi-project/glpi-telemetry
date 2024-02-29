<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ChartDataStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Telemetry\ChartSerie;

#[AsCommand(
    name: 'app:monthly-values-test',
    description: 'test getMonthlyValues()',
    hidden: false,
    aliases: ['app:monthly-values-test']
)]

class MonthlyValuesTest extends Command
{
    public function __construct(
        private ChartDataStorage $chartDataStorage,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $startDate = $this->chartDataStorage->getOldestDate();
            $endDate = date('Y-m-d', strtotime('-1 day'));
            $chartSerie = ChartSerie::GlpiVersion;
            $this->chartDataStorage->getMonthlyValues($chartSerie, new \DateTime($startDate), new \DateTime($endDate));
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->logger->error('Error in getMonthlyValues() test' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
