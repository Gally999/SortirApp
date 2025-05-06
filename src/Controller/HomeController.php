<?php

namespace App\Controller;

use App\Util\SortieArchiveManager;
use App\Util\SortieClotureManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        SortieClotureManager $sortieClotureManager,
        SortieArchiveManager $sortieArchiveManager,
    ): Response
    {
        $nbCloturees = $sortieClotureManager->cloturerSortiesArriveesALaDateLimite();
        $nbCloturees <= 0 ?
            $this->addFlash('info', 'Pas de sortie à clôturer aujourd\'hui'):
            $this->addFlash('success', $nbCloturees == 1 ? "$nbCloturees sortie clôturée" : "$nbCloturees sorties clôturées");

        $nbArchivees = $sortieArchiveManager->archiverSortiesAPlusUnMois();
        $nbArchivees <= 0 ?
            $this->addFlash('info', 'Pas de sortie à archiver aujourd\'hui'):
            $this->addFlash('success', $nbArchivees == 1 ? "$nbArchivees sortie archivée" : "$nbArchivees sorties archivées");
        return $this->render('index.html.twig');
    }
}
