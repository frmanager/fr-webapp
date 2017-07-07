<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Campaignawardtype;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $campaignawardtype = new Campaignawardtype();
        $campaignawardtype->setDisplayName('admin');
        $campaignawardtype->setValue('test');
        $campaignawardtype->setDescription('test');

        $manager->persist($campaignawardtype);
        $manager->flush();
    }
}
