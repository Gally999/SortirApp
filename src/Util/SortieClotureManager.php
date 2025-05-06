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
        $today = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');

        $etatCloturee = $this->etatRepository->findOneBy(['libelle' => EtatEnum::Cloturee]);
        $etatOuverte = $this->etatRepository->findOneBy(['libelle' => EtatEnum::Ouverte]);

        return $this->sortieRepository->createQueryBuilder('s')
            ->update()
            ->set('s.etat', ':etatCloturee')
            ->where('s.dateLimiteInscription >= :start')
            ->andWhere('s.dateLimiteInscription < :end')
            ->andWhere('s.etat = :etatOuverte')
            ->setParameter('start', $today)
            ->setParameter('end', $tomorrow)
            ->setParameter('etatCloturee', $etatCloturee)
            ->setParameter('etatOuverte', $etatOuverte)
            ->getQuery()
            ->execute();
    }
}