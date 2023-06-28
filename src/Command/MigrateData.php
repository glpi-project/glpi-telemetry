<?php

namespace App\Command;

use App\Service\PostgresConnection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:migrate-data',
    description: 'make a data migration from Postresql to MySQL',
    hidden: false,
    aliases: ['app:migrate-data']
)]

class MigrateData extends Command {

    private $postGresCo;
    private $retreiveData;

    public function __construct(PostgresConnection $postgresConnection) {
        parent::__construct();
        $this->postGresCo = $postgresConnection->getPostGresConnection();
        $this->retreiveData = $postgresConnection->getPostgresData();
    }

    protected function execute(InputInterface $input , OutputInterface $output ): int {
        if ($this->postGresCo) {
            $output->writeln("You are connected to Postgres database");
                if ($this->retreiveData) {
                    $output->writeln("Data retreived successfully");
                    return Command::SUCCESS;
                } else {
                    $output->writeln("Failed to retreive data");
                    return Command::FAILURE;
                };
        } else {
            $output->writeln(" Connection to Postgres database failed");
            return Command::FAILURE;
        }
    }


}
