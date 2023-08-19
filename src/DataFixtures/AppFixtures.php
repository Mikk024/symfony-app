<?php

namespace App\DataFixtures;

use App\Entity\Listing;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('Password');
        $manager->persist($user);

        for ($i = 1; $i < 20; $i++) {
            $listing = new Listing();
            $listing->setName('Listing' . $i);
            $listing->setEmail($faker->email());
            $listing->setDescription('Cupidatat et mollit incididunt tempor consectetur elit qui non quis ipsum.');
            $listing->setLocation($faker->address());
            $listing->setUser($user);
            $listing->setPhone($faker->randomNumber(9, true));
            $listing->setPremium($faker->boolean());
            $listing->setSalary($faker->numberBetween(10, 100));
            $listing->setFilePath($faker->filePath() . '.jpg');
            $manager->persist($listing);
        }

        $manager->flush();
    }
}
