<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $villeNames = ['Saint-Herblain', 'La Roche sur Yon', 'Chartres de Bretagne', 'Niort', 'Quimper', 'Nantes', 'Rennes'];
        $codePostaux = ['44100', '85000', '35131', '72000', '29000', '44000', '35000'];

        for ($i = 0; $i < count($villeNames); $i++) {
            $ville = new Ville();
            $ville->setNom($villeNames[$i]);
            $ville->setCodePostal($codePostaux[$i]);

            $manager->persist($ville);
            $this->addReference("ville$i", $ville);
        }

        $manager->flush();
    }
}
