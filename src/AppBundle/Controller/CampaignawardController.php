<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Campaignaward;
use AppBundle\Entity\Campaignawardtype;
use AppBundle\Entity\Campaignawardstyle;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Entity\Classroom;
use AppBundle\Utils\QueryHelper;
use DateTime;
/**
 * Campaignaward controller.
 *
 * @Route("/{campaignUrl}/campaignaward")
 */
class CampaignawardController extends Controller
{
    /**
     * Lists all Campaignaward entities.
     *
     * @Route("/", name="campaignaward_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $entity = 'Campaignaward';
        $em = $this->getDoctrine()->getManager();


        $campaignawards = $em->getRepository('AppBundle:'.$entity)->findAll();

        return $this->render(strtolower($entity).'/index.html.twig', array(
            'campaignawards' => $campaignawards,
            'entity' => $entity,
        ));
    }





    /**
     * Lists all Awards for classrooms.
     *
     * @Route("/awards", name="public_classroom_awards")
     * @Method({"GET", "POST"})
     */
    public function ClassroomAwardsAction($campaignUrl)
    {
      $logger = $this->get('logger');
      $limit = 3;
      $em = $this->getDoctrine()->getManager();
      $campaign =  $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      $queryHelper = new QueryHelper($em, $logger);
      $reportDate = $queryHelper->convertToDay(new DateTime());
      $reportDate->modify('-1 day');

      // replace this example code with whatever you need
      return $this->render('default/classroomAwards.html.twig', array(
        'classrooms' => $queryHelper->getClassroomAwards(array('campaign' => $campaign)),
        'report_date' => $reportDate,
        'campaign' => $campaign
      ));

    }



    /**
     * Finds and displays a Campaignaward entity.
     *
     * @Route("/show/{id}", name="campaignaward_show")
     * @Method("GET")
     */
    public function showAction(Campaignaward $campaignaward)
    {
        $entity = 'Campaignaward';
        $deleteForm = $this->createDeleteForm($campaignaward);

        return $this->render(strtolower($entity).'/show.html.twig', array(
            'campaignaward' => $campaignaward,
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }

}
