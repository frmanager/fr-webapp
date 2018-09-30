<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Classroom;
use App\Entity\Grade;
use App\Utils\ValidationHelper;
use App\Utils\CSVHelper;
use App\Utils\CampaignHelper;
use App\Utils\QueryHelper;
use \DateTime;
use \DateTimeZone;

/**
 * Classroom controller.
 *
 * @Route("/{campaignUrl}/classrooms")
 */
class ClassroomController extends Controller
{
  /**
   * Lists all Classroom entities.
   *
   * @Route("/", name="classroom_index")
   * @Method({"GET", "POST"})
   */
  public function classroomIndexAction($campaignUrl, LoggerInterface $logger)
  {
      
      $entity = 'Classroom';
      $em = $this->getDoctrine()->getManager();

      $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);

      $queryHelper = new QueryHelper($em, $logger);
      $tempDate = new DateTime();
      $dateString = $tempDate->format('Y-m-d').' 00:00:00';
      $reportDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
      // replace this example code with whatever you need
      return $this->render('campaign/classroom.index.html.twig', array(
        'classrooms' => $queryHelper->getClassroomRanks(array('campaign' => $campaign, 'limit'=> 0)),
        'entity' => strtolower($entity),
        'campaign' => $campaign,
      ));

  }


    /**
     * Finds and displays a Classroom entity.
     *
     * @Route("/{id}", name="classroom_show")
     * @Method("GET")
     */
    public function showAction(Classroom $classroom, $campaignUrl, LoggerInterface $logger)
    {
        
        $entity = 'Classroom';
        //$logger->debug(print_r($student->getDonations()));
        $em = $this->getDoctrine()->getManager();

        $campaign = $em->getRepository('App:Campaign')->findOneByUrl($campaignUrl);
        $classroom = $this->getDoctrine()->getRepository('App:'.strtolower($entity))->findOneById($classroom->getId());

        $qb = $em->createQueryBuilder()->select('u')
               ->from('App:Campaignaward', 'u')
               ->andWhere('u.campaign = :campaignID')
               ->setParameter('campaignID', $campaign->getId())
               ->orderBy('u.amount', 'DESC');

        $campaignAwards = $qb->getQuery()->getResult();

        $queryHelper = new QueryHelper($em, $logger);

        return $this->render('/campaign/classroom.show.html.twig', array(
            'classroom' => $classroom,
            'donations' => $queryHelper->getClassroomsData(array('campaign' => $campaign, 'id' => $classroom->getId(), 'limit' => 0)),
            'classroom_rank' => $queryHelper->getClassroomRank($classroom->getId(),array('campaign' => $campaign, 'limit' => 0)),
            'campaign_awards' => $campaignAwards,
            'entity' => $entity,
            'campaign' => $campaign,
        ));
    }

}
