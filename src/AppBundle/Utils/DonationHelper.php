<?php

// src/AppBundle/Utils/QueryHelper.php

namespace AppBundle\Utils;

use AppBundle\Entity\DonationDatabase;
use Doctrine\ORM\EntityManager;
use DateTime;
use Monolog\Logger;

class DonationHelper
{
    protected $em;
    protected $logger;

    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function reloadDonationDatabase(array $options)
    {

        //First thing we do is delete all Records
        $this->truncateDonationDatabase($options);

        if (isset($options['donation'])){
            $this->logger->info("DonationHelper::reloadDonationDatabase - Refreshing Donation #".$options['donation']->getId());
            $donations = $this->em->getRepository('AppBundle:Donation')->findBy(array('id'=>$options['donation']->getId()));
        }elseif (isset($options['campaign'])) {
            $this->logger->info("DonationHelper::reloadDonationDatabase - Refreshing a Campaign");
            $donations = $this->em->getRepository('AppBundle:Donation')->findByCampaign($options['campaign']);
        }else {
            $donations = $this->em->getRepository('AppBundle:Donation')->findAll();
        }


        $counter = 0;
        foreach ($donations as $donation) {
            $fail = false;
            //If its not accepted, try the next one
            if ($donation->getDonationStatus() == "ACCEPTED") {
                if ($donation->getType() == "campaign") {
                    $donationDatabase = new DonationDatabase();
                    $donationDatabase->setCampaign($donation->getCampaign());
                    $donationDatabase->setDonation($donation);
                    $donationDatabase->setDonatedAt($donation->getDonatedAt());
                    $donationDatabase->setAmount($donation->getAmount());
                    $donationDatabase->setType($donation->getType());
                    $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                    $this->em->persist($donationDatabase);
                    $this->em->flush();
                } elseif ($donation->getType() == "student") {
                    $donationDatabase = new DonationDatabase();

                //IF WE HAVE CONFIRMED THE STUDENT, USE THE PROPER NAME
                if ($donation->getStudentConfirmedFlag()) {
                    $donationDatabase->setStudentName($donation->getStudent()->getName());
                } else {
                    $donationDatabase->setStudentName($donation->getStudentName());
                }

                    $donationDatabase->setCampaign($donation->getCampaign());
                    $donationDatabase->setClassroom($donation->getClassroom());
                    $donationDatabase->setStudent($donation->getStudent());
                    $donationDatabase->setDonation($donation);
                    $donationDatabase->setDonatedAt($donation->getDonatedAt());
                    $donationDatabase->setAmount($donation->getAmount());
                    $donationDatabase->setType($donation->getType());
                    $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                    $this->em->persist($donationDatabase);
                    $this->em->flush();
                } elseif ($donation->getType() == "classroom") {

                  //HERE WE ARE CREATING A DONATION FOR EACH CHILD IN THE CLASSROOM
                  $classroom = $donation->getClassroom();
                  //Need to figure out how many students are in the classroom
                  $counter = 0;
                    foreach ($classroom->getStudents() as $students) {
                        $counter ++;
                    }

                    if ($counter == 0) {
                        $donationDatabase = new DonationDatabase();
                        $donationDatabase->setDonatedAt($donation->getDonatedAt());
                        $donationDatabase->setCampaign($donation->getCampaign());
                        $donationDatabase->setClassroom($classroom);
                        $donationDatabase->setDonation($donation);
                        $donationDatabase->setAmount($donation->getAmount()); //Evenly distributed
                        $donationDatabase->setType($donation->getType());
                        $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                        $this->em->persist($donationDatabase);
                        $this->em->flush();
                    } else {
                        foreach ($classroom->getStudents() as $student) {
                            $donationDatabase = new DonationDatabase();
                            $donationDatabase->setStudentName($student->getName());
                            $donationDatabase->setStudent($student);
                            $donationDatabase->setDonatedAt($donation->getDonatedAt());
                            $donationDatabase->setCampaign($donation->getCampaign());
                            $donationDatabase->setClassroom($classroom);
                            $donationDatabase->setDonation($donation);
                            $donationDatabase->setAmount(($donation->getAmount()/$counter)); //Evenly distributed
                            $donationDatabase->setType($donation->getType());
                            $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                            $this->em->persist($donationDatabase);
                            $this->em->flush();
                        }
                    }
                } elseif ($donation->getType() == "team") {
                    $team = $donation->getTeam();
                    if (in_array($team->getTeamType()->getValue(), array("student", "family"))) {
                        $teamStudents = $team->getTeamStudents();
                  //Need to figure out how many students there are
                  $counter = 0;
                        foreach ($teamStudents as $teamStudent) {
                            $counter ++;
                        }
                  //Now we actually create the records
                  foreach ($teamStudents as $teamStudent) {
                      $donationDatabase = new DonationDatabase();

                    //IF WE HAVE CONFIRMED THE STUDENT, USE THE PROPER NAME
                    if ($teamStudent->getConfirmedFlag()) {
                        $donationDatabase->setStudentName($teamStudent->getStudent()->getName());
                        $donationDatabase->setClassroom($teamStudent->getStudent()->getClassroom());
                    } else {
                        $donationDatabase->setStudentName($teamStudent->getName());
                        $donationDatabase->setClassroom($teamStudent->getClassroom());
                    }
                      $donationDatabase->setTeam($team);
                      $donationDatabase->setStudent($teamStudent->getStudent());
                      $donationDatabase->setCampaign($donation->getCampaign());
                      $donationDatabase->setDonatedAt($donation->getDonatedAt());
                      $donationDatabase->setDonation($donation);
                      $donationDatabase->setAmount(($donation->getAmount()/$counter)); //Evenly distributed
                      $donationDatabase->setType($donation->getType());
                      $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                      $this->em->persist($donationDatabase);
                      $this->em->flush();
                  }
                    } else { // The only other option is classroom

                      //HERE WE ARE CREATING A DONATION FOR EACH CHILD IN THE CLASSROOM
                      $classroom = $team->getClassroom();
                      //Need to figure out how many students are in the classroom
                      $counter = 0;
                        foreach ($classroom->getStudents() as $students) {
                            $counter ++;
                        }

                        if ($counter == 0) {
                              $donationDatabase = new DonationDatabase();
                              $donationDatabase->setTeam($team);
                              $donationDatabase->setDonatedAt($donation->getDonatedAt());
                              $donationDatabase->setCampaign($donation->getCampaign());
                              $donationDatabase->setClassroom($classroom);
                              $donationDatabase->setDonation($donation);
                              $donationDatabase->setAmount($donation->getAmount()); //Evenly distributed
                              $donationDatabase->setType($donation->getType());
                              $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                              $this->em->persist($donationDatabase);
                              $this->em->flush();
                        } else {
                            foreach ($classroom->getStudents() as $student) {
                                $donationDatabase = new DonationDatabase();
                                $donationDatabase->setStudentName($student->getName());
                                $donationDatabase->setTeam($team);
                                $donationDatabase->setStudent($student);
                                $donationDatabase->setDonatedAt($donation->getDonatedAt());
                                $donationDatabase->setCampaign($donation->getCampaign());
                                $donationDatabase->setClassroom($classroom);
                                $donationDatabase->setDonation($donation);
                                $donationDatabase->setAmount(($donation->getAmount()/$counter)); //Evenly distributed
                                $donationDatabase->setType($donation->getType());
                                $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                                $this->em->persist($donationDatabase);
                                $this->em->flush();
                            }
                        }

                    }
                }
            }

          //FLUSH EVERY 20 RECORDS
          if ($counter % 20  == 0) {
              $this->em->flush();
          }
            $counter ++;
        }
        $this->em->flush();

        $this->calculateFundsRaised($options);
    }


