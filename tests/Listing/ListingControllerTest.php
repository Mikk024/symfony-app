<?php

namespace App\Tests\Listing;

use App\Entity\Listing;
use App\Entity\User;
use App\Repository\UserRepository;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListingControllerTest extends WebTestCase
{
    public function testCreateListing(): void
    {
        $client = static::createClient();

        $user = new User();
        $user->setEmail('Test@example.com');
        $user->setPassword('passwrod');
        $user->setRoles(['ROLE_USER']);

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        
        $client->loginUser($user);
        $crawler = $client->request('GET', '/listing/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Submit')->form();
        $form['listing_form[name]'] = 'Test Name';
        $form['listing_form[description]'] = 'Test Description';
        $form['listing_form[location]'] = 'Test Location';
        $form['listing_form[email]'] = 'Test@Email.com';
        $form['listing_form[salary]'] = '100';
        $form['listing_form[phone]'] = '423132321';
        $client->submit($form);

        $listingRepository = $entityManager->getRepository(Listing::class);

        $listing = $listingRepository->findOneBy(['name' => 'Test Name']);
        $this->assertNotNull($listing);

        $this->assertResponseRedirects('/');
    }

    public function testIndexListing()
    {
        $client = static::createClient();
        $client->request('GET', '/listing');

        $this->assertResponseIsSuccessful();
    }

    public function testManageListings()
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);

        $user = $userRepository->findOneBy([]);

        $client->loginUser($user);

        $crawler = $client->request('GET', '/listing/manage');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('a.text-decoration-none', 'Listing1');


    }

    public function testShowListing()
    {
        $client = static::createClient();

        $client->request('GET', '/listing/28');

        $this->assertResponseIsSuccessful();
    }
}
