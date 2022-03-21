<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\RepertoryEntry;
use App\Service\UserPreferences\RepertoryPreferences;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepertoryEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepertoryEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepertoryEntry[]    findAll()
 * @method RepertoryEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepertoryEntryRepository extends ServiceEntityRepository
{
    private const LIMIT = 100;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepertoryEntry::class);
    }

    public function getByRepertoryPreferences(RepertoryPreferences $preferences): array
    {
        $entries = $this
            ->createQueryBuilder("e")
            ->innerJoin("e.order", "o");

        if ($staff = $preferences->getStaff()) {
            $entries
                ->andWhere("o.staff = :staff")
                ->setParameter("staff", $staff);
        }

        if ($year = $preferences->getYear()) {
            $entries
                ->andWhere("e.year = :year")
                ->setParameter("year", $year);
        }

        if ($month = $preferences->getMonth()) {
            $entries
                ->andWhere("month(e.createdAt) = :month")
                ->setParameter("month", $month);
        }

        return $entries
            ->setMaxResults(self::LIMIT)
            ->addOrderBy("e.year", "DESC")
            ->addOrderBy("e.number", "DESC")
            ->getQuery()
            ->getResult();
    }

    /**
     * @param RepertoryEntry $entry
     * @param Order $order
     * @return void
     * @throws \Exception
     */
    public function configureEntry(RepertoryEntry $entry, Order $order): void
    {
        if ($entry->getOrder() != null) {
            throw new \Exception("Entry already configured!");
        }

        $order->setRepertoryEntry($entry);
        $year = (int) $order->getDeadline()->format('Y');
        $entry->setYear($year);
        $entry->setNumber($this->getNumber($year));
    }

    /**
     * @param int $year
     * @return int
     */
    public function getNumber(int $year): int
    {
        $last = $this->createQueryBuilder('r')
            ->andWhere('r.year = :year')
            ->setParameter('year', $year)
            ->orderBy('r.number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (empty($last)) {
            return 1;
        }

        /** @var RepertoryEntry $entry */
        $entry = $last[0];
        return $entry->getNumber() + 1;
    }

    /**
     * @return array
     */
    public function getYearsUsed(): array
    {
        try {
            $first = $this
                ->createQueryBuilder('e')
                ->orderBy('e.year', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();

            $last = $this
                ->createQueryBuilder('e')
                ->orderBy('e.year', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (NonUniqueResultException|NoResultException $exception) {
            return [];
        }

        $result = [];
        $f = intval($first->getYear());
        $l = intval($last->getYear());
        for ($i = $f; $i <= $l; ++$i) {
            $result[(string) $i] = $i;
        }

        return $result;
    }
}
