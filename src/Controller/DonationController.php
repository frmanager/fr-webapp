<?php
// src/AppBundle/Controller/PaymentController.php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Address;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payee;
use PayPal\Api\Payment;
use PayPal\Api\PaymentCard;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Capture;
use PayPal\Api\PaymentExecution;
use PayPal\Exception\PayPalConnectionException;
use App\Utils\CampaignHelper;
use App\Entity\Donation;
use App\Utils\DonationHelper;
use App\Entity\Classroom;
use App\Entity\Team;
use App\Entity\Campaign;


use \DateTime;
use \DateTimeZone;

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
  public function indexAction(Request $request, $campaignUrl, LoggerInterface $logger)
  {
    

    $em = $this->getDoctrine()->getManager();

    $campaign = $em->getRepository(Campaign::class)->findOneByUrl($campaignUrl);

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
            $classroom = $em->getRepository(Classroom::class)->findOneBy(array('id'=>$classroomID, 'campaign' => $campaign));
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
              $team = $em->getRepository(Team::class)->findOneBy(array('url'=>$teamUrl, 'campaign' => $campaign));
              if(!is_null($team)){
              $logger->debug("Using Team #".$team->getId()." in donation form.");
                $donation['team'] = $team;
              }
            }
    }
    }else{
      $donationType = 'campaign';
    }


    if ($request->isMethod('POST')) {
        $fail = false;
        $params = $request->request->all();

        if(!$fail && empty($params['donation']['amount'])){
          $this->addFlash('warning','Donation amount is required');
          $fail = true;
        }

        if(!$fail && empty($params['donation']['firstName'])){
          $this->addFlash('warning','Donor First name is required');
          $fail = true;
        }

        if(!$fail && empty($params['donation']['lastName'])){
          $this->addFlash('warning','DonorLast name is required');
          $fail = true;
        }

        if(!$fail && empty($params['donation']['email'])){
          $this->addFlash('warning','Donor Email address is required');
          $fail = true;
        }

        if (!$fail && filter_var($params['donation']['email'], FILTER_VALIDATE_EMAIL) == false) {
          $this->addFlash('warning','This is not a valid Email Address');
          $fail = true;
        }


        if (!$fail && $donationType == 'team'){
          if(empty($params['donation']['teamId'])){
            $this->addFlash('warning','No Team was selected');
            $fail = true;
          }
          if(!$fail){
            $teamCheck = $em->getRepository(Team::class)->findOneBy(array('id'=>$params['donation']['teamId'], 'campaign' => $campaign));
            if(is_null($teamCheck)){
              $this->addFlash('warning','No Valid Team was selected');
              $fail = true;
            }
          }
        }else if (!$fail && $donationType == 'classroom'){
          if(empty($params['donation']['classroomID']) || $params['donation']['classroomID'] == ""){
            $this->addFlash('warning','No Classroom was selected');
            $fail = true;
          }
          if(!$fail){
            $classroomCheck = $em->getRepository(Classroom::class)->findOneBy(array('id'=>$params['donation']['classroomID'], 'campaign' => $campaign));
            if(is_null($classroomCheck)){
              $this->addFlash('warning','No Valid Classroom was selected');
              $fail = true;
            }
          }
        }else if (!$fail && $donationType == 'student'){
          if(empty($params['donation']['classroomID']) || $params['donation']['classroomID'] == ""){
            $this->addFlash('warning','No Classroom was selected');
            $fail = true;
          }

          if(!$fail){
            $classroomCheck = $em->getRepository(Classroom::class)->findOneBy(array('id'=>$params['donation']['classroomID'], 'campaign' => $campaign));
            if(is_null($classroomCheck)){
              $this->addFlash('warning','No Valid Classroom was selected');
              $fail = true;
            }
          }

          if(empty($params['donation']['studentName'])){
            $this->addFlash('warning','Student Name is required');
            $fail = true;
          }

        }

        if(!$fail && empty($params['donation']['paymentMethod'])){
          $this->addFlash('warning','Payment method must be requested');
          $fail = true;
        }

        $paymentMethod = $params['donation']['paymentMethod'];
        if(!$fail && $paymentMethod == "cc"){

          if(!$fail && $this->container->hasParameter('feature.credit_card') && $this->container->getParameter('feature.credit_card') == false){
            $this->addFlash('warning','We are not currently accepting Credit Card Donations');
            $fail = true;
          }

          if(!$fail && empty($params['donation']['cc']['cardholderFirstName'])){
            $this->addFlash('warning','Cardholder First Name is required');
            $fail = true;
          }

          if(!$fail && empty($params['donation']['cc']['cardholderLastName'])){
            $this->addFlash('warning','Cardholder Last Name is required');
            $fail = true;
          }

          if(!$fail && empty($params['donation']['cc']['country'])){
            $this->addFlash('warning','Country Code is required');
            $fail = true;
          }

          if(!$fail && empty($params['donation']['cc']['number'])){
            $this->addFlash('warning','Card Number is required');
            $fail = true;
          }else{
              /* The following code is based upon:
              * https://stackoverflow.com/questions/72768/how-do-you-detect-credit-card-type-based-on-number
              * http://www.regular-expressions.info/creditcard.html
              */
              if(preg_match('/^4[0-9]{6,}$/', $params['donation']['cc']['number'])){
                $logger->debug('Processing a VISA Card');
                $cardType = 'visa';
              }else if(preg_match('/^5[1-5][0-9]{5,}|222[1-9][0-9]{3,}|22[3-9][0-9]{4,}|2[3-6][0-9]{5,}|27[01][0-9]{4,}|2720[0-9]{3,}$/', $params['donation']['cc']['number'])){
                $logger->debug('Card was a MasterCard');
                $cardType = 'mastercard';
              }else if(preg_match('/^3[47][0-9]{5,}$/', $params['donation']['cc']['number'])){
                $logger->debug('Processing a AMEX Card');
                $cardType = 'amex';
              }else if(preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{4,}$/', $params['donation']['cc']['number'])){
                $logger->debug('Processing a Diners Club Card');
                $cardType = 'dinersclub';
              }else if(preg_match('/^6(?:011|5[0-9]{2})[0-9]{3,}$/', $params['donation']['cc']['number'])){
                $logger->debug('Processing a Discover Card');
                $cardType = 'discover';
              }else if(preg_match('/^(?:2131|1800|35[0-9]{3})[0-9]{3,}$/', $params['donation']['cc']['number'])){
                $logger->debug('Processing a JCB Card');
                $cardType = 'jcb';
              }else{
                $logger->debug('Could not find card match, defaulting to MasterCard');
                $cardType = 'mastercard';
              }
          }

          if(!$fail && empty($params['donation']['cc']['zipCode'])){
            $this->addFlash('warning','Zip Code is required');
            $fail = true;
          }

          if(!$fail && empty($params['donation']['cc']['expirationMonth'])){
            $this->addFlash('warning','Card Expiration Month is required');
            $fail = true;
          }

          if(!$fail && empty($params['donation']['cc']['expirationYear'])){
            $this->addFlash('warning','Card Expiration Year is required');
            $fail = true;
          }

          if(!$fail && empty($params['donation']['cc']['cvv'])){
            $this->addFlash('warning','Card CVV is required');
            $fail = true;
          }

        }

        if(!$fail){
          $donation = new Donation();
          $donation->setCampaign($campaign);
          $donation->setTransactionId(strtoupper(md5(uniqid(rand(), true))));
          $donation->setAmount($params['donation']['amount']);
          $donation->setDonationStatus("PENDING");
          $donation->setDonorFirstName($params['donation']['firstName']);
          $donation->setDonorLastName($params['donation']['lastName']);
          $donation->setDonorEmail($params['donation']['email']);
          $donation->setDonatedAt(new DateTime('now'));
          if(!empty($params['donation']['message'])){
            $donation->setDonorComment($params['donation']['message']);
          }
          if($donationType == "team"){
              $donation->setTeam($teamCheck);
          }elseif($donationType == "classroom"){
              $donation->setClassroom($classroomCheck);
          }elseif($donationType == "student"){
              $donation->setStudentName($params['donation']['studentName']);
              $donation->setClassroom($classroomCheck);
              //TODO: Add code to try and auto-validate studentName
          }
          $donation->setType($donationType);
          $donation->setPaymentMethod($params['donation']['paymentMethod']);
          $em->persist($donation);
          $em->flush();


          /*NOW WE START THE PAYPAL STUFF */

          $apiContext = new \PayPal\Rest\ApiContext(
                  new \PayPal\Auth\OAuthTokenCredential(
                      $this->getParameter('paypal_rest_client_id'),     // ClientID
                      $this->getParameter('paypal_rest_client_secret')      // ClientSecret
                  )
          );

          if($this->container->getParameter('kernel.environment') == "dev" || $this->container->getParameter('kernel.environment') == "test"){
            $apiContext->setConfig(array('mode' => 'sandbox'));
            $logger->debug('Mode set to sandbox');

          }else{
            $apiContext->setConfig(array('mode' => 'live'));
            $logger->debug('Mode set to live');
          }

          //IF A PAYPAL PAYMENT
          if($params['donation']['paymentMethod'] == "paypal"){
            $item1 = new Item();
            $item1->setName('Donation to '.$campaign->getName())
                ->setCurrency('USD')
                ->setQuantity(1)
                ->setPrice($donation->getAmount());

            $itemList = new ItemList();
            $itemList->setItems(array($item1));

            $details = new Details();
            $details->setTax(0)
                ->setSubtotal($donation->getAmount());

            $amount = new Amount();
            $amount->setCurrency("USD")
                ->setTotal($donation->getAmount())
                ->setDetails($details);

            //Here we are making sure the campaign gets the donation, not us!
            $payee = new Payee();
            $payee->setEmail($campaign->getPaypalEmail());

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription('Donation to '.$campaign->getName())
                ->setPayee($payee)
                ->setInvoiceNumber($donation->getTransactionId());

            //TODO: Create "Batch" Payout to receive funds https://paypal.github.io/PayPal-PHP-SDK/sample/doc/payouts/CreateBatchPayout.html

            $payer = new Payer();

            $payment = new Payment();

            $payer->setPaymentMethod("paypal");

            $redirectUrls = new RedirectUrls();

            $successRoute = $this->get('router')->generate('donation_done', array('campaignUrl' => $campaignUrl, "success"=>true, "transactionId"=>$donation->getTransactionId()));
            $failRoute = $this->get('router')->generate('donation_done', array('campaignUrl' => $campaignUrl, "success"=>false, "transactionId"=>$donation->getTransactionId()));

            $redirectUrls->setReturnUrl($this->container->getParameter('main_app_url').$successRoute)
                ->setCancelUrl($this->container->getParameter('main_app_url').$failRoute);

            $payment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));

          }else if($params['donation']['paymentMethod'] == "cc"){

            $card = new PaymentCard();
            $logger->debug("Payment Card type set to: ".$cardType);

            $card->setType($cardType)
                ->setNumber($params['donation']['cc']['number'])
                ->setExpireMonth($params['donation']['cc']['expirationMonth'])
                ->setExpireYear($params['donation']['cc']['expirationYear'])
                ->setCvv2($params['donation']['cc']['cvv'])
                ->setFirstName($params['donation']['cc']['cardholderFirstName'])
                ->setBillingCountry($params['donation']['cc']['country'])
                ->setLastName($params['donation']['cc']['cardholderLastName']);

              $fi = new FundingInstrument();
              $fi->setPaymentCard($card);

              $payer = new Payer();
              $payer->setPaymentMethod("credit_card")
                  ->setFundingInstruments(array($fi));


              $amount = new Amount();
              $amount->setCurrency("USD")
                  ->setTotal($donation->getAmount());

              //Here we are making sure the campaign gets the donation, not us!
              $payee = new Payee();
              $payee->setEmail($campaign->getPaypalEmail());

              $transaction = new Transaction();
              $transaction->setAmount($amount)
                  ->setDescription('Donation to '.$campaign->getName());

              $payment = new Payment();
              $payment->setIntent("authorize")
                  ->setPayer($payer)
                  ->setTransactions(array($transaction));

          }

         try {
              $payment->create($apiContext);

              if($params['donation']['paymentMethod'] == "cc"){
                $donation->setDonationStatus("AUTHORIZED");
                $em->persist($donation);
                $em->flush();
              }

          } catch (PayPalConnectionException $ex) {
              $logger->critical("Paypal Payment PayPalConnectionException Failure. code:".$ex->getCode()." data:".$ex->getData());
              $this->get('session')->getFlashBag()->add('danger', 'There was an issue processing the payment. Please check your information and try again');
              $donation->setDonationStatus("FAILED");
              $em->persist($donation);
              $em->flush();
              $fail = true;
          } catch (Exception $ex) {
              $donation->setDonationStatus("FAILED");
              $this->get('session')->getFlashBag()->add('danger', 'There was an issue processing the payment. Please check your information and try again');
              $em->persist($donation);
              $em->flush();
              $logger->critical("Paypal Rest API Exception Failure. code:".$ex->getCode()." data:".$ex->getData());
              $fail = true;
          }


          //IF A PAYPAL PAYMENT
          if(!$fail){
            if($params['donation']['paymentMethod'] == "paypal"){
              $approvalUrl = $payment->getApprovalLink();
              return $this->redirect($approvalUrl);
            }else {
              $transactions = $payment->getTransactions();
              $relatedResources = $transactions[0]->getRelatedResources();
              $authorization = $relatedResources[0]->getAuthorization();
              $logger->debug("Authorization: ".print_r($authorization, true));
              $donation->setPaypalPaymentDetails(json_decode($authorization, true));
              $donation->setPaypalPaymentId($authorization->getId());

              $em->persist($donation);
              $em->flush();
              $logger->debug("Capturing Payment");
              try {
                  $authId = $authorization->getId();

                  $amt = new Amount();
                  $amt->setCurrency("USD")
                      ->setTotal($donation->getAmount());

                  ### Capture
                  $capture = new Capture();
                  $capture->setAmount($amt);
                  $getCapture = $authorization->capture($capture, $apiContext);

                  $donation->setDonationStatus("ACCEPTED");
                  $em->persist($donation);
                  $em->flush();

                  } catch (Exception $ex) {
                    $logger->critical("Paypal Rest API Exception Failure. code:".$ex->getCode()." data:".$ex->getData());
                    $fail = true;
                  }

                  //return $getCapture;
                  return $this->redirectToRoute('donation_done', array('campaignUrl'=> $campaignUrl, 'success'=>true, 'transactionId'=>$donation->getTransactionId()));
            }
          }
        }
    }


    $data = array(
      'donation' => $donation,
      'donationType' => $donationType,
      'teams' => $em->getRepository(Team::class)->findByCampaign($campaign),
      'classrooms' => $em->getRepository(Classroom::class)->findByCampaign($campaign),
      'campaign' => $campaign,
    );

    if(null !== $request->query->get('teamUrl') && $donationType == "team"){
      $data['team'] = $team;
    }

    if(null !== $request->query->get('classroomID') && $donationType == "classroom"){
      $data['classroom'] = $classroom;
    }

    // replace this example code with whatever you need
    return $this->render('donation/donation.index.html.twig', $data);


  }


    /**
     * @Route("/done", name="donation_done")
     *
     */
    public function doneAction(Request $request, $campaignUrl, LoggerInterface $logger)
    {

        

        $em = $this->getDoctrine()->getManager();

        $campaign = $em->getRepository(Campaign::class)->findOneByUrl($campaignUrl);


        $failure = false;
        if(null == $request->query->get('transactionId')){
          $this->get('session')->getFlashBag()->add('info', 'Transaction ID was not provided');
          $logger->info("DONATION ISSUE: transactionId was not found. campaign_id: ".$campaign->getId()." and transaction_id: ".$transactionId);
          $failure = true;
        }else{
          $transactionId = $request->query->get('transactionId');
        }

        if(!$failure){
          $logger->debug("Looking for donation record with Transaction ID ".$transactionId);
          $donation = $em->getRepository(Donation::class)->findOneBy(array('transactionId'=>$transactionId, 'campaign' => $campaign));
        }

        if(!$failure && is_null($donation)){
          $this->get('session')->getFlashBag()->add('warning', 'We could not find the donation assocated with this transaction');
          $logger->info("DONATION ISSUE: Unable to find donation linked to campaign_id: ".$campaign->getId()." and transaction_id: ".$transactionId);
          $failure = true;
        }

        //CC transactions are already completed once they get here
        if(!$failure && $donation->getDonationStatus() == "ACCEPTED" && $donation->getPaymentMethod() == "paypal"){
          $this->get('session')->getFlashBag()->add('info', 'This donation was already processed');
          $logger->info("DONATION ISSUE: Donation was already accepted: ".$campaign->getId()." and transaction_id: ".$transactionId);
          $failure = true;
        }

        if(!$failure && $donation->getDonationStatus() == "FAILED"){
          $this->get('session')->getFlashBag()->add('info', 'This donation was already processed');
          $logger->info("DONATION ISSUE: Donation was already accepted: ".$campaign->getId()." and transaction_id: ".$transactionId);
          $failure = true;
        }

        if(!$failure && null == $request->query->get('success')){
          $logger->info("DONATION ISSUE: success was not found. campaign_id: ".$campaign->getId()." and transaction_id: ".$transactionId);
          $failure = true;
          $success = false;
        }else{
          $success = $request->query->get('success');
        }

        if(!$success){
          $this->get('session')->getFlashBag()->add('info', 'This donation was cancelled.');
          $logger->info("Donation was cancelled on the paypal side.");
          $failure = true;
        }

        //We don't need to process anything for Credit Card Transactions
        if(!$failure && $donation->getPaymentMethod() == "cc"){
          $failure = true;
        }


        if(!$failure){
          if($donation->getPaymentMethod() == "paypal"){
            if(null == $request->query->get('PayerID')){
              $logger->info("DONATION ISSUE: Paypal PayerID was not found. campaign_id: ".$campaign->getId()." and transaction_id: ".$transactionId);
              $failure = true;
            }

            if(null == $request->query->get('paymentId')){
              $logger->info("DONATION ISSUE: Paypal paymentId was not found. campaign_id: ".$campaign->getId()." and transaction_id: ".$transactionId);
              $failure = true;
            }

            if(!$failure){
              $apiContext = new \PayPal\Rest\ApiContext(
                      new \PayPal\Auth\OAuthTokenCredential(
                          $this->getParameter('paypal_rest_client_id'),     // ClientID
                          $this->getParameter('paypal_rest_client_secret')      // ClientSecret
                      )
              );

              if($this->container->getParameter('kernel.environment') == "dev" || $this->container->getParameter('kernel.environment') == "test"){
              $apiContext->setConfig(array('mode' => 'sandbox'));
              }else{
                $apiContext->setConfig(array('mode' => 'live'));
              }

              $donation->setPaypalPayerId($request->query->get('PayerID'));
              $donation->setPaypalToken($request->query->get('token'));
              $donation->setPaypalPaymentId($request->query->get('paymentId'));
              $donation->setPaypalSuccessFlag($request->query->get('success'));

               $logger->debug("Looking for success parameter");
                if ($donation->getPaypalSuccessFlag() == true) {
                    $logger->debug("Payment was a success");
                    $donation->setDonationStatus("ACCEPTED");

                    try {
                      $payment = Payment::get($donation->getPaypalPaymentId(), $apiContext);
                    } catch (PayPalConnectionException $ex) {
                        $logger->critical("Paypal Payment PayPalConnectionException Failure. code:".$ex->getCode()." data:".$ex->getData());
                        exit(1);
                    } catch (Exception $ex) {
                        $logger->critical("Paypal Payment getPaymentDetails Failure");
                        exit(1);
                    }

                    $execution = new PaymentExecution();
                    $execution->setPayerId($donation->getPaypalPayerId());

                    try {
                        $result = $payment->execute($execution, $apiContext);
                        $PaypalPaymentDetails = Payment::get($donation->getPaypalPaymentId(), $apiContext);
                        $donation->setPaypalPaymentDetails(json_decode($PaypalPaymentDetails, true));
                    } catch (PayPalConnectionException $ex) {
                        $logger->critical("Paypal Payment PayPalConnectionException Failure. code:".$ex->getCode()." data:".$ex->getData());
                        $donation->setDonationStatus("FAILED");
                        $this->get('session')->getFlashBag()->add('danger', 'There was an issue processing the payment, it has been cancelled');
                        $failure = true;
                    } catch (Exception $ex) {
                        $logger->critical("Paypal Payment Execution Failure");
                        $donation->setDonationStatus("FAILED");
                        $this->get('session')->getFlashBag()->add('danger', 'There was an issue processing the payment, it has been cancelled');
                        $failure = true;
                      }
                } else {
                    $donation->setDonationStatus("FAILED");
                    exit;
                }

          }

            //Save Data
            $em->persist($donation);
            $em->flush();
        }

          if(!$failure){
            //Send Email to Donor
            $message = (new \Swift_Message("[FRManager] Thank you for your Donation to ".$campaign->getName()))
              ->setFrom($this->getParameter('mailer_user'))
              ->setTo($donation->getDonorEmail())
              ->setContentType("text/html")
              ->setBody(
                  $this->renderView('email/donation.success.email.twig', array('donation' => $donation,'campaign' => $campaign))
              );

            $this->get('mailer')->send($message);
            $logger->debug("Done Mailer to Donor");


            //Send Email to Recipient of Donation (If Team)
            if($donation->getType() == "team"){

            $message = (new \Swift_Message("[FRManager] ".$donation->getDonorFirstName()." ".$donation->getDonorLastName()." Has made a donation to your team!"))
              ->setFrom($this->getParameter('mailer_user'))
              ->setTo($donation->getTeam()->getUser()->getEmail())
              ->setContentType("text/html")
              ->setBody(
                  $this->renderView('email/donation.notify.email.twig', array('donation' => $donation,'campaign' => $campaign))
              );

            $this->get('mailer')->send($message);
            $logger->debug("Done Mailer to Recipient of Donation");
            }

            $logger->debug("Doing a Donation Database Refresh");
            $donationHelper = new DonationHelper($em, $logger);
            $donationHelper->reloadDonationDatabase(array('donation'=>$donation));
          }else{
            return $this->redirectToRoute('donation_index', array('campaignUrl'=>$campaign->getUrl()));
          }
      }

      if($failure && $donation->getDonationStatus() !== "ACCEPTED"){
        return $this->render('donation/donation.cancelled.html.twig', array(
            'campaign' => $campaign,
        ));
      }

      return $this->render('donation/donation.success.html.twig', array(
          'donation' => $donation,
          'campaign' => $campaign,
      ));

    }
}
