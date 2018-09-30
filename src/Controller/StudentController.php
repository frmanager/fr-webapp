<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Student;
use App\Entity\Campaign;
use App\Utils\ValidationHelper;
use App\Utils\CSVHelper;
use App\Utils\QueryHelper;
use App\Utils\CampaignHelper;
use DateTime;

/**
 * Student controller.
 *
 * @Route("/{campaignUrl}/students")
 */
class StudentController extends Controller
{
    /**
     * Lists all Student entities.
     *
     * @Route("/", name="student_index")
     * @Method("GET")
     */
    public function indexAction($campaignUrl, LoggerInterface $logger)
    {
        
        $entity = 'Student';

        $em = $this->getDoctrine()->getManager();

        $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);

        $queryHelper = new QueryHelper($em, $logger);
        $tempDate = new DateTime();
        $dateString = $tempDate->format('Y-m-d').' 00:00:00';
        $reportDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
        // replace this example code with whatever you need

        return $this->render('campaign/student.index.html.twig', array(
            'students' => $queryHelper->getStudentRanks(array('campaign' => $campaign,'limit'=> 0)),
            'entity' => $entity,
            'campaign' => $campaign,
        ));
    }


    /**
     * Finds and displays a Student entity.
     *
     * @Route("/{id}", name="student_show")
     * @Method("GET")
     */
    public function showAction(Student $student, $campaignUrl, LoggerInterface $logger)
    {
        
        $entity = "student";
        $em = $this->getDoctrine()->getManager();

        //CODE TO CHECK TO SEE IF CAMPAIGN EXISTS
        $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);
        if(is_null($campaign)){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
          return $this->redirectToRoute('homepage');
        }

        //CODE TO CHECK TO SEE IF USER HAS PERMISSIONS TO CAMPAIGN
        $campaignHelper = new CampaignHelper($em, $logger);
        if(!$campaignHelper->campaignPermissionsCheck($this->get('security.token_storage')->getToken()->getUser(), $campaign)){
            $this->get('session')->getFlashBag()->add('warning', 'You do not have permissions to this campaign.');
            return $this->redirectToRoute('homepage');
        }

        $deleteForm = $this->createDeleteForm($student, $campaignUrl);
        $student = $this->getDoctrine()->getRepository('App:'.strtolower($entity))->findOneById($student->getId());
        //$logger->debug(print_r($student->getDonations()));

        $qb = $em->createQueryBuilder()->select('u')
               ->from('App:Campaignaward', 'u')
               ->where('u.campaign = :campaign')
               ->setParameter('campaign', $campaign->getId())
               ->orderBy('u.amount', 'DESC');


        $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);
        $campaignAwards = $qb->getQuery()->getResult();

        $queryHelper = new QueryHelper($em);

        return $this->render('campaign/student.show.html.twig', array(
            'student' => $student,
            'classroom' => $queryHelper->getClassroomsData(array('campaign' => $campaign, 'id' => $student->getClassroom()->getId())),
            'student_rank' => $queryHelper->getStudentRank($student->getId(),array('campaign' => $campaign, 'limit' => 0)),
            'classroom_rank' => $queryHelper->getClassroomRank($student->getClassroom()->getId(),array('campaign' => $campaign, 'limit' => 0)),
            'campaign_awards' => $campaignAwards,
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
            'campaign' => $campaign,
        ));
    }

}
