<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use AppBundle\Entity\Campaign;

use DateTime;

class CampaignSubscriber implements EventSubscriberInterface
{
    private $container;
    private $router;
    private $em;
    private $logger;

    public function __construct($router, $container, $em, $logger)
    {
      $this->router = $router;
      $this->container = $container;
      $this->em = $em;
      $this->logger = $logger;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        //$this->logger->debug(print_r($controller, true));

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        $campaignUrl = $event->getRequest()->get('campaignUrl');
        if(is_null($campaignUrl)){
          return;
        }else{
          $this->logger->debug("CampaignSubscriber found campaignURL:".$campaignUrl);
        }

        //TODO: Add logic to account for campaigns that users want a demo of (Offline but registered)
        //This will most likely require a login force of somesort.

        
        $campaign = $this->em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
        if(is_null($campaign)){
          $this->logger->debug("CampaignSubscriber did not find campaign: ".$campaignUrl);
          $this->container->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
          $redirectUrl = $this->router->generate('homepage', array('action'=>'list_campaigns'));
          $event->setController(function() use ($redirectUrl) {
              return new RedirectResponse($redirectUrl);
          });
        }elseif(!$campaign->getOnlineFlag()){
          $securityContext = $this->container->get('security.authorization_checker');
          //If it is offline, is a user logged in? If not, fail
          if ($securityContext->isGranted('ROLE_USER')) {
            $campaignHelper = new CampaignHelper($em, $logger);
            //Does that user have access to the campaign? If not, fail
            if(!$campaignHelper->campaignPermissionsCheck($this->get('security.token_storage')->getToken()->getUser(), $campaign)){
              $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
              $redirectUrl = $this->router->generate('homepage', array('action'=>'list_campaigns'));
              $event->setController(function() use ($redirectUrl) {
                  return new RedirectResponse($redirectUrl);
              });
            }
            }else{
              $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
              $redirectUrl = $this->router->generate('homepage', array('action'=>'list_campaigns'));
              $event->setController(function() use ($redirectUrl) {
                  return new RedirectResponse($redirectUrl);
              });
            }
        }elseif($campaign->getStartDate() > new DateTime("now")){
            $redirectUrl = $this->router->generate('campaign_splash', array('campaignUrl'=>$campaign->getUrl(), 'campaign'=>$campaign));
            $event->setController(function() use ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            });
        }

    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}
