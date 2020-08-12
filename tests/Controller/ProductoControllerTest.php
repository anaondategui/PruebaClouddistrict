<?php

namespace App\Tests\Controller;

use App\Controller\ProductoController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductoControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/producto');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
