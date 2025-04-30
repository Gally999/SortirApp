<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findSortiesActives(): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder
            ->addSelect('e')
            ->leftJoin('s.etat', 'e')
            ->where('e.libelle IN (:etats)')
            ->setParameter('etats', EtatEnum::actives())
            ->addOrderBy('s.dateHeureDebut', 'ASC');
        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }

    public function findSortiesActivesWithParams(
        Campus $campus,
        ?string $searchTerm = null,
        ?\DateTime $startDate = null,
        ?\DateTime $endDate = null
    ): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder
            ->leftJoin('s.campus', 'c')
            ->addSelect('c')
            ->where('s.campus = :campus')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->andWhere('e.libelle IN (:etats)')
            ->setParameter('campus', $campus)
            ->setParameter('etats', EtatEnum::actives())
            ->addOrderBy('s.dateHeureDebut', 'ASC');

        if ($searchTerm) {
            $queryBuilder->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        if ($startDate) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
