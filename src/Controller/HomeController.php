<?php

namespace App\Controller;

use App\Util\SortieClotureManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SortieClotureManager $sortieManager): Response
    {
        $nbCloturees = $sortieManager->cloturerSortiesArriveesALaDateLimite();
        $nbCloturees <= 0 ?
            $this->addFlash('info', 'Pas de sortie à clôturer aujourd\'hui'):
            $this->addFlash('success', "$nbCloturees sorties clôturées");
        return $this->render('index.html.twig');
    }
}
