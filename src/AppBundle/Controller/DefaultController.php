<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Entity\Teacher;
use AppBundle\Utils\QueryHelper;

use DateTime;

class DefaultController extends Controller
{


  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request)
  {
        $logger = $this->get('logger');

        if(null !== $request->query->get('action')){
            $action = $request->query->get('action');


            if($action === 'list_campaigns'){
                $entity = 'Campaign';
                $em = $this->getDoctrine()->getManager();

                return $this->render('campaign/campaign.index.html.twig', array(
                    'campaigns' => $em->getRepository('AppBundle:Campaign')->findAll(),
                    'entity' => $entity,
                ));
            }else{
              return $this->render('default/homepage.html.twig');
            }

        }else{
          return $this->render('default/homepage.html.twig');
        }
  }



}
