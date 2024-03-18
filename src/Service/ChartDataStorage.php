<?php

declare(strict_types=1);

namespace App\Service;

use App\Telemetry\ChartSerie;
use DateTime;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ChartDataStorage
{
    private Connection $connection;
    private Filesystem $filesystem;
    private string $storageDir;

    public function __construct(Connection $connection, Filesystem $filesystem, string $storageDir)
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->storageDir = $storageDir;
    }

    /**
     * Compute charts data values and store them into the filesystem.
     */
    public function computeValues(DateTime $start, DateTime $end): void
    {
        $directory = $this->storageDir . '/chart-data';

        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        foreach (ChartSerie::cases() as $serie) {
            $serieDirectory = $directory . '/' . $serie->name;

            if (!$this->filesystem->exists($serieDirectory)) {
                $this->filesystem->mkdir($serieDirectory);
            }

            $alreadyComputedDates = [];

            $files = (new Finder())->files()->in($serieDirectory)->name('*.json');
            foreach ($files as $file) {
                $alreadyComputedDates[] = $file->getBasename('.json');
            }

            $currentDate = clone $start;
            while ($currentDate <= $end) {
                $date = $currentDate->format('Y-m-d');

                if (!in_array($date, $alreadyComputedDates, true)) {
                    $sql = $serie->getSqlQuery();
                    $result = $this->connection->executeQuery($sql, [
                        'startDate' => $date . ' 00:00:00',
                        'endDate' => $date . ' 23:59:59'
                    ])->fetchAllAssociative();
                    $this->filesystem->dumpFile($serieDirectory . '/' . $date . '.json', json_encode($result));
                }
                $currentDate->modify('+1 day');
            }
        }
    }

    /**
     *
     * Retrieve the data for the given period & serie from the file system
     * Process them to get the values filtered by month
     *
     * @return array<string, array<int, array{name: string, total: int}>>
     */
    public function getMonthlyValues(ChartSerie $serie, DateTime $start, DateTime $end): array
    {
        $directory = $this->storageDir . '/chart-data/' . $serie->name;
        $finder    = new Finder();
        $files     = $finder->files()->in($directory)->name('*.json');
        $dates     = [];

        foreach ($files as $file) {
            $dates[] = $file->getBasename('.json');
        }

        $monthlyValues = [];
        $currentDate   = clone $start;

        while ($currentDate <= $end) {
            $date          = $currentDate->format('Y-m-d');
            $monthKey      = $currentDate->format('Y-m');
            $dailyFileName = $date . '.json';

            if (in_array($date, $dates)) {
                $fileContents = file_get_contents($directory . '/' . $dailyFileName);
                if ($fileContents === false) {
                    throw new \Exception(sprintf('Error reading file %s.', $dailyFileName));
                }

                /** @var array<int, array{name: string, total: int}> $dailyData */
                $dailyData = json_decode($fileContents, true, flags: JSON_THROW_ON_ERROR);

                foreach ($dailyData as $versionData) {
                    $versionName = $versionData['name'];
                    $versionTotal = $versionData['total'];

                    if (!isset($monthlyValues[$monthKey])) {
                        $monthlyValues[$monthKey] = [];
                    }

                    $versionExists = false;
                    foreach ($monthlyValues[$monthKey] as &$existingVersionData) {
                        if ($existingVersionData['name'] === $versionName) {
                            $versionExists = true;
                            $existingVersionData['total'] += $versionTotal;
                            break;
                        }
                    }

                    if (!$versionExists) {
                        $monthlyValues[$monthKey][] = [
                            'name' => $versionName,
                            'total' => $versionTotal,
                        ];
                    }
                }
            }
            $currentDate->modify('+1 day');
        }
        return $monthlyValues;
    }

    /**
     * Retreive the oldest date in the telemetry table
     * @return DateTime
     */
    public function getOldestDate(): DateTime
    {
        $sql = <<<SQL
            SELECT MIN(created_at) as startDate
            FROM telemetry
        SQL;

        $result = $this->connection->executeQuery($sql)->fetchOne();

        return new DateTime($result);
    }
}
