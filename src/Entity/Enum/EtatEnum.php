<?php

namespace App\Entity\Enum;

enum EtatEnum: string
{
    case Ouverte = 'OU';
    case Cloturee = 'CL';
    case EnCours = 'EC';
    case Terminee = 'TE';
    case Annulee = 'AN';
    case Historisee = 'HI';
}