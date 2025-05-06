<?php

namespace App\Util;

use App\Enum\EtatEnum;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;

class SortieArchiveManager
{
    public function __construct(
        private SortieRepository $sortieRepository,
        private EtatRepository $etatRepository,
    ) {}

    public function archiverSortiesAPlusUnMois(): int
    {
        $today = $today = new \DateTimeImmutable('today');
        $lastMonthStart = $today->modify('-1 month');
        $lastMonthEnd = $lastMonthStart->modify('+1 day');
        // dd($today, $lastMonthStart, $lastMonthEnd);

        $etatHistorisee = $this->etatRepository->findOneBy(['libelle' => EtatEnum::Historisee]);

        return $this->sortieRepository->createQueryBuilder('s')
            ->update()
            ->set('s.etat', ':etatHistorisee')
            ->where('s.dateHeureDebut >= :start')
            ->andWhere('s.dateHeureDebut < :end')
            ->setParameter('start', $lastMonthStart)
            ->setParameter('end', $lastMonthEnd)
            ->setParameter('etatHistorisee', $etatHistorisee)

            ->getQuery()
            ->execute();
    }
}