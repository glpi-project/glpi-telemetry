<?php

declare(strict_types=1);

namespace App\Service;

use App\Telemetry\ChartSerie;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Psr\Log\LoggerInterface;

class ChartDataStorage
{
    private Connection $connection;
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(Connection $connection, Filesystem $filesystem, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     *
     */
    public function computeValues(\DateTime $start, \DateTime $end): void
    {
        $this->logger->info('Enter computeValues() for period: ' . $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'));

        $directory = __DIR__ . '/../../var/storage/chart-data/';
        $finder = new Finder();

        $this->logger->info('Main storage directory: ' . $directory);

        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        $this->logger->info('Check main repository OK');

        try {
            foreach (ChartSerie::cases() as $serie) {
                $this->logger->info('Processing serie: ' . $serie->name);
                $serieName = $serie->name;
                $serieDirectory = $directory . $serieName;

                if (!$this->filesystem->exists($serieDirectory)) {
                    $this->filesystem->mkdir($serieDirectory);
                }

                $this->logger->info('Check existing repository for serie: ' . $serieName . ' OK');

                //récupérer les fichiers existants avec $finder
                $files = $finder->files()->in($serieDirectory)->name('*.json');

                //récupérer la liste des dates des fichiers existants
                $dates = [];
                foreach ($files as $file) {
                    $dates[] = $file->getBasename('.json');
                }

                //pour chaque date entre $start et $end, si la date existe dans $dates, passer à la suivante, sinon exécuter la requête SQL correspondante à la série et stocker le résultat dans un fichier JSON
                $currentDate = clone $start;
                while ($currentDate <= $end) {
                    $date = $currentDate->format('Y-m-d');
                    if (in_array($date, $dates)) {
                        $this->logger->info('File for date ' . $date . ' already exists');
                    } else {
                        $this->logger->info('File for date ' . $date . ' does not exist');
                        $sql = $serie->getSqlQuery();
                        $this->logger->info('SQL query: ' . $sql);
                        $result = $this->connection->executeQuery($sql, [
                            'startDate' => $date . ' 00:00:00',
                            'endDate' => $date . ' 23:59:59'
                        ])->fetchAllAssociative();
                        $this->logger->info('SQL query result: ' . json_encode($result));
                        $this->filesystem->dumpFile($serieDirectory . '/' . $date . '.json', json_encode($result));
                        $this->logger->info('File for date ' . $date . ' created');
                    }
                    $currentDate->modify('+1 day');
                }


            }
        } catch (\Throwable $e) {
            $this->logger->error('Error during computeValues(): ' . $e->getMessage());
        }



    }
    public function getMonthlyValues(ChartSerie $serie, \DateTime $start, \DateTime $end): array
    {

        $directory = __DIR__ . '/../../var/storage/chart-data/' . $serie->value;
        $finder = new Finder();

        //récupérer les fichiers sur la période entre $start et $end dans $directory avec $finder
        $files = $finder->files()->in($directory)->name('*.json')->date('>= ' . $start->format('Y-m-d'))->date('<= ' . $end->format('Y-m-d'));
        //extraire les données de chaque fichier et les regrouper de façon mensuelle dans un tableau qui aura le format suivant : ['Y-m' => [['name' => 'value', 'total' => 0], ...]]
        $monthlyValues = [];
        foreach ($files as $file) {
            $date = $file->getBasename('.json');
            $data = json_decode($file->getContents(), true);
            $month = substr($date, 0, 7);
            if (!isset($monthlyValues[$month])) {
                $monthlyValues[$month] = [];
            }
            $monthlyValues[$month][] = $data;
        }
        return $monthlyValues;

    }

    public function getOldestDate(): string
    {
        $sql = <<<SQL
            SELECT MIN(created_at) as startDate
            FROM telemetry
        SQL;

        //exécuter la requête
        $result = $this->connection->executeQuery($sql)->fetchOne();
        // retourner $result au format Y-m-d
        return date('Y-m-d', strtotime($result));
    }
}
