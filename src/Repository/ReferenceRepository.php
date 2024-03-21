<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reference>
 *
 * @method Reference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reference[]    findAll()
 * @method Reference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reference::class);
    }

    public function save(Reference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reference $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns references count by country.
     *
     * @return array<string, int>
     */
    public function getReferencesCountByCountries(): array
    {
        $queryBuilder = $this->createQueryBuilder('reference');
        $queryBuilder->select('reference.country', 'COUNT(reference.id) AS total')
            ->where($queryBuilder->expr()->isNotNull('reference.country'))
            ->addGroupBy('reference.country');

        /** @var array<array{country: string, total: int}> $result */
        $result = $queryBuilder->getQuery()->getArrayResult();

        $countByCountry = [];
        foreach ($result as $row) {
            $countByCountry[strtolower($row['country'])] = $row['total'];
        }

        return $countByCountry;
    }
}
