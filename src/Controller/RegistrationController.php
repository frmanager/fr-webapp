<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Form\UserType;
use App\Entity\User;
use App\Entity\Team;
use App\Entity\TeamStudent;
use App\Entity\Campaign;
use App\Entity\CampaignUser;
use App\Entity\UserStatus;
use App\Utils\CampaignHelper;
use Symfony\Component\Routing\Annotation\Route;
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
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, LoggerInterface $logger)
    {
        

        if(!empty($request->attributes->get('_route_params'))){
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)){
            $em = $this->getDoctrine()->getManager();
            $campaign = $em->getRepository('App:Campaign')->findOneByUrl($routeParams['campaignUrl']);
          }
        }

        //Verifying if user is logged in, if their account is confirmed, and if their team already exists
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('ROLE_USER')) {
          $logger->debug("User is already logged in and has an account. Checking for email confirmation");
          $user = $this->get('security.token_storage')->getToken()->getUser();
          if($user->getUserStatus()->getName() == "Confirmed"){
            $team = $em->getRepository('App:Team')->findOneBy(array('user' => $user, 'campaign' => $campaign));
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

        if ($request->isMethod('POST')) {
          $params = $request->request->all();

          $fail = false;

          if(!$fail && empty($params['user']['firstName'])){
            $this->addFlash('warning','First Name is required');
            $fail = true;
          }

          if(!$fail && empty($params['user']['lastName'])){
            $this->addFlash('warning','Last Name is required');
            $fail = true;
          }

          if(!$fail && empty($params['user']['email'])){
            $this->addFlash('warning','Email Address is required');
            $fail = true;
          }

          if(!$fail && empty($params['user']['Password']['first'])){
            $this->addFlash('warning','Password is required');
            $fail = true;
          }         

          if($params['user']['Password']['second'] !== $params['user']['Password']['first']){
            $this->addFlash('warning','Passwords must match');
            $fail = true;
          }      

          $userCheck = $em->getRepository('App:User')->findOneByEmail($params['user']['email']);
          if(!is_null($userCheck)){
            $this->addFlash('warning', 'We apologize, an account already exists with this email.');
            $fail = true;
          }


          if(!$fail){
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setUsername($params['user']['email']);
            $user->setEmail($params['user']['email']);            
            $user->setFirstName($params['user']['firstName']);
            $user->setLastName($params['user']['lastName']);            
            $user->setPassword($password);
            $user->setApiKey($password);
            $user->setFundraiserFlag(true);
            $user->setEmailConfirmationCode($this->generateRandomString(8));
            $user->setEmailConfirmationCodeTimestamp(new \DateTime());
            //Get User Status
            $userStatus = $em->getRepository('App:UserStatus')->findOneByName('Registered');

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
        }

        return $this->render('registration/register.html.twig',
            array(
              'user' => $user,
              'campaign' => $campaign
            )
        );
    }

    /**
     * @Route("/register_team", name="register_team_select")
     *
     */
    public function registerTeamSelectTeamTypeAction(Request $request, $campaignUrl, LoggerInterface $logger)
    {

      
      $this->denyAccessUnlessGranted('ROLE_USER');

      $em = $this->getDoctrine()->getManager();
      $campaign =  $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);
      $teamTypes =  $em->getRepository('App:TeamType')->findAll();

      $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);

      //Verifying if user has completed email confirmation
      $user = $this->get('security.token_storage')->getToken()->getUser();
      if($user->getUserStatus()->getName() !== "Confirmed"){
          $this->get('session')->getFlashBag()->add('warning', 'Hi, it looks like you have not confirmed your email yet.');
          return $this->redirectToRoute('confirm_email', array('campaignUrl' => $campaign->getUrl()));
      }

      //Make sure user doesn't already have a team setup for this campaign.
      $teamCheck = $em->getRepository('App:Team')->findOneBy(array('campaign' => $campaign, 'user' => $this->get('security.token_storage')->getToken()->getUser()));
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
      public function emailConfirmationAction(Request $request, $campaignUrl, LoggerInterface $logger, \Swift_Mailer $mailer)
      {

          
          $logger->debug("Entering RegistrationController->emailConfirmationAction");
          $em = $this->getDoctrine()->getManager();

          $this->denyAccessUnlessGranted('ROLE_USER');

          $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);

          $user = $this->get('security.token_storage')->getToken()->getUser();
          if(null !== $request->query->get('action')){
              $action = $request->query->get('action');

              if($action === 'resend_email_confirmation'){
                $user->setEmailConfirmationCode($this->generateRandomString(8));
                $user->setEmailConfirmationCodeTimestamp(new \DateTime());
                $em->persist($user);
                $em->flush();

                //Send Email
                $message = (new \Swift_Message("[FR Manager] account activation code"))
                  ->setFrom('funrun@lrespto.org') //TODO: Change this to parameter for support email
                  ->setTo($user->getEmail())
                  ->setContentType("text/html")
                  ->setBody(
                      $this->renderView('email/email_confirmation.email.twig', array('campaign' => $campaign, 'user' => $user))
                  );

                $logger->debug("Sending Email");
                $emailResult = $mailer->send($message);
                $logger->debug($emailResult);

                $this->addFlash('info', 'New code has been sent to your email, please check your inbox');
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
                $userStatus =  $em->getRepository('App:UserStatus')->findOneByName("CONFIRMED");
                $user->setUserStatus($userStatus);
                $user->setIsActive(true);
                $em->persist($user);
                $em->flush();

                $logger->debug("User is confirmed, checking to see if they have already registered their team");
                $team = $em->getRepository('App:Team')->findOneBy(array('user' => $user, 'campaign' => $campaign));
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
    public function registerTeamAction(Request $request, $campaignUrl, $teamTypeValue, LoggerInterface $logger)
    {
      
      $this->denyAccessUnlessGranted('ROLE_USER');

      $em = $this->getDoctrine()->getManager();
      $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);
      $teamType = $em->getRepository('App:TeamType')->findOneByValue($teamTypeValue);

      if(is_null($campaign)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
        return $this->redirectToRoute('homepage');
      }

      if(is_null($teamType)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this teamType.');
        return $this->redirectToRoute('register_team_select', array('campaignUrl' => $campaign->getUrl()));
      }

      $teamCheck = $em->getRepository('App:Team')->findOneBy(array('campaign'=>$campaign, 'user'=>$this->get('security.token_storage')->getToken()->getUser()));
      if(!is_null($teamCheck)){
        $this->get('session')->getFlashBag()->add('warning', 'You already have a team and do not need to register a new one');
        return $this->redirectToRoute('team_show', array('campaignUrl'=>$campaign->getUrl(), 'teamUrl' => $teamCheck->getUrl()));
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
            if(empty($params['team']['students'][1]['classroomID']) || empty($params['team']['students'][1]['name']) || $params['team']['students'][1]['classroomID'] == '' || $params['team']['students'][1]['name'] == ''){
              $this->addFlash('warning','Student classrom and name is required');
              $fail = true;
            }
          }

          //If it is a teacher team a class is required
          if($teamType->getValue() == "teacher"){
            if(empty($params['team']['classroom']['classroomID']) || $params['team']['classroom']['classroomID'] == ''){
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
                $team->setClassroom($em->getRepository('App:Classroom')->find($params['team']['classroom']['classroomID']));
            }

            $team->setUser($this->get('security.token_storage')->getToken()->getUser());
            $team->setCreatedBy($this->get('security.token_storage')->getToken()->getUser());
            $em->persist($team);
            $em->flush();

            $user = $em->getRepository('App:User')->find($this->get('security.token_storage')->getToken()->getUser()->getId());
            $user->setFundraiserFlag(true);
            $em->persist($user);
            $em->flush();

            //If is a "family" page, we need to add students
            if($teamType->getValue() == "family" || $teamType->getValue() == "student"){
              $logger->debug("Adding TeamStudents to Team ".$team->getId());
              foreach ($params['team']['students'] as $key => $student) {
                if(!empty($student['classroomID']) && !empty($student['name']) && !$student['classroomID'] !== '' && $student['name'] !== ''){
                  $teamStudent = new TeamStudent();
                  $classroom = $em->getRepository('App:Classroom')->find($student['classroomID']);
                  $teamStudent->setTeam($team);
                  $teamStudent->setClassroom($classroom);
                  $teamStudent->setGrade($em->getRepository('App:Grade')->find($classroom->getGrade()));
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
           ->from('App:Classroom', 'u')
           ->join('App:Grade', 'g')
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
      $teamCheck = $em->getRepository('App:Team')->findOneBy(array('url' => $newUrl, 'campaign' => $campaign));
      if(!empty($teamCheck)){
        $fixed = false;
        $count = 1;
        while(!$fixed){
          $teamCheck = $em->getRepository('App:Team')->findOneBy(array('url' => $newUrl.$count, 'campaign' => $campaign));
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
