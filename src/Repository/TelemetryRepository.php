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
            COUNT(DISTINCT glpi_uuid) as users
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

        $sql = '
            SELECT web_engine as name, COUNT(id) as value
            FROM telemetry
            WHERE created_at BETWEEN :startDate AND :endDate
            GROUP BY name
            ';
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
            SELECT os_family as name, COUNT(id) as value
            FROM telemetry
            WHERE created_at BETWEEN :startDate AND :endDate
            GROUP BY os_family
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
            SELECT SUBSTRING_INDEX(php_version, '.', 2) as version, COUNT('version') as count
            FROM telemetry
            WHERE created_at BETWEEN :startDate AND :endDate
            GROUP BY version
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
            SELECT pkey as name, count('glpi_plugin_id') as value
            FROM telemetry_glpi_plugin as tgp INNER JOIN glpi_plugin as gp ON
            tgp.glpi_plugin_id = gp.id
            WHERE tgp.created_at BETWEEN :startDate AND :endDate
            GROUP BY glpi_plugin_id
            ORDER BY value desc
            LIMIT 10
            ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }



//    /**
//     * @return Telemetry[] Returns an array of Telemetry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Telemetry
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
