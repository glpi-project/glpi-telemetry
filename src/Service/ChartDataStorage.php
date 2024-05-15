<?php

declare(strict_types=1);

namespace App\Service;

use App\Telemetry\ChartPeriodFilter;
use App\Telemetry\ChartSerie;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;

class ChartDataStorage
{
    private Connection $connection;
    private Filesystem $filesystem;
    private string $storageDir;
    private ?DateTimeImmutable $oldestDate = null;

    public function __construct(Connection $connection, Filesystem $filesystem, string $storageDir)
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->storageDir = $storageDir;
    }

    /**
     * Compute monthly values and store them into the filesystem.
     */
    public function computeMonthlyValues(ChartSerie $serie, DateTimeInterface $month, bool $force = false): void
    {
        $serieDirectory = $this->storageDir . '/chart-data/' . $serie->name;

        if (!$this->filesystem->exists($serieDirectory)) {
            $this->filesystem->mkdir($serieDirectory);
        }

        $monthFile = $serieDirectory . '/' . $month->format('Y-m') . '.json';

        if (
            $this->filesystem->exists($monthFile)
            && filemtime($monthFile) > strtotime($month->format('Y-m-t 23:59:59'))
            && $force === false
        ) {
            // Do not recompute values when file exists, unless it has been computed before the end
            // of the corresponding month.
            return;
        }

        $result = $this->connection->executeQuery(
            $serie->getSqlQuery(),
            [
                'startDate' => $month->format('Y-m-01 00:00:00'),
                'endDate'   => $month->format('Y-m-t 23:59:59'),
            ],
        )->fetchAllAssociative();

        $this->filesystem->dumpFile(
            $monthFile,
            json_encode($result, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Compute total values corresponding to given period and store them into the filesystem.
     */
    public function computePeriodTotalValues(ChartSerie $serie, ChartPeriodFilter $periodFilter, bool $force = false): void
    {
        $serieDirectory = $this->storageDir . '/chart-data/' . $serie->name;

        if (!$this->filesystem->exists($serieDirectory)) {
            $this->filesystem->mkdir($serieDirectory);
        }

        $periodFile = $serieDirectory . '/' . $periodFilter->value . '.json';

        if (
            $this->filesystem->exists($periodFile)
            && filemtime($periodFile) > strtotime('today midnight')
            && $force === false
        ) {
            // Do not recompute values when file exists, unless it has been computed before today.
            return;
        }

        $result = $this->connection->executeQuery(
            $serie->getSqlQuery(),
            [
                // expand to first day of start month and to last day of end month in order to display full months data
                'startDate' => $periodFilter->getStartDate()->format('Y-m-01 00:00:00'),
                'endDate'   => $periodFilter->getEndDate()->format('Y-m-t 23:59:59'),
            ],
        )->fetchAllAssociative();

        $this->filesystem->dumpFile(
            $periodFile,
            json_encode($result, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Retrieve the monthly values for the given period & serie.
     *
     * @return array<string, array<int, array{name: string, value: int}>>
     */
    public function getMonthlyValues(ChartSerie $serie, ChartPeriodFilter $periodFilter): array
    {
        $directory = $this->storageDir . '/chart-data/' . $serie->name;

        if (!$this->filesystem->exists($directory)) {
            // No data exists for serie
            return [];
        }

        $monthlyValues = [];
        $oldest        = $this->getOldestTelemetryDate();
        $start         = $periodFilter->getStartDate();
        $end           = $periodFilter->getEndDate();
        $currentDate   = new DateTimeImmutable(($start > $oldest ? $start : $oldest)->format('Y-m-01 00:00:00'));
        while ($currentDate <= $end) {
            $monthKey      = $currentDate->format('Y-m');
            $monthFileName = $directory . '/' . $monthKey . '.json';

            if ($this->filesystem->exists($monthFileName)) {
                $fileContents = file_get_contents($monthFileName);
                if ($fileContents === false) {
                    throw new \Exception(sprintf('Error reading file `%s`.', $monthFileName));
                }

                /** @var array<int, array{name: string, value: int}> $monthData */
                $monthData = json_decode($fileContents, true, flags: JSON_THROW_ON_ERROR);
                $monthlyValues[$monthKey] = $monthData;
            }

            $currentDate = $currentDate->modify('+1 month');
        }

        return $monthlyValues;
    }

    /**
     * Retrieve the total values for the given period & serie.
     *
     * @return array<int, array{name: string, value: int}>
     */
    public function getPeriodTotalValues(ChartSerie $serie, ChartPeriodFilter $periodFilter): array
    {
        $periodFile = $this->storageDir . '/chart-data/' . $serie->name . '/' . $periodFilter->value . '.json';

        if (!$this->filesystem->exists($periodFile)) {
            // No data exists
            return [];
        }

        $fileContents = file_get_contents($periodFile);
        if ($fileContents === false) {
            throw new \Exception(sprintf('Error reading file `%s`.', $periodFile));
        }

        /** @var array<int, array{name: string, value: int}> $periodData */
        $periodData = json_decode($fileContents, true, flags: JSON_THROW_ON_ERROR);

        return $periodData;
    }

    /**
     * Retreive the date of the oldest telemetry entry.
     *
     * @return DateTimeInterface
     */
    public function getOldestTelemetryDate(): DateTimeInterface
    {
        if ($this->oldestDate === null) {
            $sql = <<<SQL
                SELECT MIN(created_at) as startDate
                FROM telemetry
            SQL;

            /** @var ?string $result */
            $result = $this->connection->executeQuery($sql)->fetchOne();

            $this->oldestDate = $result !== null ? new DateTimeImmutable($result) : new DateTimeImmutable();
        }

        return $this->oldestDate;
    }
}
