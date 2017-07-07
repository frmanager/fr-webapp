<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Security controller.
 *
 * @Route("/{campaignUrl}/")
 */
class SecurityController extends Controller
{


  /**
   * @Route("login", name="login")
   */
  public function loginAction(Request $request)
  {
      $authUtils = $this->get('security.authentication_utils');

      $logger = $this->get('logger');
      /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
      $session = $request->getSession();
      $campaign = null;

      if(!empty($request->attributes->get('_route_params'))){
        $routeParams = $request->attributes->get('_route_params');
        if (array_key_exists('campaignUrl', $routeParams)){
          $em = $this->getDoctrine()->getManager();
          $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($routeParams['campaignUrl']);
        }
      }

      // get the login error if there is one
      $error = $authUtils->getLastAuthenticationError();

      // last username entered by the user
      $lastUsername = $authUtils->getLastUsername();

      return $this->render('security/login.html.twig', array(
          'last_username' => $lastUsername,
          'error'         => $error,
          'campaign'      => $campaign
      ));
  }

  /**
   * @Route("logout", name="logout")
   */
  public function logoutAction(Request $request)
  {
      $authUtils = $this->get('security.authentication_utils');

      $logger = $this->get('logger');
      /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
      $session = $request->getSession();
      $campaign = null;

      if(!empty($request->attributes->get('_route_params'))){
        $routeParams = $request->attributes->get('_route_params');
        if (array_key_exists('campaignUrl', $routeParams)){
          $em = $this->getDoctrine()->getManager();
          $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($routeParams['campaignUrl']);
        }
      }


      return $this->render('security/login.html.twig', array(
          'last_username' => $lastUsername,
          'error'         => $error,
          'campaign'      => $campaign
      ));
  }

}
