<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Grade;
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
  public function indexAction(LoggerInterface $logger)
  {
      return new JsonResponse(array('name' => 'bob'));
  }

}
