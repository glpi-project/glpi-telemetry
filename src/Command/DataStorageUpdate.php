<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ChartDataStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $startDate = $this->chartDataStorage->getOldestDate();
            $this->logger->info('Data storage update started ' . $startDate);
            //stocker la date actuelle -1 jour dans $endDate
            $endDate = date('Y-m-d', strtotime('-1 day'));

            //appeler la méthode computeValues de $chartDataStorage avec les paramètres $startDate et $endDate
            $this->chartDataStorage->computeValues(new \DateTime($startDate), new \DateTime($endDate));

            //loguer la fin de l'exécution
            $this->logger->info('Data storage update complete');
            //retourner 1 succès
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Data storage update failed');
            return Command::FAILURE;
        }
    }
}
