<?php

namespace App\Repository;

use App\Entity\Series;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

    public function getSeries($currentPage = 1)
    {
        $query = $this->createQueryBuilder('s')
            ->select('s, r')
            ->join('s.externalRating', 'r')
            ->orderBy('s.title', 'ASC')
            ->getQuery()
        ;

        return $this->paginate($query, $currentPage);
    }
    
    public function getSeriesByName(string $search, $currentPage = 1)
    {
        $query = $this->createQueryBuilder('s')
        ->where('s.title LIKE :search')
        ->setParameter('search', '%' . $search . '%')
        ->orderBy('s.title', 'ASC')
        ->getQuery();

        return $this->paginate($query, $currentPage);
    }

    public function getRandomSeries($genre, $limit = 6){
        return $this->createQueryBuilder('s')
        ->select('s, r')
        ->join('s.externalRating', 'r')
        ->join('s.genre', 'g')
        ->where('g.name = :genre')
        ->setParameter('genre', $genre)
        ->setFirstResult(2) // demander
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }

    public function paginate($dql, $page = 1, $limit = 24)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

}
