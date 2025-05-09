<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\Model\SortieFilterData;
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
    public function findSortiesActives(?Participant $user = null): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder
            ->leftJoin('s.campus', 'c')->addSelect('c')
            ->leftJoin('s.etat', 'e')->addSelect('e')
            ->leftJoin('s.organisateur', 'o')->addSelect('o')
            ->where(
                $queryBuilder->expr()->orX(
                    'e.libelle IN (:etats)',
                    'e.libelle = :etatEnCreation AND o = :user'
                )
            )
            ->setParameter('etats', EtatEnum::actives())
            ->setParameter('etatEnCreation', EtatEnum::EnCreation)
            ->setParameter('user', $user)
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $user->getCampus())
            ->addOrderBy(
                "CASE WHEN e.libelle = :etatEnCreation THEN 1 ELSE 0 END",
                "ASC"
            )
            ->addOrderBy('s.dateHeureDebut', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findSortiesWithFilters(
        SortieFilterData $filterData,
        ?Participant $user = null,
    ): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder
            ->leftJoin('s.campus', 'c')->addSelect('c')
            ->leftJoin('s.etat', 'e')->addSelect('e')
            ->join('s.organisateur', 'o')->addSelect('o')
            ->where('s.campus = :campus')
            ->setParameter('campus', $filterData->campus);

        if ($filterData->showTerminees) {
            $queryBuilder->andWhere('e.libelle = :etatTerminee')
                ->setParameter('etatTerminee', EtatEnum::Terminee);
        } else {
            $or = $queryBuilder->expr()->orX(
                'e.libelle IN (:etats)'
            );

            // Si un user est connecté, on ajoute aussi les sorties "en création" dont il est organisateur
            if ($user) {
                $queryBuilder->setParameter('user', $user);
                $or->add('e.libelle = :etatEnCreation AND s.organisateur = :user');
                $queryBuilder
                    ->setParameter('etatEnCreation', EtatEnum::EnCreation)
                    ->addOrderBy(
                        "CASE WHEN e.libelle = :etatEnCreation THEN 1 ELSE 0 END",
                        "ASC"
                    );
            }

            $queryBuilder->andWhere($or)
                ->setParameter('etats', EtatEnum::actives());

            // Tri
            $queryBuilder
                ->addOrderBy('s.dateHeureDebut', 'ASC');
        }

        if ($filterData->searchTerm) {
            $queryBuilder->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $filterData->searchTerm . '%');
        }

        if ($filterData->startDate) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut >= :startDate')
                ->setParameter('startDate', $filterData->startDate);
        }

        if ($filterData->endDate) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut <= :endDate')
                ->setParameter('endDate', $filterData->endDate);
        }

        if ($filterData->isOrganisateur && $user) {
            $queryBuilder
                ->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        // Si je suis inscrit
        if ($filterData->isInscrit && $user) {
            $queryBuilder
                ->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        // Si je ne suis PAS inscrit
        if ($filterData->isNotInscrit && $user) {
            $queryBuilder
                ->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
