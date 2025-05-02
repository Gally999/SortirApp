<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
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
            $user = new Participant();
            $user->setNom($faker->lastName());
            $user->setPrenom($faker->firstName());
            $user->setEmail("user$i@test.com");
            $user->setPseudo("user$i");
            $user->setCampus($this->getReference('campus' . $faker->numberBetween(0, 4), Campus::class));

            $hashedPassword = $this->passwordHasher->hashPassword($user, '123456');
            $user->setPassword($hashedPassword);

            $user->setRoles(['ROLE_USER']);
            $user->setAdministrateur(false);
            $user->setActif(true);

            $manager->persist($user);
            $this->addReference('user' . $i, $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CampusFixtures::class];
    }
}
