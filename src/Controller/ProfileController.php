<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Entity\User;
use App\Entity\Team;
use App\Entity\Campaign;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
   * @Route("/", name="profile_show", methods={"GET", "POST"})
   * 
   */
    public function indexAction(Request $request, $campaignUrl, LoggerInterface $logger)
    {

        
        $em = $this->getDoctrine()->getManager();

        $campaign = $em->getRepository(Campaign::class)->findOneByUrl($campaignUrl);

        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $em->getRepository(User::class)->find($this->get('security.token_storage')->getToken()->getUser()->getId());


        if(null !== $request->query->get('action')){
            $action = $request->query->get('action');
            if($action === 'resend_email_confirmation'){
              return $this->redirectToRoute('confirm_email', array('campaignUrl'=>$campaign->getUrl(), 'action' => 'resend_email_confirmation'));
            }
        }

        $team = $em->getRepository(Team::class)->findOneBy(array('campaign'=>$campaign,'user'=>$user));
        return $this->render('profile/profile.show.html.twig',
            array(
              'user' => $user,
              'campaign' => $campaign,
              'team' => $team,
            )
        );
    }


    /**
     * Finds and displays users Profile entity.
     *
     * @Route("/change_password", name="profile_change_password")
     *
     */
      public function changePasswordAction(Request $request, $campaignUrl, LoggerInterface $logger)
      {

          
          $em = $this->getDoctrine()->getManager();

          $campaign = $em->getRepository(Campaign::class)->findOneByUrl($campaignUrl);

          $this->denyAccessUnlessGranted('ROLE_USER');
          $user = $em->getRepository(User::class)->find($this->get('security.token_storage')->getToken()->getUser()->getId());


           if ($request->isMethod('POST')) {
               $fail = false;
               $params = $request->request->all();
               $encoder = $this->container->get('security.password_encoder');

               if(!$fail && empty($params['user']['password']['current']) || $params['user']['password']['current'] == ""){
                 $this->addFlash('warning','Please enter current password');
                 $fail = true;
               }else{
                 if (!$encoder->isPasswordValid($user, $params['user']['password']['current'])) {
                   $this->addFlash('warning','Current Password does not match');
                   $fail = true;
                 }else{
                   $currentPassword = $params['user']['password']['current'];
                 }
               }

               if(!$fail && empty($params['user']['password']['first']) || $params['user']['password']['first'] == ""){
                 $this->addFlash('warning','Please enter new password');
                 $fail = true;
               }else{
                 $newPassword = $params['user']['password']['first'];
                 $encodedPassword = $encoder->encodePassword($user, $newPassword);
               }

               if(!$fail && empty($params['user']['password']['second']) || $params['user']['password']['second'] == ""){
                 $this->addFlash('warning','Please enter new password');
                 $fail = true;
               }else{
                 if($newPassword !== $params['user']['password']['second']){
                   $this->addFlash('warning','New Passwords do not match');
                   $fail = true;
                 }
               }

               if(!$fail && $encoder->isPasswordValid($user, $newPassword)) {
                 $this->addFlash('warning','New password cannot be the same as the current password.');
                 $fail = true;
               }

               if(!$fail){
                 $logger->debug("Success!");
                 $this->addFlash('success','Password successfully changed');
                 $user->setPassword($encodedPassword);
                 $em->persist($user);
                 $em->flush();

                 return $this->redirectToRoute('profile_show', array('campaignUrl'=>$campaign->getUrl()));

               }

           }



          return $this->render('profile/profile.change_password.html.twig',
              array(
                'user' => $user,
                'campaign' => $campaign
              )
          );
      }


}
