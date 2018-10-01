<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Team;
use App\Entity\Grade;
use App\Entity\Classroom;
use App\Entity\User;
use App\Entity\Campaign;
use App\Entity\TeamStudent;
use App\Utils\ValidationHelper;
use App\Utils\CSVHelper;
use App\Utils\CampaignHelper;
use App\Utils\QueryHelper;
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
   * @Route("/", name="teamStudent_index", methods={"GET", "POST"})
   * 
   */
  public function teamStudentIndexAction($campaignUrl, $teamUrl, LoggerInterface $logger)
  {
    

    $campaign = $em->getRepository(Campaign::class)->findOneByUrl($campaignUrl);

    //CODE TO CHECK TO SEE IF TEAM EXISTS
    $team = $em->getRepository(Team::class)->findOneBy(array('url'=>$teamUrl, 'campaign' => $campaign));
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
     * @Route("/{teamStudentId}/edit", name="teamStudent_edit", methods={"GET", "POST"})
     * 
     */
    public function editAction(Request $request, $campaignUrl, $teamUrl, $teamStudentId, LoggerInterface $logger)
    {
        
        $this->denyAccessUnlessGranted('ROLE_USER');
        $em = $this->getDoctrine()->getManager();

        $campaign = $em->getRepository(Campaign::class)->findOneByUrl($campaignUrl);

        //CODE TO CHECK TO SEE IF TEAM EXISTS
        $team = $em->getRepository(Team::class)->findOneBy(array('url'=>$teamUrl, 'campaign' => $campaign));
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
        $teamStudent = $em->getRepository(TeamStudent::class)->findOneBy(array('team'=>$team, 'id'=>$teamStudentId));
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
                $teamStudent->setClassroom($em->getRepository(Classroom::class)->find($params['teamStudent']['classroomID']));
                $teamStudent->setGrade($em->getRepository(Grade::class)->find($teamStudent->getClassroom()->getGrade()));
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
             ->from('App\Entity\Classroom', 'u')
             ->join('App\Entity\Grade', 'g')
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
