<?php

namespace App\Model;

use App\Entity\Campus;
use DateTimeInterface;

class SortieSearchData
{
    public Campus $campus;
    public ?string $searchTerm = null;

    public ?DateTimeInterface $startDate = null;
    public ?DateTimeInterface $endDate = null;

    public bool $isOrganisateur = false;
    public bool $isInscrit = false;
    public bool $isNotInscrit = false;
    public bool $showTerminees = false;
}