<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Entity\Campaign;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setApiKey($password);
            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
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
}
