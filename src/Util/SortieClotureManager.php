<?php

namespace App\Util;

use App\Enum\EtatEnum;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;

class SortieClotureManager
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private EtatRepository $etatRepository,
    ) {}

    public function cloturerSortiesArriveesALaDateLimite(): int
    {
        // Date range is 5 days in case a chron job has failed in previous days
        $today = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');
        $fiveDaysAgo = $today->modify('-5 days');

        $etatCloturee = $this->etatRepository->findOneBy(['libelle' => EtatEnum::Cloturee]);
        $etatOuverte = $this->etatRepository->findOneBy(['libelle' => EtatEnum::Ouverte]);

        return $this->sortieRepository->createQueryBuilder('s')
            ->update()
            ->set('s.etat', ':etatCloturee')
            ->where('s.dateLimiteInscription >= :start')
            ->andWhere('s.dateLimiteInscription < :end')
            ->andWhere('s.etat = :etatOuverte')
            ->setParameter('start', $fiveDaysAgo)
            ->setParameter('end', $tomorrow)
            ->setParameter('etatCloturee', $etatCloturee)
            ->setParameter('etatOuverte', $etatOuverte)
            ->getQuery()
            ->execute();
    }
}