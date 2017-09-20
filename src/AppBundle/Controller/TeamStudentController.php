<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Team;
use AppBundle\Entity\Grade;
use AppBundle\Utils\ValidationHelper;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Utils\QueryHelper;
use \DateTime;
use \DateTimeZone;

/**
 * Team controller.
 *
 * @Route("/{campaignUrl}/team/{teamUrl}/child")
 */
class TeamStudentController extends Controller
{

  /**
   * Lists all Team entities.
   *
   * @Route("/", name="teamStudent_index")
   * @Method({"GET", "POST"})
   */
  public function teamIndexAction($campaignUrl, $teamUrl)
  {
    $logger = $this->get('logger');

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

    //CODE TO CHECK TO SEE IF THIS TEAM IS MANAGED BY THIS USER
    if($team->getUser()->getId() !== $this->get('security.token_storage')->getToken()->getUser()->getId()){
      $this->get('session')->getFlashBag()->add('warning', 'We are sorry, you cannot edit this team');
      return $this->redirectToRoute('team_show', array('campaignUrl'=>$campaign->getUrl(), 'teamUrl' => $team->getUrl()));
    }

    return $this->redirectToRoute('team_show', array('campaignUrl'=>$campaign->getUrl(), 'teamUrl' => $team->getUrl()));

  }

    /**
     * Displays a form to edit an existing Team entity.
     *
     * @Route("/{teamStudentId}/edit", name="teamStudent_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $campaignUrl, $teamUrl, $teamStudentId)
    {
        $logger = $this->get('logger');
        $this->denyAccessUnlessGranted('ROLE_USER');

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

        //IF CAMPAIGN CHECK FAILED
        if($accessFail){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
          return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
        }

        //CODE TO CHECK TO SEE IF TEAM EXISTS
        $team = $em->getRepository('AppBundle:Team')->findOneBy(array('url'=>$teamUrl, 'campaign' => $campaign));
        if(is_null($team)){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this team.');
          return $this->redirectToRoute('team_index', array('campaignUrl'=>$campaign->getUrl()));
        }

        //CODE TO CHECK TO SEE IF THIS TEAM IS MANAGED BY THIS USER
        if($team->getUser()->getId() !== $this->get('security.token_storage')->getToken()->getUser()->getId()){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, you cannot edit this team');
          return $this->redirectToRoute('team_show', array('campaignUrl'=>$campaign->getUrl(), 'teamUrl' => $team->getUrl()));
        }

        //CODE TO CHECK TO SEE IF STUDENT EXISTS
        $teamStudent = $em->getRepository('AppBundle:TeamStudent')->findOneBy(array('team'=>$team, 'id'=>$teamStudentId));
        if(is_null($teamStudent)){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this child.');
          return $this->redirectToRoute('team_show', array('campaignUrl'=>$campaign->getUrl(), 'teamUrl'=>$campaign->getUrl()));
        }

        //CODE TO CHECK TO SEE IF THIS STUDENT IS MANAGED BY THIS USER DOES NOT EXIST
        //We assume that if a child is apart of a team, which is checked above, user is good to go.


        if ($request->isMethod('POST')) {
            $params = $request->request->all();

            //If is a "family" page, we need to add students
              if(!empty($params['teamStudent']['classroomID']) && !empty($params['teamStudent']['name']) && !$params['teamStudent']['classroomID'] !== '' && $params['teamStudent']['name'] !== ''){
                $logger->debug("Updating TeamStudent ".$teamStudent->getId()." in Team ".$team->getId());
                $teamStudent->setClassroom($em->getRepository('AppBundle:Classroom')->find($params['teamStudent']['classroomID']));
                $teamStudent->setGrade($em->getRepository('AppBundle:Grade')->find($teamStudent->getClassroom()->getGrade()));
                $teamStudent->setName($params['teamStudent']['name']);
                $teamStudent->setConfirmedFlag(false);
                $teamStudent->setStudent(null);
                $em->persist($teamStudent);
                $em->flush();

                return $this->redirectToRoute('team_show', array('campaignUrl' => $campaign->getUrl(), 'teamUrl' => $team->getUrl()));
              }else{
                $this->get('session')->getFlashBag()->add('warning', 'Could not update record. Information is missing');
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

        return $this->render('teamStudent/teamStudent.edit.html.twig', array(
            'teamStudent' => $teamStudent,
            'classrooms' => $classrooms,
            'team' => $team,
            'campaign' => $campaign,
        ));
    }

}
