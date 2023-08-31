<?php

namespace App\Command;

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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Beggining refreshing process');
        echo "this a symfony command";

        return Command::SUCCESS;

    }
}