<?php

namespace App\Repository;

use App\Entity\Series;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Series|null find($id, $lockMode = null, $lockVersion = null)
 * @method Series|null findOneBy(array $criteria, array $orderBy = null)
 * @method Series[]    findAll()
 * @method Series[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Series::class);
    }

    public function getSeries(int $numPage = 1, int $numSeries)
    {
        $offset = $numSeries * ($numPage - 1);
        return $this->createQueryBuilder('s')
            ->select('s, r')
            ->join('s.externalRating', 'r')
            ->orderBy('s.title', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($numSeries)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getSeriesByName(string $search)
    {
        return $this->createQueryBuilder('s')
            ->where('s.title LIKE :search')
            ->setParameter('search', '%'.$search.'%')
            ->orderBy('s.title', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
