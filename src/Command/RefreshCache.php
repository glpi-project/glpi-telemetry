<?php

namespace App\Command;

use App\Service\RefreshGlpiVersionCache;
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
    private $glpiVersionCacheInt;
    public function __construct(
        RefreshGlpiVersionCache $refreshGlpiVersionCache
    ){
        $this->glpiVersionCacheInt = $refreshGlpiVersionCache;
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startDate = 0;
        $endDate   = 0;
        $filter    = "lastYear";
        $forceUpdate = true;

        $output->writeln('Beggining refreshing process');

        $this->glpiVersionCacheInt->refreshCache($startDate, $endDate, $filter, $forceUpdate);

        $output->writeln('cache refreshed');

        return Command::SUCCESS;
    }
}
