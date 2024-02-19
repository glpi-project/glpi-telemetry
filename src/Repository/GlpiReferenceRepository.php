<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GlpiReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GlpiReference>
 *
 * @method GlpiReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlpiReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlpiReference[]    findAll()
 * @method GlpiReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlpiReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GlpiReference::class);
    }

    public function save(GlpiReference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GlpiReference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
