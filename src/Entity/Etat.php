<?php

namespace App\Entity;

use App\Entity\Enum\EtatEnum;
use App\Repository\EtatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    public const ETATS = ['Ouverte', 'Cloturee', 'En cours', 'Terminee', 'Annulee', 'Historisee'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Choice(choices: self::ETATS, message: 'Choisissez un état de sortie valide')]
    #[ORM\Column(length: 50, enumType: Enum\EtatEnum::class)]
    private ?string $libelle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }
}
