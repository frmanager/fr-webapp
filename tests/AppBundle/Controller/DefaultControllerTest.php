<?php

namespace Tests\AppBundle\Controller;
use AppBundle\Utils\QueryHelper;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * @coversDefaultClass \AppBundle\Controller\DefaultController
 */
class DefaultControllerTest extends WebTestCase
{

    /**
    * @covers ::indexAction
    */
    public function testIndexAction()
    {
        $client = static::createClient(array(),array('HTTPS' => true));

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
