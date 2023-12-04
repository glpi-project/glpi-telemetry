<?php

namespace App\Repository;

use App\Entity\Telemetry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Telemetry>
 *
 * @method Telemetry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Telemetry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Telemetry[]    findAll()
 * @method Telemetry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelemetryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Telemetry::class);
    }

    public function save(Telemetry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Telemetry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getGlpiVersion($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DATE_FORMAT(created_at, "%b-%Y") as month_year,
            SUBSTRING_INDEX(glpi_version, ".", 2) as version,
            COUNT(DISTINCT glpi_uuid) as nb_instance
            FROM telemetry
            WHERE glpi_version NOT LIKE "%dev" AND
            created_at BETWEEN :startDate AND :endDate
            GROUP BY month_year, version
            ORDER BY STR_TO_DATE(month_year, "%b-%Y"), version
            ';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getWebEngines($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT web_engine as webengine, COUNT(DISTINCT glpi_uuid) as nb_instance
            FROM telemetry
            WHERE created_at BETWEEN :startDate AND :endDate
            AND web_engine IS NOT NULL AND web_engine != ''
            GROUP BY webengine
            ORDER BY nb_instance
            ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getOsFamily($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT os_family as os, COUNT(DISTINCT glpi_uuid) as nb_instance
            FROM telemetry
            WHERE created_at BETWEEN :startDate AND :endDate
            GROUP BY os
            ORDER BY nb_instance
            ';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getPhpInfos($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql =  "
            SELECT DATE_FORMAT(created_at, '%b-%Y') as month_year,
            SUBSTRING_INDEX(php_version, '.', 2) as version,
            COUNT(DISTINCT glpi_uuid) as nb_instance
            FROM telemetry
            WHERE created_at BETWEEN :startDate AND :endDate
            GROUP BY month_year, version
            ORDER BY STR_TO_DATE(month_year, '%b-%Y'), version;
            ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getTopPlugin($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT pkey as pluginname, COUNT('glpi_plugin_id') as total
            FROM telemetry_glpi_plugin as tgp INNER JOIN glpi_plugin as gp ON
            tgp.glpi_plugin_id = gp.id
            WHERE tgp.created_at BETWEEN :startDate AND :endDate
            GROUP BY glpi_plugin_id
            ORDER BY total DESC
            LIMIT 10
            ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getDefaultLanguages($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT glpi_default_language as language,
        COUNT(DISTINCT glpi_uuid) as nb_instances
        FROM telemetry
        WHERE created_at BETWEEN :startDate AND :endDate
        GROUP BY language
        ORDER BY nb_instances DESC
        LIMIT 10
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getDbEngines($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT
            CASE
                WHEN UPPER(db_engine) LIKE '%MYSQL%' THEN 'MySQL'
                WHEN UPPER(db_engine) LIKE '%POSTGRES%' THEN 'PostgreSQL'
                WHEN UPPER(db_engine) LIKE '%PERCONA%' THEN 'Percona'
                WHEN UPPER(db_engine) LIKE '%MARIA%' THEN 'MariaDB'
                WHEN UPPER(db_version) LIKE '%POSTGRES%' THEN 'PostgreSQL'
                WHEN UPPER(db_version) LIKE '%PERCONA%' THEN 'Percona'
                WHEN UPPER(db_version) LIKE '%MARIA%' THEN 'MariaDB'
                ELSE 'MySQL'
            END as dbengine,
        COUNT(DISTINCT glpi_uuid) as nb_instances
        FROM telemetry
        WHERE created_at BETWEEN :startDate AND :endDate
        GROUP BY dbengine
        ORDER BY nb_instances
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getInstallMode($startDate, $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT install_mode as mode,
        COUNT(DISTINCT glpi_uuid) as nb_instances
        FROM telemetry
        WHERE created_at BETWEEN :startDate AND :endDate
        GROUP BY mode
        ORDER BY nb_instances
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }
}
