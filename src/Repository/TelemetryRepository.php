<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Telemetry;
use DateTimeImmutable;
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

    /**
     * Get count of existing  telemetry entries for the given date and the given GLPI UUID.
     * @param DateTimeImmutable $date
     * @param string $glpiUuid
     * @return int
     */
    public function countByDate(DateTimeImmutable $date, string $glpiUuid): int
    {
        $queryBuilder = $this->createQueryBuilder('telemetry');
        $queryBuilder->select('COUNT(telemetry.id) AS total')
            ->where($queryBuilder->expr()->between('telemetry.created_at', ':start_date', ':end_date'))
            ->andWhere($queryBuilder->expr()->eq('telemetry.glpi_uuid', ':glpi_uuid'))
            ->setParameter('start_date', $date->format('Y-m-d 00:00:00'))
            ->setParameter('end_date', $date->format('Y-m-d 23:59:59'))
            ->setParameter('glpi_uuid', $glpiUuid);

        /** @var string $result */
        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        return (int) $result;
    }
}
