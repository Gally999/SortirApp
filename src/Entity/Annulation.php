<?php

namespace App\Entity;

use App\Repository\AnnulationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnnulationRepository::class)]
class Annulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sortie $sortie = null;

    #[ORM\Column(length: 250)]
    private ?string $raison = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateAnnulation = null;

    public function __construct()
    {
        $this->dateAnnulation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(Sortie $sortie): static
    {
        $this->sortie = $sortie;

        return $this;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): static
    {
        $this->raison = $raison;

        return $this;
    }

    public function getDateAnnulation(): ?\DateTimeImmutable
    {
        return $this->dateAnnulation;
    }

    public function setDateAnnulation(\DateTimeImmutable $dateAnnulation): static
    {
        $this->dateAnnulation = $dateAnnulation;

        return $this;
    }
}
