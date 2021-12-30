<?php

namespace App\Repository;

use App\Entity\ExternalRating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExternalRating|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExternalRating|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExternalRating[]    findAll()
 * @method ExternalRating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalRating::class);
    }

    public function getSeriesRating($series, $sourceId = 1)
    {
        $ratings = [];
        foreach($series as $serie){
            $rating = $this->createQueryBuilder('r')
                ->select('r.value')
                ->andWhere('r.series= :sid AND r.source = :srcid')
                ->setParameter('sid', $serie->getId())
                ->setParameter('srcid', $sourceId)
                ->getQuery()
                ->getOneOrNullResult();
            $ratings[$serie->getId()] = $rating['value'];
        }
        return $ratings;
    }
}
