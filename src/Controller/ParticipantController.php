<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ParticipantController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, ParticipantRepository $repo, SluggerInterface $slugger): Response
    {
        $participant = $repo->find($this->getUser()->getId());

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $profilePictureFile = $form->get('profilePicture')->getData();

            if ($profilePictureFile) {
                $originalFilename = pathinfo($profilePictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePictureFile->guessExtension();

                // Déplacez le fichier dans le répertoire où vous souhaitez stocker les images
                try {
                    $profilePictureFile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
                    $participant->setProfilePicture($newFilename);
                } catch (FileException $e) {
                    //renvoyer une erreur au formulaire


                }
            }




            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'form' => $form,
            'participant' => $participant
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
