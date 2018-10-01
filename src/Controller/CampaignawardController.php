<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Campaignaward;
use App\Entity\Campaignawardtype;
use App\Entity\Campaignawardstyle;
use App\Utils\CampaignHelper;
use App\Entity\Classroom;
use App\Utils\QueryHelper;
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
     * @Route("/", name="campaignaward_index", methods={"GET"})
     * 
     */
    public function indexAction(LoggerInterface $logger)
    {
        $entity = 'Campaignaward';
        $em = $this->getDoctrine()->getManager();


        $campaignawards = $em->getRepository(Campaignaward::class)->findAll();

        return $this->render(strtolower($entity).'/index.html.twig', array(
            'campaignawards' => $campaignawards,
            'entity' => $entity,
        ));
    }





    /**
     * Lists all Awards for classrooms.
     *
     * @Route("/awards", name="public_classroom_awards", methods={"GET", "POST"})
     * 
     */
    public function ClassroomAwardsAction($campaignUrl, LoggerInterface $logger)
    {
      $limit = 3;
      $em = $this->getDoctrine()->getManager();
      $campaign =  $em->getRepository(Campaign::class)->findOneByUrl($campaignUrl);
      $queryHelper = new QueryHelper($em, $logger);
      $reportDate = $queryHelper->convertToDay(new DateTime());

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
     * @Route("/show/{id}", name="campaignaward_show", methods={"GET"})
     * 
     */
    public function showAction(Campaignaward $campaignaward, LoggerInterface $logger)
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
