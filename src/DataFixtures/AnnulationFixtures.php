<?php

namespace App\DataFixtures;

use App\Entity\Annulation;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AnnulationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $i = 0;

        while ($this->hasReference('sortieAnnulee' . $i, Sortie::class)) {
            $annulation = new Annulation();
            $annulation->setRaison($faker->sentence(5, true));

            $dateAnnulation = $faker->dateTimeBetween('-10 days', 'now');
            $annulation->setDateAnnulation(\DateTimeImmutable::createFromMutable($dateAnnulation));
            $annulation->setSortie($this->getReference('sortieAnnulee' . $i, Sortie::class));

            $manager->persist($annulation);
            $i++;
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SortieFixtures::class];
    }
}
