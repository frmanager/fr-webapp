<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * Security controller.
 *
 * @Route("/{campaignUrl}")
 */
class SecurityController extends Controller
{


  /**
   * @Route("/login", name="login")
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
   * @Route("/loginRedirect", name="loginRedirect")
   */
  public function loginRedirectAction(Request $request)
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

      if(count($campaign) == 0){
        return $this->redirectToRoute('homepage', array('action' => 'list_campaigns'));
      }else{
        return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));
      }
  }


  /**
   * @Route("/logout", name="logout")
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


      return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));
  }

}
