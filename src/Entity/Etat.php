<?php

namespace App\Entity;

use App\Enum\EtatEnum;
use App\Repository\EtatRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (!in_array($this->libelle?->value, EtatEnum::values(), true)) {
            $context->buildViolation('Choisissez un état de sortie valide')
                ->atPath('libelle')
                ->addViolation();
        }
    }
    #[ORM\Column(length: 50, enumType: EtatEnum::class)]
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
