<?php

namespace App\Enum;

enum EtatEnum: string
{
    use EnumToArray;

    case EnCreation = 'En creation';
    case Ouverte = 'Ouverte';
    case Cloturee = 'Cloturee';
    case EnCours = 'En Cours'; // TODO rename en minuscule avec les fixtures
    case Terminee = 'Terminee';
    case Annulee = 'Annulee';
    case Historisee = 'Historisee';
}