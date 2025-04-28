<?php

namespace App\Entity\Enum;

enum EtatEnum
{
    case Ouverte;
    case Cloturee;
    case EnCours;
    case Terminee;
    case Annulee;
    case Historisee;
}