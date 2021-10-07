<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\Staff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    private $limit = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getByStaff(Staff $staff): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :staff')
            ->setParameter('staff', $staff)
            ->setMaxResult($this->limit)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
