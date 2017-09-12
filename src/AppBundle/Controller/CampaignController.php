<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Entity\Classroom;
use AppBundle\Utils\QueryHelper;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use \DateTime;
use \DateTimeZone;

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


     $queryHelper = new QueryHelper($em, $logger);

     $causevoxteams = $em->getRepository('AppBundle:Causevoxteam')->findAll();
     $causevoxfundraisers = $em->getRepository('AppBundle:Causevoxfundraiser')->findAll();

     $reportDate = $queryHelper->convertToDay(new DateTime());
     $reportDate->modify('-1 day');

     // replace this example code with whatever you need
     return $this->render('campaign/dashboard.html.twig', array(
       'new_classroom_awards' => $queryHelper->getClassroomAwards(array('campaign' => $campaign, 'before_date' => $reportDate, 'limit' => 5, 'order_by' => array('field' => 'donated_at',  'order' => 'asc'))),
       'classroom_rankings' => $queryHelper->getClassroomRanks(array('campaign' => $campaign,'limit'=> $limit, 'before_date' => $reportDate)),
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
   * Displays Coming Soon Splash Page
   *
   * @Route("/coming_soon", name="campaign_splash")
   * @Method("GET")
   */
  public function spashAction($campaignUrl)
  {
      $logger = $this->get('logger');

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
      }


      return $this->render('campaign/campaign.splash.html.twig', array(
          'campaign' => $campaign,
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

      return $this->render('campaign/campaign.faq.html.twig', array(
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
