<?php

namespace App\Repository;

use App\Entity\RepertoryEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepertoryEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepertoryEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepertoryEntry[]    findAll()
 * @method RepertoryEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepertoryEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepertoryEntry::class);
    }

    /**
     * @param int $year
     * @return int
     */
    public function getNumber(int $year): int
    {
        $last = $this->createQueryBuilder('r')
            ->andWhere('year(r.createdAt) = :year')
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
}
