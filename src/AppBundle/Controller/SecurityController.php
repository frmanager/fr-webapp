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

      if (!empty($request->attributes->get('_route_params'))) {
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)) {
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
      $em = $this->getDoctrine()->getManager();
      $logger = $this->get('logger');

      $authUtils = $this->get('security.authentication_utils');
      $session = $request->getSession();

      $campaign = null;

      if (!empty($request->attributes->get('_route_params'))) {
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)) {
              $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($routeParams['campaignUrl']);
          }
      }

      if (count($campaign) == 0){
        $this->get('session')->getFlashBag()->add('warning', 'Hi, we could not find that campaign');
        return $this->redirectToRoute('homepage', array('action' => 'list_campaigns'));
      }

      $user = $this->get('security.token_storage')->getToken()->getUser();
      //CODE TO CHECK TO SEE IF A USERS TEAM EXISTS, IF NOT, THEY NEED TO CREATE ONE
      $team = $em->getRepository('AppBundle:Team')->findOneBy(array('user' => $user, 'campaign' => $campaign));
      if(is_null($team)){
        $this->get('session')->getFlashBag()->add('warning', 'Hi, it looks like you have not completed your team registration yet');
        return $this->redirectToRoute('register_team', array('campaignUrl'=>$campaign->getUrl()));
      }

      return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));

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

      if (!empty($request->attributes->get('_route_params'))) {
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)) {
              $em = $this->getDoctrine()->getManager();
              $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($routeParams['campaignUrl']);
          }
      }


      return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));
  }
}
