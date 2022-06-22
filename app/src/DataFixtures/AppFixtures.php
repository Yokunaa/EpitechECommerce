<?php

namespace App\DataFixtures;

use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        public UserRepository $ur,
        public UserPasswordHasherInterface $ph
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $uf = new UserFixtures($this->ph);
        $pf = new ProductFixtures($this->ur);
        $uf->load($manager);
        $pf->load($manager);

        $manager->flush();
    }
}