    private function truncateDonationDatabase(array $options)
    {
        $qb = $this->em->createQueryBuilder();

        if (isset($options['donation'])) {
            $qb->delete('AppBundle:DonationDatabase', 'd');
            $qb->where('d = :donation');
            $qb->setParameter('donation', $options['donation']);
        }else if (isset($options['campaign'])) {
            $qb->delete('AppBundle:DonationDatabase', 'd');
            $qb->where('d.campaign = :campaign');
            $qb->setParameter('campaign', $options['campaign']);
        } else {
            $qb->delete('AppBundle:DonationDatabase', 'd');
        }

        $result = $qb->getQuery()->getResult();
    }


    private function calculateFundsRaised(array $options)
    {
      if (isset($options['campaign'])) {
          $teams = $this->em->getRepository('AppBundle:Team')->findByCampaign($options['campaign']);
      } else {
          $teams = $this->em->getRepository('AppBundle:Team')->findAll();
      }

      foreach ($teams as $team) {
        $fundsraised = 0;
        foreach ($team->getDonationDatabases() as $donationDatabase) {
          $fundsraised += $donationDatabase->getAmount();
        }
        $team->setFundsRaised($fundsraised);
        $this->em->persist($team);
        $this->em->flush();
      }
    }



    public function convertToDay($inDate)
    {
        $dateString = $inDate->format('Y-m-d').' 00:00:00';

        return DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
    }
}
