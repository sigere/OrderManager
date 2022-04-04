<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Order;
use App\Service\UserPreferences\IndexPreferences;
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
    public const LIMIT = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param IndexPreferences $preferences
     * @param int|null $rows
     * @return Order[]
     */
    public function getByIndexPreferences(IndexPreferences $preferences, int &$rows = null): array
    {
        $orders = $this->createQueryBuilder('o');

        $states = $preferences->getStates();
        if (empty($states) && !$preferences->getSettled() && !$preferences->getDeleted()) {
            return [];
        }

        $states = empty($states) ? ["invalid-state"] : $states;
        $statement = "o.state in (:states)";
        $orders->setParameter("states", $states);

        if ($preferences->getSettled()) {
            $statement .= " or o.settledAt is not null";
        } else {
            $orders = $orders
                ->andWhere("o.settledAt is null");
        }

        if ($preferences->getDeleted()) {
            $statement .= " or o.deletedAt is not null";
        } else {
            $orders = $orders
                ->andWhere("o.deletedAt is null");
        }

        $orders = $orders
            ->andWhere($statement);

        if ($staff = $preferences->getStaff()) {
            $orders = $orders
                ->andWhere('o.staff = :staff')
                ->setParameter('staff', $staff);
        }

        if ($client = $preferences->getClient()) {
            $orders = $orders
                ->andWhere('o.client = :client')
                ->setParameter('client', $client);
        }

        $dateType = $preferences->getDateType();
        if ($dateFrom = $preferences->getDateFrom()) {
            $orders
                ->andWhere('o.' . $dateType . ' >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo = $preferences->getDateTo()) {
            $dateTo->setTime(23, 59);
            $orders
                ->andWhere('o.' . $dateType . ' <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        $rows = (clone $orders)->select('count(o.id)')->getQuery()->getSingleScalarResult();

        return $orders
            ->setMaxResults(self::LIMIT)
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Client $client
     * @param int $year
     * @param int|null $month
     * @return Order[]
     */
    public function getForInvoicingByClient(Client $client, int $year, ?int $month = null): array
    {
        $orders = $this->createQueryBuilder('o')
            ->andWhere('o.deletedAt is null')
            ->andWhere('o.settledAt is null')
            ->andWhere('o.client = :client')
            ->setParameter('client', $client)
            ->andWhere('year(o.deadline) = :year')
            ->setParameter('year', $year);

        if ($month) {
            $orders = $orders
                ->andWhere('month(o.deadline) = :month')
                ->setParameter('month', $month);
        }

        return $orders
            ->orderBy('o.deadline', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
