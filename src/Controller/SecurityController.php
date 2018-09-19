<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
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
  public function loginAction(Request $request, LoggerInterface $logger)
  {
      $authUtils = $this->get('security.authentication_utils');

      
      /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
      $session = $request->getSession();

      $campaign = null;

      if (!empty($request->attributes->get('_route_params'))) {
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)) {
              $em = $this->getDoctrine()->getManager();
              $campaign = $em->getRepository('App:Campaign')->findOneByUrl($routeParams['campaignUrl']);
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
  public function loginRedirectAction(Request $request, LoggerInterface $logger)
  {
      $em = $this->getDoctrine()->getManager();
      

      $authUtils = $this->get('security.authentication_utils');
      $session = $request->getSession();

      if (!empty($request->attributes->get('_route_params'))) {
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)) {
              $campaign = $em->getRepository('App:Campaign')->findOneByUrl($routeParams['campaignUrl']);
          }
      }

      if (count($campaign) == 0){
        $this->get('session')->getFlashBag()->add('warning', 'Hi, we could not find that campaign');
        return $this->redirectToRoute('homepage', array('action' => 'list_campaigns'));
      }

      $logger->debug("Checking to see if user is confirmed");
      $user = $this->get('security.token_storage')->getToken()->getUser();
      //CODE TO CHECK TO SEE IF A USERS TEAM EXISTS, IF NOT, THEY NEED TO CREATE ONE
      if($user->getUserStatus()->getName() == "Confirmed"){
        $logger->debug("User is confirmed, checking to see if they have already registered their team");
        $team = $em->getRepository('App:Team')->findOneBy(array('user' => $user, 'campaign' => $campaign));
        if(is_null($team)){
          $logger->debug("Team is not registered, forwarding them to the correct page");
          $this->get('session')->getFlashBag()->add('warning', 'Hi, it looks like you have not completed your team registration yet');
          return $this->redirectToRoute('register_team_select', array('campaignUrl' => $campaign->getUrl()));
        }
      }else{
        $logger->debug("User is not fully registered, sending to confirm_email");
        $this->get('session')->getFlashBag()->add('warning', 'Hi, it looks like you have not confirmed your email yet.');
        return $this->redirectToRoute('confirm_email', array('campaignUrl' => $campaign->getUrl()));
      }

      return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));
  }


  /**
   * @Route("/logout", name="logout")
   */
  public function logoutAction(Request $request, LoggerInterface $logger)
  {
      $authUtils = $this->get('security.authentication_utils');

      
      /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
      $session = $request->getSession();
      $campaign = null;

      if (!empty($request->attributes->get('_route_params'))) {
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)) {
              $em = $this->getDoctrine()->getManager();
              $campaign = $em->getRepository('App:Campaign')->findOneByUrl($routeParams['campaignUrl']);
          }
      }


      return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));
  }



  /**
   * Allows user to reset password
   *
   * @Route("/password_reset_form", name="password_reset_form")
   *
   */
    public function passwordResetFormAction(Request $request, $campaignUrl, LoggerInterface $logger)
    {

        
        $logger->debug("Entering RegistrationController->passwordResetAction");
        $em = $this->getDoctrine()->getManager();

        $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);

        if ($request->isMethod('POST')) {
            $fail = false;
            $params = $request->request->all();

            if(empty($params['user']['email'])){
              $this->addFlash('warning','You must entter an email');
              $fail = true;
            }

            if(!$fail){
              $user = $em->getRepository('App:User')->findOneByEmail($params['user']['email']);
              if(empty($user)){
                $this->addFlash('warning','We are sorry, we could not find this account');
                $fail = true;
              }else{
                $user->setPasswordResetCode(strtoupper(md5(uniqid(rand(), true))));
                $user->setPasswordResetCodeTimestamp(new \DateTime());
                $em->persist($user);
                $em->flush();
              }
            }

            if(!$fail){

              //Send Email
              $message = (new \Swift_Message("FR Manager Password Reset Request"))
                ->setFrom('funrun@lrespto.org') //TODO: Change this to parameter for support email
                ->setTo($user->getEmail())
                ->setContentType("text/html")
                ->setBody(
                    $this->renderView('email/password_resetting.email.twig', array('campaign' => $campaign, 'user' => $user))
                );

              $this->get('mailer')->send($message);

              $this->get('session')->getFlashBag()->add('info', 'Please check your inbox for more information on changing your password');
              return $this->redirectToRoute('login', array('campaignUrl' => $campaign->getUrl()));
            }

        }

        return $this->render('security/password.reset.form.html.twig', array(
          'campaign' => $campaign,
        ));


    }


    /**
     * Allows user to reset password
     *
     * @Route("/password_reset", name="password_reset")
     *
     */
      public function passwordResetAction(Request $request, $campaignUrl, LoggerInterface $logger)
      {

          
          $logger->debug("Entering RegistrationController->passwordResetAction");
          $em = $this->getDoctrine()->getManager();

          $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);

          if(null !== $request->query->get('password_reset_token') && null !== $request->query->get('email')){
              $fail = false;
              $passwordResetToken = $request->query->get('password_reset_token');
              $userEmail = $request->query->get('email');
              $user = $em->getRepository('App:User')->findOneByEmail(urldecode($userEmail));

              if(empty($user)){
                $this->addFlash('warning','We are sorry, we could not find this account');
                $fail = true;
              }

              if(!$fail && $user->getPasswordResetCode() !== $passwordResetToken){
                $this->addFlash('warning','We are sorry, This token could not be validated');
                $fail = true;
              }

              if(!$fail){
                $dateNow = (new \DateTime());
                $userPasswordResetCodeTimestamp = $user->getPasswordResetCodeTimestamp()->modify('+30 minutes');
                if($userPasswordResetCodeTimestamp < $dateNow){
                  $logger->debug("Password Reset Code is expired");
                  $this->addFlash('warning','This confirmation code is expired');
                  $fail = true;
                }
              }

              if (!$fail && $request->isMethod('POST')) {
                  $fail = false;
                  $params = $request->request->all();

                  if(!$fail && (empty($params['user']['password']['first']) || empty($params['user']['password']['second']))){
                    $this->addFlash('warning','Please confirm your password');
                    $fail = true;
                  }

                  if(!$fail && ($params['user']['password']['first'] !== $params['user']['password']['second'])){
                    $this->addFlash('warning','Passwords do not match');
                    $fail = true;
                  }


                  if(!$fail){
                    $user->setPasswordResetCode = null;
                    $encoder = $this->container->get('security.password_encoder');
                    $encoded = $encoder->encodePassword($user, $params['user']['password']['first']);
                    $user->setPassword($encoded);

                    $em->persist($user);
                    $em->flush();


                    //Send Email
                    $message = (new \Swift_Message("FR Manager Password Reset Notification"))
                      ->setFrom('funrun@lrespto.org') //TODO: Change this to parameter for support email
                      ->setTo($user->getEmail())
                      ->setContentType("text/html")
                      ->setBody(
                          $this->renderView('email/password_reset.email.twig', array('campaign' => $campaign, 'user' => $user))
                      );

                    $this->get('mailer')->send($message);



                    $logger->info("Password has been reset for user:".$user->getEmail());
                    $this->addFlash('success','Password has been reset!');
                    return $this->redirectToRoute('login', array('campaignUrl' => $campaign->getUrl()));

                  }
              }

          }else{
            $logger->debug("There was an issue with this URL, please try again");
            return $this->redirectToRoute('password_reset_form', array('campaignUrl' => $campaign->getUrl()));

          }

          return $this->render('security/password.reset.html.twig', array(
            'campaign' => $campaign,
            'user' => $user
          ));


      }








  private function generateRandomString($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
  }


}
