<?php

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
        $this->logger->info('Enter computeValues() for period: '. $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'));

        $directory = __DIR__ . '/../../var/storage/chart-data/';
        $finder = new Finder();

        $this->logger->info('Main storage directory: '. $directory);

        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        $this->logger->info('Check existing repository OK');

        try {
            foreach (ChartSerie::cases() as $serie) {
                $this->logger->info('Processing serie: '. $serie->name);
                $serieName = $serie->name;
                $serieDirectory = $directory . $serieName;

                if (!$this->filesystem->exists($serieDirectory)) {
                    $this->filesystem->mkdir($serieDirectory);
                }

                $this->logger->info('Check existing repository for serie: '. $serieName . ' OK');

                for ($date = clone $start; $date <= $end; $date->add(\DateInterval::createFromDateString('1 day'))) {
                    $formattedDate = $date->format('Y-m-d');
                    $this->logger->info('Processing date: '. $formattedDate);
                    $file = $serieDirectory . '/' . $formattedDate . '.json';
                        if ($this->filesystem->exists($file)) {
                            $this->logger->info('File already exists for date: '. $date);
                            continue;
                        } else {
                            $this->logger->info('File does not exist for date: '. $date);
                            $sql = $serie->getSqlQuery();
                            $result = $this->connection->executeQuery($sql, ['startDate' => $start, 'endDate' => $end]);
                            $data = $result->fetchAllAssociative();
                            $json = json_encode($data);
                            $this->filesystem->dumpFile($serieDirectory . '/' . $date . '.json', $json);
                        }
                }
            }

        } catch (\Throwable $e) {
            $this->logger->error('Error during computeValues(): '. $e->getMessage());
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
