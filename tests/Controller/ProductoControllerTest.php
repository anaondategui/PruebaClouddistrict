<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductoControllerTest extends WebTestCase
{

    public function test_productNombre_sinBusca_return500()
    {
        $client = static::createClient();
        $client->request('GET', '/productoNombre');

        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        $this->assertEquals("No sabemos que buscar, por favor pasa por POST un nombre a buscar con key \'busca\'", $client->getResponse()->getContent());
    }

    public function test_productNombre_conBusca_return200()
    {
        $client = static::createClient();

        $client->request('GET', '/productoNombre?busca=Portatil');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}
