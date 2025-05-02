<?php

namespace App\Repository;

use App\Entity\Campus;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Campus>
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }
    
    public function insertSortie(): void
    {
        $em = $this->getEntityManager();

        $etat = $em->getReference(\App\Entity\Etat::class, 2);
        $lieu = $em->getReference(\App\Entity\Lieu::class, 10);
        $campus = $em->getReference(\App\Entity\Campus::class, 2);
        $organisateur = $em->getReference(\App\Entity\Participant::class, 1);

        $sortie = new \App\Entity\Sortie();
        $sortie->setEtat($etat);
        $sortie->setLieu($lieu);
        $sortie->setCampus($campus);
        $sortie->setOrganisateur($organisateur);
        $sortie->setNom('azeazeazeaze');
        $sortie->setDateHeureDebut(new DateTimeImmutable());
        $sortie->setDuree(150);
        $sortie->setDateLimiteInscription(new \DateTimeImmutable());
        $sortie->setNbInscriptionMax(3);
        $sortie->setInfosSortie('test');

        $em->persist($sortie);
        $em->flush();
    }


    //    /**
    //     * @return Campus[] Returns an array of Campus objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Campus
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
