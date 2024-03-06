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
    private string $storageDir;

    public function __construct(Connection $connection, Filesystem $filesystem, string $storageDir, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->storageDir = $storageDir;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     *
     */
    public function computeValues(\DateTime $start, \DateTime $end): void
    {
        $this->logger->info('Enter computeValues() for period: ' . $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'));

        $directory = __DIR__ . $this->storageDir . '/chart-data/';
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

    /**
     * @param ChartSerie $serie
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array<string,array{name:string,total:int}[]>
     */
    public function getMonthlyValues(ChartSerie $serie, \DateTime $start, \DateTime $end): array
    {
        $this->logger->info('Enter getMonthlyValues() for serie: ' . $serie->name . ' and period: ' . $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'));

        $directory = __DIR__ . $this->storageDir . '/chart-data/' . $serie->name;
        $finder = new Finder();
        $files = $finder->files()->in($directory)->name('*.json');

        $dates = [];

        foreach ($files as $file) {
            $dates[] = $file->getBasename('.json');
        }

        $this->logger->info('Dates: ' . json_encode($dates));

        $monthlyValues = [];
        $currentDate = clone $start;

        while ($currentDate <= $end) {

            $this->logger->info('Processing date: ' . $currentDate->format('Y-m-d'));

            $date = $currentDate->format('Y-m-d');
            $monthKey = $currentDate->format('Y-m');
            $dailyFileName = $date . '.json';

            $this->logger->info('Month key: ' . $monthKey);
            $this->logger->info('Daily file name: ' . $dailyFileName);

            if (in_array($date, $dates)) {
                $this->logger->info('File for current date ' . $date . ' exists');
                $fileContents = file_get_contents($directory . '/' . $dailyFileName);
                if ($fileContents === false) {
                    throw new \Exception('Error reading file ' . $dailyFileName);
                }

                /** @var array{name:string,total:int}[] $dailyData */
                $dailyData = json_decode($fileContents, true, flags: JSON_THROW_ON_ERROR);

                foreach ($dailyData as $versionData) {
                    $versionName = $versionData['name'];
                    $versionTotal = $versionData['total'];

                    // Initialize the monthlyValues array for the month if necessary
                    if (!isset($monthlyValues[$monthKey])) {
                        $monthlyValues[$monthKey] = [];
                    }

                    // Check if the version data already exists in the monthlyValues for the current month
                    $versionExists = false;
                    foreach ($monthlyValues[$monthKey] as &$existingVersionData) {
                        if ($existingVersionData['name'] === $versionName) {
                            $versionExists = true;
                            $existingVersionData['total'] += $versionTotal;
                            break;
                        }
                    }

                    // If the version data does not exist, add it
                    if (!$versionExists) {
                        $monthlyValues[$monthKey][] = [
                            'name' => $versionName,
                            'total' => $versionTotal,
                        ];
                    }
                }

                $currentDate->modify('+1 day');
            }
        }
        return $monthlyValues;
    }

    /**
     * Retreive the oldest date in the telemetry table
     * @return \DateTime
     */
    public function getOldestDate(): \DateTime
    {
        $sql = <<<SQL
            SELECT MIN(created_at) as startDate
            FROM telemetry
        SQL;

        $result = $this->connection->executeQuery($sql)->fetchOne();

        return new \DateTime($result);
    }
}
