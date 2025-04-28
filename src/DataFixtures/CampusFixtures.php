<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campusNames = ['Saint-Herblain', 'La Roche sur Yon', 'Chartres de Bretagne', 'Niort', 'Quimper'];
            for ($i = 0; $i < count($campusNames); $i++) {
                $campus = new Campus();
                $campus->setNom($campusNames[$i]);
                $manager->persist($campus);
                $this->addReference("campus$i", $campus);
            }

        $manager->flush();
    }
}
