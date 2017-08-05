<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Team;
use AppBundle\Entity\Grade;
use AppBundle\Utils\ValidationHelper;
use AppBundle\Utils\CSVHelper;
use AppBundle\Utils\CampaignHelper;
use AppBundle\Utils\QueryHelper;
use DateTime;

/**
 * Team controller.
 *
 * @Route("/{campaignUrl}/team")
 */
class TeamController extends Controller
{
  /**
   * Lists all Team entities.
   *
   * @Route("/", name="team_index")
   * @Method({"GET", "POST"})
   */
  public function teamIndexAction($campaignUrl)
  {
      $logger = $this->get('logger');
      $entity = 'Team';
      $em = $this->getDoctrine()->getManager();

      //CODE TO CHECK TO SEE IF CAMPAIGN EXISTS
      $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      if(is_null($campaign)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
        return $this->redirectToRoute('homepage');
      }

      $queryHelper = new QueryHelper($em, $logger);
      $tempDate = new DateTime();
      $dateString = $tempDate->format('Y-m-d').' 00:00:00';
      $reportDate = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
      // replace this example code with whatever you need
      return $this->render('campaign/team.index.html.twig', array(
        'teams' => $queryHelper->getTeamRanks(array('campaign' => $campaign, 'limit'=> 0)),
        'entity' => strtolower($entity),
        'campaign' => $campaign,
      ));

  }

    /**
     * Finds and displays a Team entity.
     *
     * @Route("/{id}", name="team_show")
     * @Method("GET")
     */
    public function showAction(Team $team, $campaignUrl)
    {
        $logger = $this->get('logger');
        $entity = 'Team';
        $team = $this->getDoctrine()->getRepository('AppBundle:'.strtolower($entity))->findOneById($team->getId());
        //$logger->debug(print_r($student->getDonations()));
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder()->select('u')
               ->from('AppBundle:Campaignaward', 'u')
               ->orderBy('u.amount', 'DESC');

        $campaignAwards = $qb->getQuery()->getResult();
        $campaignSettings = new CampaignHelper($this->getDoctrine()->getRepository('AppBundle:Campaignsetting')->findAll());
        $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
        $queryHelper = new QueryHelper($em, $logger);

        return $this->render('/campaign/team.show.html.twig', array(
            'team' => $team,
            'team_rank' => $queryHelper->getTeamRank($team->getId(),array('campaign' => $campaign, 'limit' => 0)),
            'campaign_awards' => $campaignAwards,
            'campaignsettings' => $campaignSettings->getCampaignSettings(),
            'entity' => $entity,
            'campaign' => $campaign,
        ));
    }


    /**
     * @Route("/register_team", name="register_team")
     *
     */
    public function registerFundraiserTeamAction(Request $request, $campaignUrl)
    {

      $logger = $this->get('logger');
      $this->denyAccessUnlessGranted('ROLE_USER');

      $em = $this->getDoctrine()->getManager();
      $campaign =  $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);


      //CODE TO CHECK TO SEE IF CAMPAIGN EXISTS
      $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      if(is_null($campaign)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
        return $this->redirectToRoute('homepage');
      }


      if ($this->getRequest()->isMethod('POST')) {

          $team = new Team();

          $em = $this->getDoctrine()->getManager();
          $em->persist($team);
          $em->flush();

          return $this->redirectToRoute('team_view', array('url' => $team->getUrl()));
      }


      return $this->render('default/termsOfService.html.twig', array(
        'campaign' => $campaign,
      ));
    }


}
