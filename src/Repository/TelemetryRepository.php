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

    public function getAllGlpiVersion(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT glpi_version, COUNT(id)
            FROM telemetry
            GROUP BY glpi_version
            ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    public function getWebEngines(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT web_engine, COUNT(id)
            FROM telemetry
            GROUP BY web_engine
            ';
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();

            return $resultSet->fetchAllAssociative();
    }

    public function getOsFamily(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT os_family, COUNT(id)
            FROM telemetry
            GROUP BY os_family
            ';
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();

            return $resultSet->fetchAllAssociative();
    }

    public function getPhpInfos(): array
    {
    $conn = $this->getEntityManager()->getConnection();

    $sql = '
            SELECT split_part(php_version, '.', 2), count(*)
            FROM telemetry
            GROUP BY version
            ';
            $stmt = $conn->prepare($sql);
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
