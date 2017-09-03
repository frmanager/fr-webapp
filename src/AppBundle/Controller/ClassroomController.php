<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Classroom;
use AppBundle\Entity\Grade;
use AppBundle\Utils\ValidationHelper;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Utils\QueryHelper;
use DateTime;

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
  public function classroomIndexAction($campaignUrl)
  {
      $logger = $this->get('logger');
      $entity = 'Classroom';
      $em = $this->getDoctrine()->getManager();
      $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
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
     * Creates a new Classroom entity.
     *
     * @Route("/new", name="classroom_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, $campaignUrl)
    {
        $entity = 'Classroom';
        $classroom = new Classroom();
        $form = $this->createForm('AppBundle\Form\ClassroomType', $classroom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($classroom);
            $em->flush();

            return $this->redirectToRoute('classroom_index', array('id' => $classroom->getId()));
        }

        return $this->render('crud/new.html.twig', array(
            'classroom' => $classroom,
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Finds and displays a Classroom entity.
     *
     * @Route("/{id}", name="classroom_show")
     * @Method("GET")
     */
    public function showAction(Classroom $classroom, $campaignUrl)
    {
        $logger = $this->get('logger');
        $entity = 'Classroom';
        $classroom = $this->getDoctrine()->getRepository('AppBundle:'.strtolower($entity))->findOneById($classroom->getId());
        //$logger->debug(print_r($student->getDonations()));
        $em = $this->getDoctrine()->getManager();

        $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);

        $qb = $em->createQueryBuilder()->select('u')
               ->from('AppBundle:Campaignaward', 'u')
               ->andWhere('u.campaign = :campaignId')
               ->setParameter('campaignId', $campaign->getId())
               ->orderBy('u.amount', 'DESC');

        $campaignAwards = $qb->getQuery()->getResult();

        $queryHelper = new QueryHelper($em, $logger);

        return $this->render('/campaign/classroom.show.html.twig', array(
            'classroom' => $classroom,
            'classroom_rank' => $queryHelper->getClassroomRank($classroom->getId(),array('campaign' => $campaign, 'limit' => 0)),
            'campaign_awards' => $campaignAwards,
            'entity' => $entity,
            'campaign' => $campaign,
        ));
    }

}
