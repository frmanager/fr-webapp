<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Teacher;
use AppBundle\Entity\Grade;
use AppBundle\Utils\ValidationHelper;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Utils\QueryHelper;
use DateTime;

/**
 * Teacher controller.
 *
 * @Route("/{campaignUrl}/teachers")
 */
class TeacherController extends Controller
{
  /**
   * Lists all Teacher entities.
   *
   * @Route("/", name="teacher_index")
   * @Method({"GET", "POST"})
   */
  public function teacherIndexAction($campaignUrl)
  {
      $logger = $this->get('logger');
      $entity = 'Teacher';
      $em = $this->getDoctrine()->getManager();
      $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);  
      $queryHelper = new QueryHelper($em, $logger);
      $tempDate = new DateTime();
      $dateString = $tempDate->format('Y-m-d').' 00:00:00';
      $reportDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
      // replace this example code with whatever you need
      return $this->render('campaign/teacher.index.html.twig', array(
        'teachers' => $queryHelper->getTeacherRanks(array('campaign' => $campaign, 'limit'=> 0)),
        'entity' => strtolower($entity),
        'campaign' => $campaign,
      ));

  }

    /**
     * Creates a new Teacher entity.
     *
     * @Route("/new", name="teacher_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, $campaignUrl)
    {
        $entity = 'Teacher';
        $teacher = new Teacher();
        $form = $this->createForm('AppBundle\Form\TeacherType', $teacher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($teacher);
            $em->flush();

            return $this->redirectToRoute('teacher_index', array('id' => $teacher->getId()));
        }

        return $this->render('crud/new.html.twig', array(
            'teacher' => $teacher,
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    /**
     * Finds and displays a Teacher entity.
     *
     * @Route("/{id}", name="teacher_show")
     * @Method("GET")
     */
    public function showAction(Teacher $teacher, $campaignUrl)
    {
        $logger = $this->get('logger');
        $entity = 'Teacher';
        $teacher = $this->getDoctrine()->getRepository('AppBundle:'.strtolower($entity))->findOneById($teacher->getId());
        //$logger->debug(print_r($student->getDonations()));
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()->select('u')
               ->from('AppBundle:Campaignaward', 'u')
               ->orderBy('u.amount', 'DESC');

        $campaignAwards = $qb->getQuery()->getResult();
        $campaignSettings = new CampaignHelper($this->getDoctrine()->getRepository('AppBundle:Campaignsetting')->findAll());

        $queryHelper = new QueryHelper($em, $logger);

        return $this->render('/campaign/teacher.show.html.twig', array(
            'teacher' => $teacher,
            'teacher_rank' => $queryHelper->getTeacherRank($teacher->getId(),array('limit' => 0)),
            'campaign_awards' => $campaignAwards,
            'campaignsettings' => $campaignSettings->getCampaignSettings(),
            'entity' => $entity,
            'campaign' => $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl),
        ));
    }

}
