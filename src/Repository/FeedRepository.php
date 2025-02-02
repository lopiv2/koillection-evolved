<?php

namespace App\Repository;

use App\Entity\Feed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feed>
 */
class FeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feed::class);
    }

    /**
     * Guarda un feed en la base de datos.
     */
    public function save(Feed $feed, bool $flush = true): void
    {
        $this->getEntityManager()->persist($feed);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Elimina un feed de la base de datos.
     */
    public function remove(Feed $feed, bool $flush = true): void
    {
        $this->getEntityManager()->remove($feed);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Encuentra feeds por una fuente específica.
     */
    public function findBySource(int $sourceId): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.source = :sourceId')
            ->setParameter('sourceId', $sourceId)
            ->orderBy('f.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra los feeds más recientes.
     */
    public function findLatestFeeds(int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
