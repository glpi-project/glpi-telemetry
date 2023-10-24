<?php

namespace App\Command;

use App\Service\RefreshCacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'app:refresh-cache',
    description: 'refresh caches',
    hidden: false,
    aliases: ['app:refresh-cache']
)]

class RefreshCache extends Command
{
    public function __construct(
        private RefreshCacheService $refreshCacheService
    ){
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startDate   = 0;
        $endDate     = 0;
        $filter      = "lastYear";
        $forceUpdate = true;
        $date = date('y-m-d h:i:s');

        $output->writeln("{$date}Beggining refreshing process");

        $this->refreshCacheService->refreshAllCaches($filter, $forceUpdate);

        $output->writeln('cache refreshed');

        return Command::SUCCESS;
    }
}
