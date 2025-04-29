<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties')]
final class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_list', methods: ['GET', 'POST'])]
    public function list(SortieRepository $sortieRepository): Response
    {

        // $sorties = $sortieRepository->findBy(['etat.libelle' => ['En creation', 'Ouverte', 'Cloturee', 'En cours', 'Terminée']], ['dateHeureDebut' => 'DESC']);
        $sorties = $sortieRepository->findSortiesActives();
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
