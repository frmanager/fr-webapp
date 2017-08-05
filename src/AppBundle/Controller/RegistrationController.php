<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\Campaign;
use AppBundle\Entity\CampaignUser;
use AppBundle\Entity\UserStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use DateTime;

/**
 * Security controller.
 *
 * @Route("/{campaignUrl}")
 */
class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $logger = $this->get('logger');
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);


        $campaign = null;

        if(!empty($request->attributes->get('_route_params'))){
          $routeParams = $request->attributes->get('_route_params');
          if (array_key_exists('campaignUrl', $routeParams)){
            $em = $this->getDoctrine()->getManager();
            $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($routeParams['campaignUrl']);
          }
        }


        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setApiKey($password);
            $user->setUsername($user->getEmail());
            $user->setEmailConfirmationCode(base64_encode(random_bytes(20)));
            $user->setEmailConfirmationCodeTimestamp(new \DateTime());
            //Get User Status
            $userStatus = $em->getRepository('AppBundle:UserStatus')->findOneByName('Registered');

            if(!empty($userStatus)){
              $logger->debug('UserStatus of Registered could not be found');
            }

            $user->setUserStatus($userStatus);


            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            //Create CampaignUser
            $campaignUser = new CampaignUser();
            $campaignUser->setUser($user);
            $campaignUser->setCampaign($campaign);
            $em->persist($campaignUser);


            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('campaign_index', array('campaignUrl' => $campaign->getUrl()));
        }

        return $this->render('registration/register.html.twig',
            array(
              'form' => $form->createView(),
              'campaign' => $campaign
            )
        );
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
      $teamTypes =  $em->getRepository('AppBundle:TeamType')->findAll();

      //CODE TO CHECK TO SEE IF CAMPAIGN EXISTS
      $campaign = $em->getRepository('AppBundle:Campaign')->findOneByUrl($campaignUrl);
      if(is_null($campaign)){
        $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
        return $this->redirectToRoute('homepage');
      }

      if ($request->isMethod('POST')) {

          $team = new Team();
          $em = $this->getDoctrine()->getManager();
          $em->persist($team);
          $em->flush();

          return $this->redirectToRoute('team_view', array('url' => $team->getUrl()));
      }

      return $this->render('registration/team.new.html.twig', array(
        'campaign' => $campaign,
        'teamTypes' => $teamTypes
      ));
    }

}
