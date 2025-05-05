<?php

namespace App\Controller\Admin;

use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/participants', name: 'admin_participants')]
class ParticipantController extends AbstractController
{
    #[Route('/', name: '_list')]
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
