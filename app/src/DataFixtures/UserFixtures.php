<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(public UserPasswordHasherInterface $ph)
    {
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setLogin($faker->userName());
            $user->setFirstname($faker->firstName());
            $user->setLastname($faker->lastName());
            $user->setEmail($faker->safeEmail());
            $hashedPassword = $this->ph->hashPassword(
                $user,
                'password'
            );
            $user->setPassword($hashedPassword);

            $cart = new Cart();
            $user->setCart($cart);

            $manager->persist($user);
            $manager->persist($cart);
        }

        $manager->flush();
    }
}