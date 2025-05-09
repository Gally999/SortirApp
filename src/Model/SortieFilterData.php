<?php

namespace App\Model;

use App\Entity\Campus;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SortieFilterData
{
    public Campus $campus;

    #[Assert\Length(min: 2, minMessage: "La recherche doit contenir au moins {{ limit }} caractères")]
    public ?string $searchTerm = null;

    #[Assert\GreaterThanOrEqual("today", message: "La date de début doit être dans le futur.")]
    public ?DateTimeInterface $startDate = null;

    #[Assert\GreaterThanOrEqual("today", message: "La date de fin doit être dans le futur.")]
    #[Assert\Expression(
        "this.endDate === null || this.startDate === null || this.endDate >= this.startDate",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    public ?DateTimeInterface $endDate = null;

    public bool $isOrganisateur = false;
    public bool $isInscrit = false;
    public bool $isNotInscrit = false;
    public bool $showTerminees = false;
}