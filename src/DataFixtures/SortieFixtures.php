<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 30; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence($nbWords = 3, $variableNbWords = true));
            $sortie->setCampus($this->getReference('campus' . $faker->numberBetween(0, 4), Campus::class));

            $dateTimeInFuture = $faker->dateTimeBetween($max = 'now', '+2 months', $timezone = 'Europe/Paris');
            $sortie->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateTimeInFuture));

            $sortie->setDuree($faker->numberBetween(30, 280));

            $dateLimiteInscription = $faker->dateTimeInInterval($max = $dateTimeInFuture, '-7 days');
            $sortie->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimiteInscription));

            $sortie->setNbInscriptionMax($faker->numberBetween(2, 20));
            $sortie->setEtat($this->getReference('etat' . $faker->numberBetween(0, 6), Etat::class));

            $sortie->setLieu($this->getReference('lieu' . $faker->numberBetween(0, 19), Lieu::class));
            $sortie->setOrganisateur($this->getReference('admin', Participant::class));
            $sortie->setInfosSortie($faker->paragraph($nbSentences = 2, $variableNbSentences = true));

            $manager->persist($sortie);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EtatFixtures::class,
            CampusFixtures::class,
            LieuFixtures::class,
            ParticipantFixtures::class
        ];
    }
}
