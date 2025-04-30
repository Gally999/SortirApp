<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\ParticipantRepository;
use App\Enum\EtatEnum;
use App\Form\SortieFilterType;
use App\Model\SortieSearchData;
use App\Repository\EtatRepository;
use App\Entity\Participant;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties')]
final class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_list', methods: ['GET'])]
    public function list(
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
        Request $request,
    ): Response {
        $participant = $participantRepository->find($this->getUser()->getId());
        $searchData = new SortieSearchData();
        $searchForm = $this->createForm(SortieFilterType::class, $searchData, [
            'user' => $participant,
        ]);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {

            $sorties = $sortieRepository->findSortiesWithFilters(
                $searchData->campus,
                $participant,
                $searchData->searchTerm,
                $searchData->startDate,
                $searchData->endDate,
                $searchData->isOrganisateur,
                $searchData->isInscrit,
                $searchData->isNotInscrit,
                $searchData->showTerminees
            );
        } else {
            // Sorties par défaut - état = actif + campus de l'utilisateur connecté
            $sorties = $sortieRepository->findSortiesActives($participant->getCampus());
        }

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'searchForm' => $searchForm,
            'currentUser' => $participant,
        ]);
    }

    #[Route('/creer', name: 'sortie_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, CampusRepository $campusRepository): Response
    {
        // dd($this->getUser()->getCampus());


        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        $user = $entityManager->getRepository(Participant::class)->find($this->getUser()->getId());
        $campus = $user->getCampus();

        if ($form->isSubmitted() && $form->isValid()) {


            $action = $request->get('action'); // 'save' ou 'publish'
            if ($action === 'publish') {
                $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::Ouverte]);
            } else {

                $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::EnCreation]);
            }

            if ($etat) {
                $sortie->setEtat($etat);
            }
            $sortie->setOrganisateur($user);
            $sortie->setCampus($campus);

            $entityManager->persist($sortie);
            $entityManager->flush();


            $this->addFlash('success', 'Sortie créée avec succès (État : ' . $etat->getLibelleString() . ')');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form,
            'campus' => $campus
        ]);
    }

    #[Route('/modify/{id}', name: 'sortie_modify', methods: ['GET', 'POST'])]
    public function modifySortie($id, Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, SortieRepository $sortieRepository): Response
    {;
        $user = $entityManager->getRepository(Participant::class)->find($this->getUser()->getId());
        $campus = $user->getCampus();
        $sortie = $sortieRepository->find($id);

        // autorisation de modification d'une sortie (rules)
        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable');
            return $this->redirectToRoute('sortie_list');
        } elseif ($this->getUser()->getId() !== $sortie->getOrganisateur()->getId()) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette sortie');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getEtat()->getLibelle() != EtatEnum::EnCreation) {
            $this->addFlash('error', 'Vous ne pouvez modifier qu\'une sortie en cours de création (état actuel : ' .  $sortie->getEtat()->getLibelleString() . ')');
            return $this->redirectToRoute('sortie_list');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $action = $request->get('action'); // 'save' ou 'publish'
            if ($action === 'publish') {
                $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::Ouverte]);
            } else {

                $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::EnCreation]);
            }

            if ($etat) {
                $sortie->setEtat($etat);
            }
            $sortie->setOrganisateur($user);
            $sortie->setCampus($campus);

            $entityManager->persist($sortie);
            $entityManager->flush();


            $this->addFlash('success', 'Sortie mise à jour avec succès (État : ' . $etat->getLibelleString() . ')');
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('sortie/modify.html.twig', [
            'form' => $form,
            'campus' => $campus,
            'sortie' => $sortie

        ]);
    }

    #[Route('/delete/{id}', name: 'sortie_delete', methods: ['POST'])]
    public function deleteSortie($id, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, Request $request)
    {
        $user = $entityManager->getRepository(Participant::class)->find($this->getUser()->getId());
        $sortie = $sortieRepository->find($id);

        // autorisation de suppression d'une sortie (rules)
        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable');
            return $this->redirectToRoute('sortie_list');
        } elseif ($this->getUser()->getId() !== $sortie->getOrganisateur()->getId()) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de supprimer cette sortie');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getEtat()->getLibelle() != EtatEnum::EnCreation) {
            $this->addFlash('error', 'Vous ne pouvez supprimer qu\'une sortie en cours de création (état actuel : ' .  $sortie->getEtat()->getLibelleString() . ')');
            return $this->redirectToRoute('sortie_list');
        } elseif (!$this->isCsrfTokenValid('delete' . $sortie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide');
            return $this->redirectToRoute('sortie_list');
        } elseif (!$user->isAdministrateur()) {
            $this->addFlash('error', 'Il faut être administrateur pour gérer une sortie');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie instanceof Sortie) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Sortie supprimée avec succès');
            return $this->redirectToRoute('app_profile');
        }
    }
}
