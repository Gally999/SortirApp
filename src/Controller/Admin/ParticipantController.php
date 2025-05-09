<?php

namespace App\Controller\Admin;

use App\Form\Admin\ParticipantType;
use App\Form\Admin\ParticipantTypeCreate;
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

    /**
     * Handles the modification of a participant's details.
     *
     * This method allows an admin to modify the details of a participant
     * identified by their pseudo. It retrieves the participant, checks for 
     * various authorization conditions, and handles the form submission 
     * for updating the participant's information.
     *
     * @param string $pseudo The pseudo of the participant to modify.
     * @param ParticipantRepository $participantsRepo The repository to find participants.
     * @param Request $request The HTTP request object.
     * @param EntityManagerInterface $entityManager The entity manager to handle database operations.
     *
     * @return Response The HTTP response, either rendering the modification form or redirecting.
     * 
     * Injection de dépendance Repository grâce au Service Container de Symfony 
     */
    #[Route('/modify/{pseudo}', name: '_modify', methods:['GET', 'POST'])]
    public function modifyParticipant($pseudo,ParticipantRepository $participantsRepo, EntityManagerInterface $entityManager, Request $request):Response 
    {
        //récupération du participant avec le repository et une fonction custom
        $participant = $participantsRepo->findParticipantByPseudo($pseudo);

        // S'il n'éxiste pas, on redirige vers la liste des participants avec une erreur en notification flash
        if(!$participant){
            $this->addFlash('error', 'Participant introuvable');
            return $this->redirectToRoute('admin_participants_list');
        }
        // Si on est pas l'auteur du participant, on redirige vers la liste des participants avec une erreur en notification flash
        elseif($this->getUser()->getId() == $participant->getId()){
            return $this->redirectToRoute('app_profile');
        }
        // Si on n'est pas admin, on redirige vers la liste des participants avec une erreur en notification flash
        elseif($this->getUser()->isAdministrateur() == false){
            $this->addFlash('error', 'Vous devez etre admin pour modifier un participant');
            return $this->redirectToRoute('app_home');
        }
        // Si le participant est admin, on redirige vers la liste des participants avec une erreur en notification flash
        elseif($participant->isAdministrateur() == true){
            $this->addFlash('error', 'Vous ne pouvez pas modifier un administrateur');
            return $this->redirectToRoute('admin_participants_list');
        }
        // Création du form grace au FormType et on y passe le participant pour hydrater le formulaire
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // mise en file du participant
            $entityManager->persist($participant);
            // pousser dans la base de données
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

    #[Route('/create', name: '_create', methods:['GET', 'POST'])]
    public function createParticipant(Request $request, EntityManagerInterface $entityManager): Response
    {
        if($this->getUser()->isAdministrateur() == false){
            $this->addFlash('error', 'Vous devez etre admin pour ajouter un participant');
            return $this->redirectToRoute('app_home');
        }
        $participant = new \App\Entity\Participant();
        $form = $this->createForm(ParticipantTypeCreate::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setPassword(password_hash($participant->getPassword(), PASSWORD_DEFAULT));
            if($participant->isAdministrateur()){
                $participant->setRoles(['ROLE_ADMIN']);
            }
            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash('success', 'Participant ajouté avec succès');
            return $this->redirectToRoute('admin_participants_list');
        }

        return $this->render(
            'admin/Participant/createParticipant.html.twig',
            [
                'form' => $form->createView(),
                'participant' => $participant
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
        $participant->setRoles([$participant->isAdministrateur() ? 'ROLE_ADMIN' : 'ROLE_USER']);
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

    

}
