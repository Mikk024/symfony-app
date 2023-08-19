<?php 

namespace App\Tests;

use App\Entity\Listing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ListingTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testItWorks()
    {
        $this->assertTrue(True);
    }
}