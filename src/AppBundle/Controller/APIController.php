<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Grade;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * API controller.
 *
 */
class APIController extends Controller
{

  /**
   * @Route("/test")
   */
  public function indexAction()
  {
      return new JsonResponse(array('name' => 'bob'));
  }

}
