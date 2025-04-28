<?php

namespace App\Entity\Enum;

enum EtatEnum: string
{
    case Ouverte = 'Ouverte';
    case Cloturee = 'Cloturee';
    case EnCours = 'En Cours';
    case Terminee = 'Terminee';
    case Annulee = 'Annulee';
    case Historisee = 'Historisee';
}