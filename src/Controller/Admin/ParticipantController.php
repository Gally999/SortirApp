<?php

namespace App\Controller\Admin;

use App\Form\Admin\ParticipantType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/participants', name: 'admin_participants')]
class ParticipantController extends AbstractController
{
    #[Route('/', name: '_list', methods:['GET'])]
    public function listParticipant(ParticipantRepository $participantsRepo, CampusRepository $campusRepo): Response
    {
        if($this->getUser()->isAdministrateur() == false){
            $this->addFlash('error', 'Vous devez etre admin pour voir la liste des participants');
            return $this->redirectToRoute('app_home');
        }
        //ne pas se prendre soi même
        $participants = $participantsRepo->findAllButId($this->getUser()->getId());

        $campus = $campusRepo->findAll();
        return $this->render(
            'admin/Participant/listeParticipants.html.twig',
            [
                'participants' => $participants,
                'campuses' => $campus,
            ]
        );
    }
    #[Route('/setAdmin/{pseudo}', name: '_setAdmin', methods:['POST'])]
    public function setAdmin($pseudo, ParticipantRepository $participantsRepo, EntityManagerInterface $entityManager, Request $request):Response
    {
        //csrf
        if (!$this->isCsrfTokenValid('set_admin_' . $pseudo, $request->get('_token'))) {
            $this->addFlash('error', 'Token invalide');
            return $this->redirectToRoute('admin_participants_list');
        }

        $participant = $participantsRepo->findParticipantByPseudo($pseudo);
        if(!$participant){
            $this->addFlash('error', 'Participant introuvable');
            return $this->redirectToRoute('admin_participants_list');
        }
        elseif($this->getUser()->getId() == $participant->getId()){
            return $this->redirectToRoute('admin_participants_list');
        }
        elseif($this->getUser()->isAdministrateur() == false){
            $this->addFlash('error', 'Vous devez etre admin pour modifier un participant');
            return $this->redirectToRoute('app_home');
        }elseif($participant->isAdministrateur() == true){
            $this->addFlash('error', 'Vous ne pouvez pas modifier un administrateur');
            return $this->redirectToRoute('admin_participants_list');
        }

        if($participant->isActif() == false){
            $participant->setActif(true);
        }
        $participant->setAdministrateur(!$participant->isAdministrateur());
        $entityManager->persist($participant);
        $entityManager->flush();
        if($participant->isAdministrateur()){
            $this->addFlash('success', $participant->getPseudo() .' est maintenant utilisateur');
        }
        else{

            $this->addFlash('success', $participant->getPseudo() .' n\'est plus administrateur');
        }
        return $this->redirectToRoute('admin_participants_list');
    }

    #[Route('/setActif/{pseudo}', name: '_setActif', methods:['POST'])]
    public function setActif($pseudo, ParticipantRepository $participantsRepo, EntityManagerInterface $entityManager, Request $request):Response
    {
        //csrf
        if (!$this->isCsrfTokenValid('set_actif_' . $pseudo, $request->get('_token'))) {
            $this->addFlash('error', 'Token invalide');
            return $this->redirectToRoute('admin_participants_list');
        }

        $participant = $participantsRepo->findParticipantByPseudo($pseudo);

        if(!$participant){
            $this->addFlash('error', 'Participant introuvable');
            return $this->redirectToRoute('admin_participants_list');
        }
        elseif($this->getUser()->getId() == $participant->getId()){
            return $this->redirectToRoute('admin_participants_list');
        }
        elseif($this->getUser()->isAdministrateur() == false){
            $this->addFlash('error', 'Vous devez etre admin pour modifier un participant');
            return $this->redirectToRoute('app_home');
        }elseif($participant->isAdministrateur() == true){
            $this->addFlash('error', 'Vous ne pouvez pas modifier un administrateur');
            return $this->redirectToRoute('admin_participants_list');
        }

        $participant->setActif(!$participant->isActif());
        $entityManager->persist($participant);
        $entityManager->flush();
        if($participant->isActif()){
            $this->addFlash('success', $participant->getPseudo() .' est maintenant actif');
        }
        else{
            $this->addFlash('success', $participant->getPseudo() .' n\'est plus actif, il ne pourra plus se connecter');
        }
        return $this->redirectToRoute('admin_participants_list');
    }

    #[Route('/modify/{pseudo}', name: '_modify', methods:['GET', 'POST'])]
    public function modifyParticipant($pseudo,ParticipantRepository $participantsRepo, Request $request, EntityManagerInterface $entityManager):Response 
    {
        $participant = $participantsRepo->findParticipantByPseudo($pseudo);
        if(!$participant){
            $this->addFlash('error', 'Participant introuvable');
            return $this->redirectToRoute('admin_participants_list');
        }
        elseif($this->getUser()->getId() == $participant->getId()){
            return $this->redirectToRoute('app_profile');
        }
        elseif($this->getUser()->isAdministrateur() == false){
            $this->addFlash('error', 'Vous devez etre admin pour modifier un participant');
            return $this->redirectToRoute('app_home');
        }elseif($participant->isAdministrateur() == true){
            $this->addFlash('error', 'Vous ne pouvez pas modifier un administrateur');
            return $this->redirectToRoute('admin_participants_list');
        }

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash('success', 'Participant modifié avec succès');
            return $this->redirectToRoute('admin_participants_list');
        }

        return $this->render(
            'admin/Participant/modifyParticipant.html.twig',
            [
                'form' => $form->createView(),
                'participant' => $participant,
            ]
        );
    }

}
