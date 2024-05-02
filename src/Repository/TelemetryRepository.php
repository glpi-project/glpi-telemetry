<?php

declare(strict_types=1);

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
}
