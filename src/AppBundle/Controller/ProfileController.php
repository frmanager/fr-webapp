<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use \DateTime;
use \DateTimeZone;

/**
 * Profile controller.
 *
 * @Route("/{campaignUrl}/profile")
 */
class ProfileController extends Controller
{

  /**
   * Finds and displays users Profile entity.
   *
   * @Route("/", name="profile_show")
   * @Method("GET")
   */
    public function indexAction(Request $request, $campaignUrl)
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
        }elseif($campaign->getStartDate() > new DateTime("now")){
          return $this->redirectToRoute('campaign_splash', array('campaignUrl'=>$campaign->getUrl(), 'campaign'=>$campaign));
        }

        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $em->getRepository('AppBundle:User')->find($this->get('security.token_storage')->getToken()->getUser()->getId());

        if(null !== $request->query->get('action')){
            $action = $request->query->get('action');
            if($action === 'resend_email_confirmation'){
              return $this->redirectToRoute('confirm_email', array('campaignUrl'=>$campaign->getUrl(), 'action' => 'resend_email_confirmation'));
            }
        }


        return $this->render('profile/profile.show.html.twig',
            array(
              'user' => $user,
              'campaign' => $campaign
            )
        );
    }


}
