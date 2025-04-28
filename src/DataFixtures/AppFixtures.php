<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new Participant();
        $user->setNom('admin');
        $user->setPrenom('admin');
        $user->setEmail('a@a.com');
        $user->setPseudo('admin');

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_ADMIN']);
        $user->setAdministrateur(true);
        $user->setActif(true);

        $manager->persist($user);
        $manager->flush();
    }
}
