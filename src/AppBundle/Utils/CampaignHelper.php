<?php

// src/AppBundle/Utils/CampaignHelper.php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManager;
use DateTime;
use Monolog\Logger;
use AppBundle\Entity\Campaign;
use AppBundle\Entity\User;

class CampaignHelper
{

  protected $em;
  protected $logger;

  public function __construct(EntityManager $em, Logger $logger)
  {
      $this->em = $em;
      $this->logger = $logger;
  }

  /**
  *
  * campaignPermissionsCheck takes the campaign that was requested and verifies user has access to it
  *
  * Access is verified by looking at the CampaignUser entity and verifying a record exists
  * for that campaign and user combination
  *
  * @param Campaign $campaign
  *
  * @return boolean
  *
  */
  public function campaignPermissionsCheck(User $user, Campaign $campaign){
    //CODE TO PROTECT CONTROLLER FROM USERS WHO ARE NOT IN CAMPAIGNUSER TABLE
    //TODO: ADD CODE TO ALLOW ADMINS TO ACCESS
    $query = $this->em->createQuery('SELECT IDENTITY(cu.campaign) FROM AppBundle:CampaignUser cu where cu.user=?1');
    $query->setParameter(1, $user);
    $results = array_map('current', $query->getScalarResult());
    if(!in_array($campaign->getId(), $results)){
      return false;
    }
    return true;
  }

}
