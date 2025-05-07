<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $counter = 0;

        for ($i = 0; $i < 40; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence($nbWords = 3, $variableNbWords = true));
            $sortie->setCampus($this->getReference('campus' . $faker->numberBetween(0, 4), Campus::class));

            $dateTimeInFuture = $faker->dateTimeBetween($max = '-15 days', '+2 months', $timezone = 'Europe/Paris');
            $sortie->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateTimeInFuture));

            $sortie->setDuree($faker->randomDigitNot(0) * 30);

            $dateLimiteInscription = $faker->dateTimeInInterval($max = $dateTimeInFuture, '-15 days');
            $sortie->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimiteInscription));

            $sortie->setNbInscriptionMax($faker->numberBetween(3, 20));

            if ($sortie->getDateHeureDebut() < new \DateTimeImmutable()) {
                $etat = 'Terminee';
            } elseif ($sortie->getDateLimiteInscription() < new \DateTimeImmutable()) {
                if ($faker->boolean(10)) {
                    $etat = 'Annulee';
                } else {
                    $etat = 'Cloturee';
                }
            } else {
                if ($faker->boolean(10)) {
                    $etat = 'En creation';
                } else {
                    $etat = 'Ouverte';
                }
            }
            if ($faker->boolean(10)) {
                $etat = 'Annulee';
            }
            $sortie->setEtat($this->getReference('etat' . $etat, Etat::class));

            $sortie->setLieu($this->getReference('lieu' . $faker->numberBetween(0, 19), Lieu::class));
            $sortie->setOrganisateur($this->getReference('user' . $faker->numberBetween(0, 9), Participant::class));
            $sortie->setInfosSortie($faker->paragraph($nbSentences = 2, $variableNbSentences = true));

            for ($j = 0; $j < 3; $j++) {
                $sortie->addParticipant($this->getReference('user' . $faker->numberBetween(0, 9), Participant::class));
            }

            if ($sortie->getEtat()->getLibelle() == EtatEnum::Annulee) {
                $this->setReference('sortieAnnulee' . $counter, $sortie);
                $counter++;
            }

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
