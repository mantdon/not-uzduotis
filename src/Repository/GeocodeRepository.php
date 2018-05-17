<?php

namespace App\Repository;

use App\Entity\Geocode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Geocode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Geocode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Geocode[]    findAll()
 * @method Geocode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeocodeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Geocode::class);
    }

    public function getCount()
    {
        return $this->createQueryBuilder('g')
                    ->select('count(g.id)')
                    ->getQuery()
                    ->getSingleScalarResult();
    }
}
