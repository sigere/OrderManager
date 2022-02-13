<?php

namespace App\Repository;

use App\Entity\Order;
use App\Service\UserPreferences\AbstractOrderPreferences;
use App\Service\UserPreferences\ArchivesPreferences;
use App\Service\UserPreferences\IndexPreferences;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    private const LIMIT = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param IndexPreferences $preferences
     * @return Order[]
     */
    public function getByIndexPreferences(IndexPreferences $preferences): array
    {
        $states = $preferences->getStates();

        if (empty($states)) {
            return [];
        }

        $orders = $this->getQueryBuilderForAbstractOrderPreferences($preferences);

        foreach ($states as $key => $state) {
            $orders = $orders
                ->orWhere('o.state = :state' . $key)
                ->setParameter('state' . $key, $state);
        }

        return $orders
            ->andWhere('o.settledAt is null')
            ->andWhere('o.deletedAt is null')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param ArchivesPreferences $preferences
     * @return Order[]
     */
    public function getByArchivesPreferences(ArchivesPreferences $preferences): array
    {
        $orders = $this->getQueryBuilderForAbstractOrderPreferences($preferences);

        $orders
            ->andWhere('o.settledAt is not null');

        if ($preferences->getDeleted() == true) {
            $orders->orWhere('o.deletedAt is not null');
        }

        return $orders
            ->getQuery()
            ->getResult();
    }

    private function getQueryBuilderForAbstractOrderPreferences(AbstractOrderPreferences $preferences): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('o');

        if ($staff = $preferences->getStaff()) {
            $queryBuilder = $queryBuilder
                ->andWhere('o.staff = :staff')
                ->setParameter('staff', $staff);
        }

        if ($client = $preferences->getClient()) {
            $queryBuilder = $queryBuilder
                ->andWhere('o.client = :client')
                ->setParameter('client', $client);
        }

        $dateType = $preferences->getDateType();
        if ($dateFrom = $preferences->getDateFrom()) {
            $queryBuilder
                ->andWhere('o.' . $dateType . ' >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo = $preferences->getDateTo()) {
            $dateTo->setTime(23, 59);
            $queryBuilder
                ->andWhere('o.' . $dateType . ' <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        return $queryBuilder
            ->setMaxResults(self::LIMIT)
            ->orderBy('o.deadline', 'ASC');
    }
}
