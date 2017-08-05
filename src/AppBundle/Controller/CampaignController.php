<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Entity\Teacher;
use AppBundle\Utils\QueryHelper;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use DateTime;

/**
 * Manage Campaign controller.
 *
 * @Route("/{campaignUrl}")
 */
class CampaignController extends Controller
{

  /**
   * @Route("/", name="campaign_index")
   */
   public function campaignIndexAction($campaignUrl)
   {

     $logger = $this->get('logger');
     $limit = 3;
     $em = $this->getDoctrine()->getManager();

     $campaign =  $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);

     if(count($campaign) == 0){
       return $this->redirectToRoute('homepage', array('action' => 'list_campaigns'));
     }


     $queryHelper = new QueryHelper($em, $logger);
     $campaignSettings = new CampaignHelper($this->getDoctrine()->getRepository('AppBundle:Campaignsetting')->findAll());
     $causevoxteams = $em->getRepository('AppBundle:Causevoxteam')->findAll();
     $causevoxfundraisers = $em->getRepository('AppBundle:Causevoxfundraiser')->findAll();

     $reportDate = $queryHelper->convertToDay(new DateTime());
     $reportDate->modify('-1 day');

     // replace this example code with whatever you need
     return $this->render('campaign/dashboard.html.twig', array(
       'campaign_settings' => $campaignSettings->getCampaignSettings(),
       'new_teacher_awards' => $queryHelper->getTeacherAwards(array('campaign' => $campaign, 'before_date' => $reportDate, 'limit' => 5, 'order_by' => array('field' => 'donated_at',  'order' => 'asc'))),
       'teacher_rankings' => $queryHelper->getTeacherRanks(array('campaign' => $campaign,'limit'=> $limit, 'before_date' => $reportDate)),
       'report_date' => $reportDate,
       'ranking_limit' => $limit,
       'causevoxteams' => $causevoxteams,
       'causevoxfundraisers' => $causevoxfundraisers,
       'student_rankings' => $queryHelper->getStudentRanks(array('campaign' => $campaign,'limit'=> $limit, 'before_date' => $reportDate)),
       'totals' => $queryHelper->getTotalDonations(array('campaign' => $campaign,'before_date' => $reportDate)),
       'campaign' => $campaign
     ));


  }




    /**
     * Finds and displays a Campaign entity.
     *
     * @Route("/show/{id}", name="campaign_show")
     * @Method("GET")
     */
    public function showAction(Campaign $campaign)
    {
        $logger = $this->get('logger');
        $entity = 'Campaign';
        $deleteForm = $this->createDeleteForm($campaign);

        return $this->render('campaign/show.html.twig', array(
            'campaign' => $campaign,
            'delete_form' => $deleteForm->createView(),
            'entity' => $entity,
        ));
    }


    /**
     * @Route("/faq", name="campaign_faq")
     */
    public function faqAction($campaignUrl)
    {
      $logger = $this->get('logger');
      $em = $this->getDoctrine()->getManager();
      $queryHelper = new QueryHelper($em, $logger);
      $campaign =  $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      $campaignSettings = new CampaignHelper($this->getDoctrine()->getRepository('AppBundle:Campaignsetting')->findAll());
      $causevoxteams = $em->getRepository('AppBundle:Causevoxteam')->findAll();
      $causevoxfundraisers = $em->getRepository('AppBundle:Causevoxfundraiser')->findAll();

      return $this->render('campaign/campaign.faq.html.twig', array(
        'campaign_settings' => $campaignSettings->getCampaignSettings(),
        'causevoxteams' => $causevoxteams,
        'causevoxfundraisers' => $causevoxfundraisers,
        'campaign' => $campaign,
      ));
    }



    /**
     * @Route("/terms_of_service", name="campaign_terms_of_service")
     */
    public function termsOfServiceAction($campaignUrl)
    {
      $logger = $this->get('logger');
      $em = $this->getDoctrine()->getManager();

      $campaign =  $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);

      return $this->render('default/termsOfService.html.twig', array(
        'campaign' => $campaign,
      ));
    }


}
