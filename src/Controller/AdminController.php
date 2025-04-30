<?php

namespace App\Controller;

use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function adminIndex(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/participants', name: 'participants')]
    public function listParticipant(ParticipantRepository $participantsRepo, CampusRepository $campusRepo): Response
    {
        $participants = $participantsRepo->findAll();
        $campus = $campusRepo->findAll();
        return $this->render(
            'admin/listeParticipants.html.twig',
            [
                'participants' => $participants,
                'campuses' => $campus,
            ]
        );
    }
}
