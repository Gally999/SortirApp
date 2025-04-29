<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParticipantController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, ParticipantRepository $repo): Response
    {
        $participant = $repo->find($this->getUser()->getId());


        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/profile/{pseudo}', name: 'app_profile_show')]
    public function showProfile($pseudo, ParticipantRepository $repo): Response
    {
        if ($this->getUser()->getPseudo() == $pseudo) {
            return $this->redirectToRoute('app_profile');
        }
        $participant = $repo->findParticipantByPseudo($pseudo);
        if (!$participant) {
            $this->addFlash('error', 'Profil introuvable');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('profile/showProfile.html.twig', [
            'participant' => $participant,
        ]);
    }
}
