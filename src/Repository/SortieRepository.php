<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Participant;
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

    public function findSortiesWithFilters(
        Campus $campus,
        ?Participant $user = null,
        ?string $searchTerm = null,
        ?\DateTime $startDate = null,
        ?\DateTime $endDate = null,
        bool $isOrganisateur = false,
        bool $isInscrit = false,
        bool $isNotInscrit = false,
        bool $showTerminees = false,
    ): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder
            ->leftJoin('s.campus', 'c')->addSelect('c')
            ->leftJoin('s.etat', 'e')->addSelect('e')
            ->where('s.campus = :campus')
            ->setParameter('campus', $campus)
            ->addOrderBy('s.dateHeureDebut', 'ASC');

        if (!$showTerminees) {
            $queryBuilder
                ->andWhere('e.libelle IN (:etats)')
                ->setParameter('etats', EtatEnum::actives());
        } else {
            $queryBuilder->andWhere('e.libelle = :etatTerminee')
                ->setParameter('etatTerminee', EtatEnum::Terminee);
        }

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

        if ($isOrganisateur) {
            $queryBuilder
                ->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        // Si je suis inscrit
        if ($isInscrit) {
            $queryBuilder
                ->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        // Si je ne suis PAS inscrit
        if ($isNotInscrit) {
            $queryBuilder->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
