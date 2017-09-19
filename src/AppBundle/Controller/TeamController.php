<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Team;
use AppBundle\Entity\Grade;
use AppBundle\Entity\TeamStudent;
use AppBundle\Entity\Campaign;
use AppBundle\Utils\ValidationHelper;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Utils\QueryHelper;
use AppBundle\Utils\DonationHelper;
use \DateTime;
use \DateTimeZone;


/**
 * Team controller.
 *
 * @Route("/{campaignUrl}/team")
 */
class TeamController extends Controller
{
  /**
   * Lists all Team entities.
   *
   * @Route("/", name="team_index")
   * @Method({"GET", "POST"})
   */
  public function teamIndexAction($campaignUrl)
  {
      $logger = $this->get('logger');
      $entity = 'Team';
      $em = $this->getDoctrine()->getManager();

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

      $queryHelper = new QueryHelper($em, $logger);
      $tempDate = new DateTime();
      $dateString = $tempDate->format('Y-m-d').' 00:00:00';
      $reportDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);

      return $this->render('team/team.index.html.twig', array(
        'teams' => $queryHelper->getTeamRanks(array('campaign' => $campaign, 'limit'=> 0)),
        'entity' => strtolower($entity),
        'campaign' => $campaign,
      ));

  }

    /**
     * Finds and displays a Team entity.
     *
     * @Route("/{teamUrl}", name="team_show")
     * @Method("GET")
     */
    public function showAction($campaignUrl, $teamUrl)
    {
        $logger = $this->get('logger');
        $entity = 'Team';
        $em = $this->getDoctrine()->getManager();

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

        //CODE TO CHECK TO SEE IF TEAM EXISTS
        $team = $em->getRepository('AppBundle:Team')->findOneBy(array('url'=>$teamUrl, 'campaign' => $campaign));
        if(is_null($team)){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this team.');
          return $this->redirectToRoute('team_index', array('campaignUrl'=>$campaign->getUrl()));
        }

        $queryHelper = new QueryHelper($em, $logger);
        $tempDate = new DateTime();
        $dateString = $tempDate->format('Y-m-d').' 00:00:00';
        $reportDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);

        return $this->render('team/team.show.html.twig', array(
            'team' => $team,
            'team_rank' => $queryHelper->getTeamRank($team->getId(), array('campaign' => $campaign, 'limit'=> 0)),
            'entity' => $entity,
            'campaign' => $campaign,
            'teamStudents' => $team->getTeamStudents(),
        ));
    }


    /**
     * Displays a form to edit an existing Team entity.
     *
     * @Route("/{teamUrl}/edit", name="team_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $campaignUrl, $teamUrl)
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        //CODE TO CHECK TO SEE IF CAMPAIGN EXISTS
        $logger->debug("Checking to see if campaign Exists");
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

        //CODE TO CHECK TO SEE IF TEAM EXISTS
        $team = $em->getRepository('AppBundle:Team')->findOneBy(array('url'=>$teamUrl, 'campaign' => $campaign));
        if(is_null($team)){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this team.');
          return $this->redirectToRoute('team_index', array('campaignUrl'=>$campaign->getUrl()));
        }

        //CODE TO CHECK TO SEE IF THIS CAMPAIGN IS MANAGED BY THIS USER
        if($team->getUser()->getId() !== $this->get('security.token_storage')->getToken()->getUser()->getId()){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, you cannot edit this team');
          return $this->redirectToRoute('team_show', array('campaignUrl'=>$campaign->getUrl(), 'teamUrl' => $team->getUrl()));
        }


        if(null !== $request->query->get('action')){
            $action = $request->query->get('action');

            if($action === 'delete_team_student'){
              $logger->debug("Performing delete_team_student");
              if(null == $request->query->get('teamStudentID')){
                $this->get('session')->getFlashBag()->add('warning', 'Could not delete student, ID not provided');
                return $this->redirectToRoute('team_edit', array('campaignUrl' => $campaign->getUrl(), 'teamUrl' => $teamUrl));
              }

              $teamStudent = $em->getRepository('AppBundle:TeamStudent')->find($request->query->get('teamStudentID'));
              if(empty($teamStudent)){
                $this->get('session')->getFlashBag()->add('warning', 'Could not find student to delete');
                return $this->redirectToRoute('team_edit', array('campaignUrl' => $campaign->getUrl(), 'teamUrl' => $teamUrl));
              }

              $logger->debug("Removing TeamStudent #".$teamStudent->getId());
              $em->remove($teamStudent);
              $logger->debug("Flushing");
              $em->flush();

              $logger->debug("Doing a Donation Database Refresh");
              $donationHelper = new DonationHelper($em, $logger);
              $donationHelper->reloadDonationDatabase(array('campaign'=>$campaign));

              $this->get('session')->getFlashBag()->add('info', 'Student has been removed');
              return $this->redirectToRoute('team_edit', array('campaignUrl' => $campaign->getUrl(), 'teamUrl' => $teamUrl));
            }
        }



        if ($request->isMethod('POST')) {
            $params = $request->request->all();


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
            if($team->getTeamType()->getValue() == "student"){
              if(empty($params['team']['student']['classroomId']) or empty($params['team']['student']['name']) or $params['team']['student']['classroomId'] == '' or $params['team']['student']['name'] == ''){
                $this->addFlash('warning','Student classrom and name is required');
                $fail = true;
              }
            }

            //If it is a teacher team a class is required
            if($team->getTeamType()->getValue() == "teacher"){
              if(empty($params['team']['classroom']['classroomId']) or $params['team']['classroom']['classroomId'] == ''){
                $this->addFlash('warning','Please select a classroom');
                $fail = true;
              }
            }


            if(!$fail){
              $logger->debug("Updating team: ".$team->getName());
              if($team->getName() !== $params['team']['name']){
                $team->setName($params['team']['name']);
                $team->setUrl($this->createTeamUrl($campaign, $params['team']['name']));
              }
              $team->setFundingGoal($params['team']['fundingGoal']);
              $team->setDescription($params['team']['description']);


              //If it is a "Teacher" team, set the classroom
              if($team->getTeamType()->getValue() == "teacher"){
                  $team->setClassroom($em->getRepository('AppBundle:Classroom')->find($params['team']['classroom']['classroomId']));
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
              if($team->getTeamType()->getValue() == "family"){
                  foreach ($params['team']['students'] as $key => $student) {
                    $teamStudent = $em->getRepository('AppBundle:TeamStudent')->find($student['id']);
                    if(!empty($teamStudent)){
                      if($teamStudent->getClassroom()->getId() !== $student['classroomId'] || $teamStudent->getName() !== $student['name']){
                        $teamStudent->setClassroom($em->getRepository('AppBundle:Classroom')->find($student['classroomId']));
                        $teamStudent->setGrade($em->getRepository('AppBundle:Grade')->find($teamStudent->getClassroom()->getGrade()));
                        $teamStudent->setName($student['name']);
                        $em->persist($teamStudent);
                      }
                    }else{
                      $logger->info("Could not find TeamStudent #".$student['id']);
                    }
                  }

                  if(!empty($params['team']['newStudent'])){
                    $student = $params['team']['newStudent'];
                    if(!empty($student['classroomId']) && !empty($student['name'])){
                      $logger->debug("Adding TeamStudents to Team ".$team->getId());
                      $teamStudent = new TeamStudent();
                      $teamStudent->setTeam($team);
                      $teamStudent->setClassroom($em->getRepository('AppBundle:Classroom')->find($student['classroomId']));
                      $teamStudent->setGrade($em->getRepository('AppBundle:Grade')->find($teamStudent->getClassroom()->getGrade()));
                      $teamStudent->setName($student['name']);
                      $teamStudent->setCreatedBy($this->get('security.token_storage')->getToken()->getUser());
                      $em->persist($teamStudent);
                    }
                  }

                }else if($team->getTeamType()->getValue() == "student"){
                  $student = $params['team']['student'];
                  $logger->debug("Adding TeamStudents".$team->getId());
                  $teamStudent = $em->getRepository('AppBundle:TeamStudent')->findOneBy(array('team'=>$team));
                  $teamStudent->setTeam($team);
                  $teamStudent->setClassroom($em->getRepository('AppBundle:Classroom')->find($student['classroomId']));
                  $teamStudent->setGrade($em->getRepository('AppBundle:Grade')->find($teamStudent->getClassroom()->getGrade()));
                  $teamStudent->setName($student['name']);
                  $teamStudent->setCreatedBy($this->get('security.token_storage')->getToken()->getUser());
                  $em->persist($teamStudent);
                }

              $em->flush();

              $donationHelper = new DonationHelper($em, $logger);
              $donationHelper->reloadDonationDatabase(array('campaign'=>$campaign));

              $this->addFlash('success','Team has been updated!');
              return $this->redirectToRoute('team_edit', array('campaignUrl' => $campaign->getUrl(), 'teamUrl' => $team->getUrl()));
            }

        }

        $qb = $em->createQueryBuilder()->select('u')
             ->from('AppBundle:Classroom', 'u')
             ->join('AppBundle:Grade', 'g')
             ->where('u.grade = g.id')
             ->andWhere('u.campaign = :campaignId')
             ->setParameter('campaignId', $campaign->getId())
             ->orderBy('g.name', 'ASC');

        $classrooms =  $qb->getQuery()->getResult();

        return $this->render('team/team.edit.html.twig', array(
            'team' => $team,
            'campaign' => $campaign,
            'classrooms' => $classrooms,
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
