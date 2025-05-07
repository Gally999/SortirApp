<?php

namespace App\Controller;

use App\Entity\Annulation;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\Form\SortieFilterType;
use App\Form\SortieType;
use App\Model\SortieFilterData;
use App\Repository\AnnulationRepository;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        $searchData = new SortieFilterData();
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
            $sorties = $sortieRepository->findSortiesActives($participant);
        }

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'searchForm' => $searchForm,
            'currentUser' => $participant,
        ]);
    }

    #[Route(path: '/{id}', name: 'sortie_details', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function details(
        int $id,
        SortieRepository $sortieRepo,
        ParticipantRepository $participantRepo,
        AnnulationRepository $annulationRepo,
    ): Response
    {
        $sortie = $sortieRepo->find($id);
        $currentUser = $participantRepo->find($this->getUser()->getId());

        if (!$sortie) {
            throw $this->createNotFoundException("Cette sortie n'est pas disponible");
        }

        if (($sortie->getEtat()->getLibelle() == EtatEnum::EnCreation) && ($sortie->getOrganisateur() !== $currentUser)) {
            $this->addFlash('error', 'Vous n\'êtes pas authorisé à voir cette sortie' );
            return $this->redirectToRoute('sortie_list');
        }

        $annulation = null;
        if ($sortie->getEtat()->getLibelle() == EtatEnum::Annulee) {
            $annulation = $annulationRepo->findOneBy(['sortie' => $sortie], ['dateAnnulation' => 'DESC']);
        }

        return $this->render('sortie/details.html.twig', [
            "sortie" => $sortie,
            "currentUser" => $currentUser,
            "annulation" => $annulation,
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
            return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form,
            'campus' => $campus
        ]);
    }

    #[Route('/modify/{id}', name: 'sortie_modify', requirements: ['id'=>'\d+'], methods: ['GET', 'POST'])]
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
            return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
        }
        return $this->render('sortie/modify.html.twig', [
            'form' => $form,
            'campus' => $campus,
            'sortie' => $sortie
        ]);
    }

    #[Route('/delete/{id}', name: 'sortie_delete', requirements: ['id'=>'\d+'], methods: ['POST'])]
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
        }

        if ($sortie instanceof Sortie) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Sortie supprimée avec succès');
            return $this->redirectToRoute('sortie_list');
        }
    }

    #[Route('/{id}/publish', name: 'sortie_publish', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function publishSortie(
        int $id,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager
    ) {

        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable');
            return $this->redirectToRoute('sortie_list');
        } elseif ($this->getUser()->getId() !== $sortie->getOrganisateur()->getId()) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette sortie');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getEtat()->getLibelle() != EtatEnum::EnCreation) {
            $this->addFlash('error', 'Vous ne pouvez modifier qu\'une sortie en cours de création (état actuel : ' . $sortie->getEtat()->getLibelleString() . ')');
            return $this->redirectToRoute('sortie_list');
        }

        $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::Ouverte]);
        if ($etat) {
            $sortie->setEtat($etat);
        }
        // $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Sortie mise à jour avec succès (État : ' . $etat->getLibelleString() . ')');
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/{id}/inscription', name: 'sortie_inscription', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function inscription(
        int $id,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
        Request $request,
    )
    {
        $participant = $participantRepository->find($this->getUser()->getId());
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getEtat()->getLibelle() != EtatEnum::Ouverte) {
            $this->addFlash('error', 'Les inscriptions sont fermées pour cette sortie');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('error', 'Les inscriptions sont fermées pour cette sortie');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getNbInscriptionMax() <= $sortie->getParticipants()->count()) {
            $this->addFlash('error', 'Le nombre de participants maximum est atteint');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getOrganisateur() == $participant) {
            $this->addFlash('error', 'Vous êtes déjà inscrit•e à cette sortie en tant qu\'organisateur•ice');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getParticipants()->contains($participant)) {
            $this->addFlash('error', 'Vous êtes déjà inscrit•e à cette sortie');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getParticipants()->count() == $sortie->getNbInscriptionMax() -1) {
            $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::Cloturee]);
            $sortie->setEtat($etat);
        }

        $sortie->addParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit•e à la sortie ' .  $sortie->getNom() . ' du ' . $sortie->getDateHeureDebut()->format('d/m/Y'));
        $from = $request->query->get('from');
        return $this->redirectToRoute($from ?? 'sortie_details', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desinscrire', name: 'sortie_desinscription', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function desinscrire(
        int $id,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
        Request $request,
    )
    {
        $participant = $participantRepository->find($this->getUser()->getId());
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getEtat()->getLibelle() != EtatEnum::Ouverte && $sortie->getEtat()->getLibelle() != EtatEnum::Cloturee) {
            $this->addFlash('error', 'Vous ne pouvez plus vous désinscrire de cette sortie');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getParticipants()->count() == $sortie->getNbInscriptionMax() && $sortie->getDateLimiteInscription() > new \DateTime()) {
            $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::Ouverte]);
            $sortie->setEtat($etat);
        }

        $sortie->removeParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Vous avez été désinscrit•e de la sortie ' .  $sortie->getNom() . ' du ' . $sortie->getDateHeureDebut()->format('d/m/Y'));
        $from = $request->query->get('from');
        return $this->redirectToRoute($from ?? 'sortie_details', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/annuler', name: 'sortie_annuler', requirements: ['id'=>'\d+'], methods: ['POST'])]
    public function annuler(
        int $id,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
        Request $request,
    )
    {
        $participant = $participantRepository->find($this->getUser()->getId());
        $sortie = $sortieRepository->find($id);
        if (!$sortie) {
            $this->addFlash('error', 'Sortie introuvable');
            return $this->redirectToRoute('sortie_list');
        } elseif ($participant !== $sortie->getOrganisateur()) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler cette sortie');
            return $this->redirectToRoute('sortie_list');
        } elseif ($sortie->getEtat()->getLibelle() !== EtatEnum::Ouverte && $sortie->getEtat()->getLibelle() !== EtatEnum::Cloturee) {
            $this->addFlash('error', 'Il est trop tard pour annuler cette sortie');
            return $this->redirectToRoute('sortie_list');
        }

        $motif = $request->request->get('motif');
        if (!$motif) {
            $this->addFlash('danger', 'Le motif est requis.');
            return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
        }

        $etat = $etatRepository->findOneBy(['libelle' => EtatEnum::Annulee]);
        if ($etat) {
            $sortie->setEtat($etat);
        }
        $entityManager->persist($sortie);

        $annulation = new Annulation();
        $annulation->setRaison($motif);
        $annulation->setSortie($sortie);
        $entityManager->persist($annulation);

        $entityManager->flush();

        $this->addFlash('success', 'La sortie \'' . $sortie->getNom() . '\' est annulée');
        return $this->redirectToRoute('sortie_list');
    }
}
