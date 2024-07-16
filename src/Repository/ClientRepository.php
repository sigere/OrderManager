<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function getForDefaultView(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.deletedAt is null')
            ->orderBy('c.alias', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAliasIgnoreCase($params): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('upper(c.alias) like :alias')
            ->setParameter('alias', strtoupper($params['alias']))
            ->getQuery()
            ->getResult();
    }

    public function getForInvoices(int $year, ?int $month = null): array
    {
        $clients = $this->createQueryBuilder('c')
            ->andWhere('c.deletedAt is null')
            ->orderBy('c.alias', 'ASC')
            ->getQuery()
            ->getResult();

        $repo = $this->getEntityManager()->getRepository(Order::class);
        $result = [];

        foreach ($clients as $client) {
            try {
                $count = $repo->createQueryBuilder('o')
                    ->select('count(o.id)')
                    ->andWhere('o.deletedAt is null')
                    ->andWhere('o.settledAt is null')
                    ->andWhere('year(o.deadline) = :year')
                    ->setParameter('year', $year);

                if ($month) {
                    $count = $count
                        ->andWhere('month(o.deadline) = :month')
                        ->setParameter('month', $month);
                }

                $count = $count
                    ->andWhere('o.client = :client')
                    ->setParameter('client', $client)
                    ->getQuery()
                    ->getSingleScalarResult();
            } catch (NoResultException | NonUniqueResultException $e) {
                $count = 0;
            }
            if ($count > 0) {
                $result[] = [
                    'client' => $client,
                    'count' => $count
                ];
            }
        }

        return $result;
    }
}
