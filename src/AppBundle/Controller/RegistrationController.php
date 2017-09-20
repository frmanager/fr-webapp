<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\Team;
use AppBundle\Entity\TeamStudent;
use AppBundle\Entity\Campaign;
use AppBundle\Entity\CampaignUser;
use AppBundle\Entity\UserStatus;
use AppBundle\Utils\CampaignHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use \DateTime;
use \DateTimeZone;

/**
 * Security controller.
 *
 * @Route("/{campaignUrl}")
 */
class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $logger = $this->get('logger');

        if(!empty($request->attributes->get('_route_params'))){
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)){
            $em = $this->getDoctrine()->getManager();
            $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($routeParams['campaignUrl']);
          }
        }

        //Verifying if user is logged in, if their account is confirmed, and if their team already exists
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('ROLE_USER')) {
          $logger->debug("User is already logged in and has an account. Checking for email confirmation");
          $user = $this->get('security.token_storage')->getToken()->getUser();
          if($user->getUserStatus()->getName() == "Confirmed"){
            $team = $em->getRepository('AppBundle:Team')->findOneBy(array('user' => $user, 'campaign' => $campaign));
            if(is_null($team)){
              $this->get('session')->getFlashBag()->add('warning', 'Hi, it looks like you have not completed your team registration yet');
              return $this->redirectToRoute('register_team_select', array('campaignUrl' => $campaign->getUrl()));
            }
          }else{
            return $this->redirectToRoute('confirm_email', array('campaignUrl' => $campaign->getUrl()));
          }
        }

        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $userCheck = $em->getRepository('AppBundle:User')->findOneByEmail($user->getEmail());
            if(!is_null($userCheck)){
              $this->get('session')->getFlashBag()->add('warning', 'We apologize, an account already exists with this email.');
              return $this->render('registration/register.html.twig',
                  array(
                    'form' => $form->createView(),
                    'campaign' => $campaign
                  )
              );
            }

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setApiKey($password);
            $user->setUsername($user->getEmail());
            $user->setFundraiserFlag(true);
            $user->setEmailConfirmationCode($this->generateRandomString(8));
            $user->setEmailConfirmationCodeTimestamp(new \DateTime());
            //Get User Status
            $userStatus = $em->getRepository('AppBundle:UserStatus')->findOneByName('Registered');

            if(!empty($userStatus)){
              $logger->debug('UserStatus of Registered could not be found');
            }

            $user->setUserStatus($userStatus);


            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            //Send Confirmation Email
            //Send Email
            $message = (new \Swift_Message("FR Manager account activation code"))
              ->setFrom('funrun@lrespto.org') //TODO: Change this to parameter for support email
              ->setTo($user->getEmail())
              ->setContentType("text/html")
              ->setBody(
                  $this->renderView('email/email_confirmation.email.twig', array('campaign' => $campaign, 'user' => $user))
              );

            $this->get('mailer')->send($message);


            //Create CampaignUser
            $campaignUser = new CampaignUser();
            $campaignUser->setUser($user);
            $campaignUser->setCampaign($campaign);
            $em->persist($campaignUser);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user
            $this->authenticateUser($user);
            $this->addFlash('success','Thanks for registering. You should receive an email with instructions on how to fully activate your account.');

            return $this->redirectToRoute('confirm_email', array('campaignUrl' => $campaign->getUrl()));
        }

        return $this->render('registration/register.html.twig',
            array(
              'form' => $form->createView(),
              'campaign' => $campaign
            )
        );
    }

    /**
     * @Route("/register_team", name="register_team_select")
     *
     */
    public function registerTeamSelectTeamTypeAction(Request $request, $campaignUrl)
    {

      $logger = $this->get('logger');
      $this->denyAccessUnlessGranted('ROLE_USER');

      $em = $this->getDoctrine()->getManager();
      $campaign =  $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      $teamTypes =  $em->getRepository('AppBundle:TeamType')->findAll();

      //CODE TO CHECK TO SEE IF CAMPAIGN EXISTS
      $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      $accessFail = false;
      //Does Campaign Exist? if not, fail
      if(is_null($campaign)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
        return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
      //If it does exist, is it "offline"? if not, fail
      }elseif(!$campaign->getOnlineFlag()){
        $securityContext = $this->container->get('security.authorization_checker');
        //If it is offline, is a user logged in? If not, fail
        if ($securityContext->isGranted('ROLE_USER')) {
          $campaignHelper = new CampaignHelper($em, $logger);
          //Does that user have access to the campaign? If not, fail
          if(!$campaignHelper->campaignPermissionsCheck($this->get('security.token_storage')->getToken()->getUser(), $campaign)){
            $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
            return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
          }
        }else{
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
          return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
        }
      }elseif($campaign->getStartDate() > new DateTime("now")){
        return $this->redirectToRoute('campaign_splash', array('campaignUrl'=>$campaign->getUrl(), 'campaign'=>$campaign));
      }

      //IF CAMPAIGN CHECK FAILED
      if($accessFail){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
        return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
      }

      //Verifying if user has completed email confirmation
      $user = $this->get('security.token_storage')->getToken()->getUser();
      if($user->getUserStatus()->getName() !== "Confirmed"){
          $this->get('session')->getFlashBag()->add('warning', 'Hi, it looks like you have not confirmed your email yet.');
          return $this->redirectToRoute('confirm_email', array('campaignUrl' => $campaign->getUrl()));
      }

      //Make sure user doesn't already have a team setup for this campaign.
      $teamCheck = $em->getRepository('AppBundle:Team')->findOneBy(array('campaign' => $campaign, 'user' => $this->get('security.token_storage')->getToken()->getUser()));
      if(!is_null($teamCheck)){
        $this->get('session')->getFlashBag()->add('warning', 'Unfortunatley, you can only have one team per campaign.');
        return $this->redirectToRoute('team_show', array('campaignUrl' => $campaign->getUrl(), 'teamUrl' => $teamCheck->getUrl()));
      }


      return $this->render('team/team.type.select.html.twig', array(
        'campaign' => $campaign,
        'teamTypes' => $teamTypes
      ));
    }



    /**
     * Confirms Email Address
     *
     * @Route("/confirm_email", name="confirm_email")
     *
     */
      public function emailConfirmationAction(Request $request, $campaignUrl)
      {

          $logger = $this->get('logger');
          $logger->debug("Entering RegistrationController->emailConfirmationAction");
          $em = $this->getDoctrine()->getManager();

          $this->denyAccessUnlessGranted('ROLE_USER');

          //CODE TO CHECK TO SEE IF CAMPAIGN EXISTS
          $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
          $accessFail = false;
          //Does Campaign Exist? if not, fail
          if(is_null($campaign)){
            $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
            return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
          //If it does exist, is it "offline"? if not, fail
          }elseif(!$campaign->getOnlineFlag()){
            $securityContext = $this->container->get('security.authorization_checker');
            //If it is offline, is a user logged in? If not, fail
            if ($securityContext->isGranted('ROLE_USER')) {
              $campaignHelper = new CampaignHelper($em, $logger);
              //Does that user have access to the campaign? If not, fail
              if(!$campaignHelper->campaignPermissionsCheck($this->get('security.token_storage')->getToken()->getUser(), $campaign)){
                $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
                return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
              }
            }else{
              $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
              return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
            }
          }elseif($campaign->getStartDate() > new DateTime("now")){
            return $this->redirectToRoute('campaign_splash', array('campaignUrl'=>$campaign->getUrl(), 'campaign'=>$campaign));
          }

          $user = $this->get('security.token_storage')->getToken()->getUser();
          if(null !== $request->query->get('action')){
              $action = $request->query->get('action');

              if($action === 'resend_email_confirmation'){
                $user->setEmailConfirmationCode($this->generateRandomString(8));
                $user->setEmailConfirmationCodeTimestamp(new \DateTime());
                $em->persist($user);
                $em->flush();

                //Send Email
                $message = (new \Swift_Message("FR Manager account activation code"))
                  ->setFrom('funrun@lrespto.org') //TODO: Change this to parameter for support email
                  ->setTo($user->getEmail())
                  ->setContentType("text/html")
                  ->setBody(
                      $this->renderView('email/email_confirmation.email.twig', array('campaign' => $campaign, 'user' => $user))
                  );

                $this->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('info', 'New code has been sent to your email, please check your inbox');
                return $this->redirectToRoute('confirm_email', array('campaignUrl' => $campaign->getUrl()));
              }
          }

          if ($request->isMethod('POST')) {
              $fail = false;
              $params = $request->request->all();

              if(empty($params['user']['emailConfirmationCode'])){
                $this->addFlash('warning','Please input the Email Confirmation Code');
                $fail = true;
              }else{
                $confirmationCode = $params['user']['emailConfirmationCode'];
              }

              //see if the emailConfirmationCode is still Valid
              //Its only valid for 30 minutes
              //We take the timestamp in the database, add 30 minutes, and see if it is still greater than Now...
              $dateNow = (new \DateTime());
              $userEmailConfirmationCodeTimestamp = $user->getEmailConfirmationCodeTimestamp()->modify('+30 minutes');
              if(!$fail && $userEmailConfirmationCodeTimestamp < $dateNow){
                $logger->debug("Code is expired");
                $this->addFlash('warning','This confirmation code is expired');
                $fail = true;
              }

              if(!$fail && $user->getEmailConfirmationCode() !== $confirmationCode){
                $logger->debug("Code does not match what is in the database");
                $this->addFlash('warning','Confirmation code does not match our records');
                $fail = true;
              }

              if(!$fail){
                $this->addFlash('warning','Thank you for confirming your account');
                $user->setEmailConfirmationCode = null;
                $userStatus =  $em->getRepository('AppBundle:UserStatus')->findOneByName("CONFIRMED");
                $user->setUserStatus($userStatus);
                $user->setIsActive(true);
                $em->persist($user);
                $em->flush();

                $logger->debug("User is confirmed, checking to see if they have already registered their team");
                $team = $em->getRepository('AppBundle:Team')->findOneBy(array('user' => $user, 'campaign' => $campaign));
                if(is_null($team)){
                  $logger->debug("Team is not registered, forwarding them to the correct page");
                  return $this->redirectToRoute('register_team_select', array('campaignUrl' => $campaign->getUrl()));
                }else{
                  return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));
                }

              }

          }

          return $this->render('registration/confirmed.html.twig', array(
            'campaign' => $campaign,
            'user' => $user,
          ));


      }



    /**
     * @Route("/register_team/{teamTypeValue}", name="register_team")
     *
     */
    public function registerTeamAction(Request $request, $campaignUrl, $teamTypeValue)
    {
      $logger = $this->get('logger');
      $this->denyAccessUnlessGranted('ROLE_USER');

      $em = $this->getDoctrine()->getManager();
      $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      $teamType = $em->getRepository('AppBundle:TeamType')->findOneByValue($teamTypeValue);

      if(is_null($campaign)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
        return $this->redirectToRoute('homepage');
      }

      if(is_null($teamType)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this teamType.');
        return $this->redirectToRoute('register_team_select', array('campaignUrl' => $campaign->getUrl()));
      }

      if ($request->isMethod('POST')) {
          $fail = false;
          $params = $request->request->all();
          //dump($request->request->get('data'));
          //$logger->debug('Team Name: '.print_r($params, true));

          //form Validation
          if(empty($params['team']['name'])){
            $this->addFlash('warning','Family name is required');
            $fail = true;
          }

          if(empty($params['team']['fundingGoal'])){
            $this->addFlash('warning','Funding goal is required');
            $fail = true;
          }

          if($this->reservedWordCheck($params['team']['name'])){
            $this->addFlash('warning','We apologize, this name cannot be used');
            $fail = true;
          }

          if($this->badWordCheck($params['team']['name'])){
            $this->addFlash('warning','We apologize, this name cannot be used');
            $fail = true;
          }

          //If it is a student team, a class and student name is required
          if($teamType->getValue() == "student"){
            if(empty($params['team']['students'][1]['classroomID']) or empty($params['team']['students'][1]['name']) or $params['team']['students'][1]['classroomID'] == '' or $params['team']['students'][1]['name'] == ''){
              $this->addFlash('warning','Student classrom and name is required');
              $fail = true;
            }
          }

          //If it is a teacher team a class is required
          if($teamType->getValue() == "teacher"){
            if(empty($params['team']['classroom']['classroomID']) or $params['team']['classroom']['classroomID'] == ''){
              $this->addFlash('warning','Please select a classroom');
              $fail = true;
            }
          }


          if(!$fail){
            $logger->debug("Registering new ".$teamType->getName()." team");
            $team = new Team();
            $team->setTeamType($teamType);
            $team->setName($params['team']['name']);
            $team->setFundingGoal($params['team']['fundingGoal']);
            $team->setDescription($params['team']['description']);
            $team->setCampaign($campaign);
            $team->setUrl($this->createTeamUrl($campaign, $params['team']['name']));

            //If it is a "Teacher" team, set the classroom
            if($teamType->getValue() == "teacher"){
                $team->setClassroom($em->getRepository('AppBundle:Classroom')->find($params['team']['classroom']['classroomID']));
            }

            $team->setUser($this->get('security.token_storage')->getToken()->getUser());
            $team->setCreatedBy($this->get('security.token_storage')->getToken()->getUser());
            $em->persist($team);
            $em->flush();

            $user = $em->getRepository('AppBundle:User')->find($this->get('security.token_storage')->getToken()->getUser()->getId());
            $user->setFundraiserFlag(true);
            $em->persist($user);
            $em->flush();

            //If is a "family" page, we need to add students
            if($teamType->getValue() == "family" or $teamType->getValue() == "student"){
              $logger->debug("Adding TeamStudents to Team ".$team->getId());
              foreach ($params['team']['students'] as $key => $student) {
                if(!empty($student['classroomID']) && !empty($student['name']) && !$student['classroomID'] !== '' && $student['name'] !== ''){
                  $teamStudent = new TeamStudent();
                  $teamStudent->setTeam($team);
                  $teamStudent->setClassroom($em->getRepository('AppBundle:Classroom')->find($student['classroomID']));
                  $teamStudent->setGrade($em->getRepository('AppBundle:Grade')->find($teamStudent->getClassroom()->getGrade()));
                  $teamStudent->setName($student['name']);
                  $teamStudent->setCreatedBy($this->get('security.token_storage')->getToken()->getUser());
                  $em->persist($teamStudent);
                }
              }
            }
            $em->flush();

            return $this->redirectToRoute('team_show', array('campaignUrl' => $campaign->getUrl(), 'teamUrl' => $team->getUrl()));
          }

      }

      $qb = $em->createQueryBuilder()->select('u')
           ->from('AppBundle:Classroom', 'u')
           ->join('AppBundle:Grade', 'g')
           ->where('u.grade = g.id')
           ->andWhere('u.campaign = :campaignID')
           ->setParameter('campaignID', $campaign->getId())
           ->orderBy('g.name', 'ASC');

      $classrooms =  $qb->getQuery()->getResult();

     return $this->render('team/team.new.html.twig', array(
       'campaign' => $campaign,
       'classrooms' => $classrooms,
       'teamType' => $teamType
     ));

    }


    /*
    *
    * Returns @true if it finds a reserved word
    * Returns @false if it does not find a reserved word
    *
    */
    private function reservedWordCheck($name){

      //Matches entire string
      if(in_array($name, array('new', 'register', 'family'))){
        return true;
      }

      //Looks for individual words
      /*
      if (strpos($name, 'are') !== false) {
          echo 'true';
      }
      */

      return false;
    }


    private function badWordCheck($string){
      $badWords = array("fuck","fucker","bullshit","sex","shit","damn","ass","fart","cunt","bitch","nigger","pussy","piss","cock","turd");

      $matches = array();
      $matchFound = preg_match_all(
                      "/\b(" . implode($badWords,"|") . ")\b/i",
                      $string,
                      $matches
                    );

      if ($matchFound) {
        return true;
      }

      return false;
    }



    private function authenticateUser(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }

    private function createTeamUrl(Campaign $campaign, $teamName){
      $em = $this->getDoctrine()->getManager();
      //this logic looks to see if URL is in use and then iterates on it
      $newUrl = preg_replace("/[^ \w]+/", "", $teamName);
      $newUrl = str_replace(' ', '-', strtolower($newUrl));
      $teamCheck = $em->getRepository('AppBundle:Team')->findOneBy(array('url' => $newUrl, 'campaign' => $campaign));
      if(!empty($teamCheck)){
        $fixed = false;
        $count = 1;
        while(!$fixed){
          $teamCheck = $em->getRepository('AppBundle:Team')->findOneBy(array('url' => $newUrl.$count, 'campaign' => $campaign));
          if(empty($teamCheck)){
            $newUrl = $newUrl.$count;
            $fixed = true;
          }else{
            $count ++;
          }
        }
      }
      return $newUrl;
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
