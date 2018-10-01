<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\CampaignHelper;
use App\Entity\Campaign;
use App\Entity\Classroom;
use App\Utils\QueryHelper;

use \DateTime;
use \DateTimeZone;

class DefaultController extends Controller
{


  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request, LoggerInterface $logger)
  {
        

        if(null !== $request->query->get('action')){
            $action = $request->query->get('action');

            if($action === 'list_campaigns'){
                $entity = 'Campaign';
                $em = $this->getDoctrine()->getManager();

                return $this->render('campaign/campaign.list.html.twig', array(
                    'campaigns' => $em->getRepository(Campaign::class)->findBy(array('onlineFlag'=>true)),
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
