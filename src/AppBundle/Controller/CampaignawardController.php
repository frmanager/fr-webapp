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
use AppBundle\Entity\Teacher;
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

        $campaignawardtypes = $em->getRepository('AppBundle:Campaignawardtype')->findAll();
        if (empty($campaignawardtypes)) {
            $defaultCampaignawardtypes = [];

            array_push($defaultCampaignawardtypes, array('name' => 'Teacher/Class', 'value' => 'teacher', 'description' => ''));
            array_push($defaultCampaignawardtypes, array('name' => 'Student/Individual', 'value' => 'student', 'description' => ''));

            foreach ($defaultCampaignawardtypes as $defaultCampaignawardtype) {
                $em = $this->getDoctrine()->getManager();

                $campaignawardtype = new Campaignawardtype();
                $campaignawardtype->setDisplayName($defaultCampaignawardtype['name']);
                $campaignawardtype->setValue($defaultCampaignawardtype['value']);
                $campaignawardtype->setDescription($defaultCampaignawardtype['description']);

                $em->persist($campaignawardtype);
                $em->flush();
            }
        }

        $campaignawardstyles = $em->getRepository('AppBundle:Campaignawardstyle')->findAll();
        if (empty($campaignawardstyles)) {
            $defaultCampaignawardstyles = [];

            array_push($defaultCampaignawardstyles, array('name' => 'Place', 'value' => 'place', 'description' => ''));
            array_push($defaultCampaignawardstyles, array('name' => 'Donation Level', 'value' => 'level', 'description' => 'award received if (Teacher/Student) reach donation amount'));

            foreach ($defaultCampaignawardstyles as $defaultCampaignawardstyle) {
                $em = $this->getDoctrine()->getManager();

                $campaignawardstyle = new Campaignawardstyle();
                $campaignawardstyle->setDisplayName($defaultCampaignawardstyle['name']);
                $campaignawardstyle->setValue($defaultCampaignawardstyle['value']);
                $campaignawardstyle->setDescription($defaultCampaignawardstyle['description']);

                $em->persist($campaignawardstyle);
                $em->flush();
            }
        }

        $em->clear();

        $campaignawards = $em->getRepository('AppBundle:'.$entity)->findAll();

        return $this->render(strtolower($entity).'/index.html.twig', array(
            'campaignawards' => $campaignawards,
            'entity' => $entity,
        ));
    }





    /**
     * Lists all Awards for teachers.
     *
     * @Route("/awards", name="public_teacher_awards")
     * @Method({"GET", "POST"})
     */
    public function TeacherAwardsAction($campaignUrl)
    {
      $logger = $this->get('logger');
      $limit = 3;
      $em = $this->getDoctrine()->getManager();
      $campaign =  $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      $queryHelper = new QueryHelper($em, $logger);
      $campaignSettings = new CampaignHelper($this->getDoctrine()->getRepository('AppBundle:Campaignsetting')->findAll());
      $reportDate = $queryHelper->convertToDay(new DateTime());
      $reportDate->modify('-1 day');

      // replace this example code with whatever you need
      return $this->render('default/teacherAwards.html.twig', array(
        'campaign_settings' => $campaignSettings->getCampaignSettings(),
        'teachers' => $queryHelper->getTeacherAwards(array('campaign' => $campaign, 'before_date' => $reportDate)),
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
