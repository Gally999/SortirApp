<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        // Création admin
        $admin = new Participant();
        $admin->setNom('admin');
        $admin->setPrenom('admin');
        $admin->setEmail('a@a.com');
        $admin->setPseudo('admin');
        $admin->setCampus($this->getReference('campus1', Campus::class));

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin');
        $admin->setPassword($hashedPassword);

        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setAdministrateur(true);
        $admin->setActif(true);

        $this->addReference('admin', $admin);

        $manager->persist($admin);

        for ($i = 0; $i < 10; $i++) {
            $participant = new Participant();
            $participant->setNom($faker->firstName());
            $participant->setPrenom($faker->lastName());
            $participant->setEmail("user$i@test.com");
            $participant->setPseudo("user$i");
            $participant->setCampus($this->getReference('campus' . $faker->numberBetween(0, 4), Campus::class));

            $hashedPassword = $this->passwordHasher->hashPassword($participant, '123456');
            $participant->setPassword($hashedPassword);

            $participant->setRoles(['ROLE_USER']);
            $participant->setAdministrateur(false);
            $participant->setActif(true);

            $manager->persist($participant);
            $this->addReference('participant' . $i, $participant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CampusFixtures::class];
    }
}
