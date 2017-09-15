<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Grade;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Utils\QueryHelper;
use \DateTime;
use \DateTimeZone;


/**
 * Grade controller.
 *
 * @Route("/{campaignUrl}/grades")
 */
class GradeController extends Controller
{
    /**
     * Lists all Grade entities.
     *
     * @Route("/", name="grade_index")
     * @Method("GET")
     */
    public function indexAction($campaignUrl)
    {
        $entity = 'Grade';
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

        $grades = $em->getRepository('AppBundle:Grade')->findBy(array("campaign"=>$campaign));


        return $this->render('campaign/grade.index.html.twig', array(
            'grades' => $grades,
            'entity' => $entity,
            'campaign' => $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl),
        ));
    }


    /**
     * Finds and displays a Grade entity.
     *
     * @Route("/show/{id}", name="grade_show")
     * @Method("GET")
     */
    public function showAction(Grade $grade, $campaignUrl)
    {
        $entity = 'Grade';
        $em = $this->getDoctrine()->getManager();

        return $this->render('campaign/grade.show.html.twig', array(
            'grade' => $grade,
            'entity' => $entity,
            'campaign' => $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl),
        ));
    }



    private function clean($string)
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with underscores.
   $string = preg_replace('/[^A-Za-z0-9\_]/', '', $string); // Removes special chars.
   return strtolower($string);
    }
}
