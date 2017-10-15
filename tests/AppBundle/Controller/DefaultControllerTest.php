<?php

namespace Tests\AppBundle\Controller;
use AppBundle\Utils\QueryHelper;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
  * @covers DefaultController::indexAction
  */
class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient(array(),array('HTTPS' => true));

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
