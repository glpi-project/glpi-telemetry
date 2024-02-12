<?php

namespace App\Repository;

use App\Entity\GlpiPlugin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GlpiPlugin>
 *
 * @method GlpiPlugin|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlpiPlugin|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlpiPlugin[]    findAll()
 * @method GlpiPlugin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlpiPluginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GlpiPlugin::class);
    }

    public function save(GlpiPlugin $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GlpiPlugin $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Returns the GlpiPlugin entity corresponding to given key.
     *
     * @param string $key
     *
     * @return GlpiPlugin|null
     */
    public function findOneByPluginKey(string $key): GlpiPlugin|null
    {
        return $this->findOneBy(['pkey' => $key]);
    }
}
