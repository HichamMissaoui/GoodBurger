<?php

namespace CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormControllerTest extends WebTestCase
{
    public function testUserform()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/userForm');
    }

}
