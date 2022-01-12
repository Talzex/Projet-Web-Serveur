<?php

namespace App\Repository;

use App\Entity\Rating;
use App\Entity\Series;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rating|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rating|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rating[]    findAll()
 * @method Rating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function isRated(User $user, Series $serie)
    {
        return $this->createQueryBuilder('r')
        ->select('count(r.id)')
        ->andWhere('r.user = :u')
        ->andWhere('r.series = :s')
        ->setParameter('u', $user)
        ->setParameter('s', $serie)
        ->getQuery()
        ->getSingleScalarResult() < 1 ? false : true;
    }

    public function getRating(User $user, Series $serie)
    {
        return $this->createQueryBuilder('r')
        ->andWhere('r.user = :u')
        ->andWhere('r.series = :s')
        ->setParameter('u', $user)
        ->setParameter('s', $serie)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function deleteRating(Rating $rating)
    {
        return $this->createQueryBuilder('r')
            ->delete()
            ->andWhere('r.series = :sid')
            ->andWhere('r.user = :uid')
            ->setParameter('sid', $rating->getSeries()->getId())
            ->setParameter('uid', $rating->getUser()->getId())
            ->getQuery()
            ->getResult();
    }
}
