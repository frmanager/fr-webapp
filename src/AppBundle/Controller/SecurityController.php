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



  /**
   * @Route("/register", name="user_registration")
   */
  public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
  {
      // 1) build the form
      $user = new User();
      $form = $this->createForm(UserType::class, $user);

      // 2) handle the submit (will only happen on POST)
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

          // 3) Encode the password (you could also do this via Doctrine listener)
          $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
          $user->setPassword($password);

          // 4) save the User!
          $em = $this->getDoctrine()->getManager();
          $em->persist($user);
          $em->flush();

          // ... do any other work - like sending them an email, etc
          // maybe set a "flash" success message for the user

          return $this->redirectToRoute('replace_with_some_route');
      }

      return $this->render(
          'registration/register.html.twig',
          array('form' => $form->createView())
      );
  }


}
