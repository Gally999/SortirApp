<?php

namespace App\Enum;

enum EtatEnum: string
{
    use EnumToArray;

    case Ouverte = 'Ouverte';
    case Cloturee = 'Cloturee';
    case EnCours = 'En Cours';
    case Terminee = 'Terminee';
    case Annulee = 'Annulee';
    case Historisee = 'Historisee';
}