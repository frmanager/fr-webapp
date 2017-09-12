<?php
// src/AppBundle/Controller/PaymentController.php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentCard;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;
use AppBundle\Utils\CampaignHelper;

/**
 * Manage Campaign controller.
 *
 * @Route("/{campaignUrl}/donation")
 */
class DonationController extends Controller
{


  /**
   * @Route("/", name="donation_index")
   *
   * @Method({"GET", "POST"})
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


    /*
    * There are 4 types of donations:
    * team = Donation to a specific team
    * classroom = Donation to a specific classroom
    * campaign = Donation to the campaign (Defaulted if type is null)
    * student = Donation to a specific student
    *
    */
    $donation = array();
    if(null !== $request->query->get('type')){
      $donationType = $request->query->get('type');
      if($donationType == 'classroom' || $donationType == 'student'){
        if(null !== $request->query->get('classroomID')){
            $classroomID = $request->query->get('classroomID');
            $logger->debug("ClassroomID is found and set to ".$classroomID);
            //CODE TO CHECK TO SEE IF CLASSROOM EXISTS
            $classroom = $em->getRepository('AppBundle:Classroom')->findOneBy(array('id'=>$classroomID, 'campaign' => $campaign));
            if(!is_null($classroom)){
              $logger->debug("Using Classroom #".$classroom->getId()." in donation form.");
              $donation['classroom'] = $classroom;
            }
          }
      } elseif($donationType == 'team'){
          if(null !== $request->query->get('teamUrl')){
              $teamUrl = $request->query->get('teamUrl');
              $logger->debug("teamUrl is found and set to ".$teamUrl);
              //CODE TO CHECK TO SEE IF TEAM EXISTS
              $team = $em->getRepository('AppBundle:Team')->findOneBy(array('url'=>$teamUrl, 'campaign' => $campaign));
              if(!is_null($team)){
              $logger->debug("Using Team #".$team->getId()." in donation form.");
                $donation['team'] = $team;
              }
            }
    }
    }else{
      $donationType = 'campaign';
    }

    $logger->debug("Donation Object:", $donation);

    // replace this example code with whatever you need
    return $this->render('donation/donation.index.html.twig', array(
      'donation' => $donation,
      'donationType' => $donationType,
      'teams' => $em->getRepository('AppBundle:Team')->findByCampaign($campaign),
      'classrooms' => $em->getRepository('AppBundle:Classroom')->findByCampaign($campaign),
      'campaign' => $campaign,
    ));



  }




    /**
     * @Route("/prepare", name="donation_prepare")
     *
     */
    public function prepareAction(Request $request, $campaignUrl)
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





      $apiContext = new \PayPal\Rest\ApiContext(
              new \PayPal\Auth\OAuthTokenCredential(
                  $this->getParameter('paypal.paypal_rest.client_id'),     // ClientID
                  $this->getParameter('paypal.paypal_rest.client_secret')      // ClientSecret
              )
      );

      $payer = new Payer();
      $payer->setPaymentMethod("paypal");

      $item1 = new Item();
      $item1->setName('Ground Coffee 40 oz')
          ->setCurrency('USD')
          ->setQuantity(1)
          ->setSku("123123") // Similar to `item_number` in Classic API
          ->setPrice(7.5);
      $item2 = new Item();
      $item2->setName('Granola bars')
          ->setCurrency('USD')
          ->setQuantity(5)
          ->setSku("321321") // Similar to `item_number` in Classic API
          ->setPrice(2);

      $itemList = new ItemList();
      $itemList->setItems(array($item1, $item2));

      $details = new Details();
      $details->setShipping(1.2)
          ->setTax(1.3)
          ->setSubtotal(17.50);

      $amount = new Amount();
      $amount->setCurrency("USD")
          ->setTotal(20)
          ->setDetails($details);

      $transaction = new Transaction();
      $transaction->setAmount($amount)
          ->setItemList($itemList)
          ->setDescription("Payment description")
          ->setInvoiceNumber(uniqid());

      $redirectUrls = new RedirectUrls();

      $successRoute = $this->get('router')->generate('donation_done', array('campaignUrl' => $campaignUrl, "success"=>true));
      $failRoute = $this->get('router')->generate('donation_done', array('campaignUrl' => $campaignUrl, "success"=>false));

      $redirectUrls->setReturnUrl("http://fr-webapp".$successRoute)
          ->setCancelUrl("http://fr-webapp".$failRoute);


      $payment = new Payment();
      $payment->setIntent("sale")
          ->setPayer($payer)
          ->setRedirectUrls($redirectUrls)
          ->setTransactions(array($transaction));

      $request = clone $payment;

      try {
          $payment->create($apiContext);
      } catch (Exception $ex) {
          exit(1);
      }

      $approvalUrl = $payment->getApprovalLink();

      return $this->redirect($approvalUrl);

    }


    /**
     * @Route("/done", name="donation_done")
     *
     */
    public function doneAction(Request $request, $campaignUrl)
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

        //IF CAMPAIGN CHECK FAILED
        if($accessFail){
          $this->get('session')->getFlashBag()->add('warning', 'We are sorry, we could not find this campaign.');
          return $this->redirectToRoute('homepage', array('action'=>'list_campaigns'));
        }


        $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    $this->getParameter('paypal.paypal_rest.client_id'),     // ClientID
                    $this->getParameter('paypal.paypal_rest.client_secret')      // ClientSecret
                )
        );

        $logger->debug("Looking for success parameter");
        if(null !== $request->query->get('success')){
          $logger->debug("Found success parameter");
          $successFlag = $request->query->get('success');

          if ($successFlag == true) {
              $logger->debug("Payment was a success");
              $paymentId = $request->query->get('paymentId');
              $payment = Payment::get($paymentId, $apiContext);

              $execution = new PaymentExecution();
              $execution->setPayerId($request->query->get('PayerID'));

              try {
                  $result = $payment->execute($execution, $apiContext);

                  try {
                      $payment = Payment::get($paymentId, $apiContext);
                  } catch (Exception $ex) {
                      exit(1);
                  }
              } catch (Exception $ex) {
                  exit(1);
              }
              $logger->debug($payment);

              return $this->render('donation/donation.success.html.twig', array(
                  'campaign' => $campaign,
                  'paymentDetails' => $payment
              ));


          } else {
              exit;
          }

        }

    }
}
