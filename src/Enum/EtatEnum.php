<?php

namespace App\Enum;

enum EtatEnum: string
{
    use EnumToArray;

    case EnCreation = 'En creation';
    case Ouverte = 'Ouverte';
    case Cloturee = 'Cloturee';
    case EnCours = 'En cours';
    case Terminee = 'Terminee';
    case Annulee = 'Annulee';
    case Historisee = 'Historisee';

    public function label(): string
    {
        return match ($this) {
            self::EnCreation => 'En création',
            self::Ouverte => 'Ouverte',
            self::Cloturee => 'Clôturée',
            self::EnCours => 'En cours',
            self::Terminee => 'Terminée',
            self::Annulee => 'Annulée',
            self::Historisee => 'Historisée',
        };
    }
}