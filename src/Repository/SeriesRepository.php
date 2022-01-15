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

    public function getSeries($sort = null, $search = null, $series = [])
    {
        $query = $this->createQueryBuilder('s')
            ->select('s, COALESCE(avg(r.value), 0), er')
            ->leftjoin('s.externalRating', 'er')
            ->leftJoin('s.ratings', 'r')
            ;

            if($search != null){
                $query->where('s.title LIKE :search')
                ->setParameter('search', '%' . htmlspecialchars($search) . '%');
            }

            if(!empty($series)){
                $query->andWhere('s.id IN (:series)');
                $query->setParameter('series', $series); 
            }
            
            if($sort != null){
                $query->orderBy('avg(r.value)', $sort == 'ASC' ? 'ASC' : 'DESC');
                $query->addOrderBy('s.title', 'ASC');
            } else {
                $query->orderBy('s.title', 'ASC');
            }

            $query->groupBy('s.id')
            ->getQuery()
            ->getResult()
            ;
        return $query;
    }

    public function getRandomSeries($genre, $limit = 4){
        return $this->createQueryBuilder('s')
        ->select('s, r')
        ->join('s.externalRating', 'r')
        ->join('s.genre', 'g')
        ->where('g.id = :genre')
        ->setParameter('genre', $genre)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }
}