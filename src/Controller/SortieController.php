<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Campus;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    #[Route('/creer', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, CampusRepository $campusRepository): Response
    {
        // dd($this->getUser()->getCampus());

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $action = $request->get('action');

            // $etatRepo = $entityManager->getRepository(Etat::class);

            $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::EnCreation]);

            if ($action === 'publier') {
                $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::Ouverte]);
            }

            if ($etat) {
                $sortie->setEtat($etat);
            }

            $sortie->setOrganisateur($this->getUser());


            $entityManager->persist($sortie);
            $entityManager->flush();


            $this->addFlash('success', 'Sortie créée avec succès (État : ' . $sortie->getEtat()->getLibelle() . ')');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/modify/{id}', name: 'sortie_modify', methods: ['GET', 'POST'])]
    public function modifySortie($id, Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {;
        $sortie = $sortieRepository->find($id);
        if ($this->getUser()->getId() !== $sortie->getOrganisateur()->getId()) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette sortie');
            return $this->redirectToRoute('sortie_list');
        } elseif (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable');
            return $this->redirectToRoute('sortie_list');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $request->get('action');

            // Choix de l'état selon le bouton
            $etatRepo = $entityManager->getRepository(Etat::class);
            $etat = $etatRepo->findOneBy(['libelle' => EtatEnum::EnCreation]);

            if ($action === 'publier') {
                $etat = $etatRepo->findOneBy(['libelle' => EtatEnum::Ouverte]);
            }

            if ($etat) {
                $sortie->setEtat($etat);
            }

            $sortie->setOrganisateur($this->getUser());

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec succès (État : ' . $sortie->getEtat()->getLibelle() . ')');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('sortie/modify.html.twig', [
            'form' => $form,
        ]);
    }
}
