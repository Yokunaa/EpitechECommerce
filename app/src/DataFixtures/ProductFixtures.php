<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function __construct(public UserRepository $ur)
    {
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 150; $i++) {
            $product = new Product();
            $product->setName('product ' . $i);
            $product->setPrice($faker->randomFloat(2, 10, 100));
            $product->setDescription($faker->paragraph());
            $product->setPhoto("https://via.placeholder.com/650");

            $user = $this->ur->find($faker->numberBetween(1, 20));
            $user->addProduct($product);
            $manager->persist($product);
        }

        $manager->flush();
    }
}