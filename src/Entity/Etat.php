<?php

namespace App\Entity;

use App\Enum\EtatEnum;
use App\Repository\EtatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    public const ETATS = ['Ouverte', 'Cloturee', 'En Cours', 'Terminee', 'Annulee', 'Historisee'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Choice(choices: self::ETATS, message: 'Choisissez un état de sortie valide')]
    #[ORM\Column(length: 50)]
    private ?EtatEnum $libelle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?EtatEnum
    {
        return $this->libelle;
    }
    public function getLibelleString(): ?string
    {
        return $this->libelle?->value;
    }


    public function setLibelle(EtatEnum $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }
    public function __toString(): string
    {
        return $this->libelle?->value ?? '';
    }
}
