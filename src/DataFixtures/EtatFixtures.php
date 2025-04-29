<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Enum\EtatEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etats = EtatEnum::cases();

        for ($i = 0; $i < count($etats); $i++) {
            $etat = new Etat();
            $etat->setLibelle($etats[$i]);

            $manager->persist($etat);
            $this->addReference("etat$i", $etat);
        }

        $manager->flush();
    }
}