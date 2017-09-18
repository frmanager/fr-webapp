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

        if (isset($options['campaign'])) {
            $donations = $this->em->getRepository('AppBundle:Donation')->findByCampaign($campaign);
        } else {
            $donations = $this->em->getRepository('AppBundle:Donation')->findAll();
        }


        $counter = 0;
        foreach ($donations as $donation) {
            $fail = false;
            if ($donation->getDonationStatus() !== "ACCEPTED") {
                $fail = true;
            }
            if (!$fail) {

              //If its not accepted, try the next one
              if (in_array($donation->getType(), array("campaign", "classroom", "student"))) {
                  $donationDatabase = new DonationDatabase();

                //IF WE HAVE CONFIRMED THE STUDENT, USE THE PROPER NAME
                if ($donation->getType() == "student" && $donation->getStudentConfirmedFlag()) {
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
                    } else {
                        $donationDatabase->setStudentName($teamStudent->getName());
                    }
                      $donationDatabase->setTeam($team);
                      $donationDatabase->setStudent($teamStudent->getStudent());
                      $donationDatabase->setCampaign($donation->getCampaign());
                      $donationDatabase->setDonatedAt($donation->getDonatedAt());
                      $donationDatabase->setClassroom($teamStudent->getStudent()->getClassroom());
                      $donationDatabase->setDonation($donation);
                      $donationDatabase->setAmount(round($donation->getAmount()/$counter, 2)); //Evenly distributed
                      $donationDatabase->setType($donation->getType());
                      $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                      $this->em->persist($donationDatabase);
                  }
                } else { // The only other option is classroom

                      //HERE WE ARE CREATING A DONATION FOR EACH CHILD IN THE CLASSROOM
                      $classroom = $team->getClassroom();
                      //Need to figure out how many students are in the classroom
                      $counter = 0;
                      foreach ($classroom->getStudents() as $students) {
                          $counter ++;
                      }

                      foreach ($classroom->getStudents() as $student) {
                          $donationDatabase = new DonationDatabase();
                          $donationDatabase->setStudentName($student->getName());
                          $donationDatabase->setTeam($team);
                          $donationDatabase->setStudent($student);
                          $donationDatabase->setDonatedAt($donation->getDonatedAt());
                          $donationDatabase->setCampaign($donation->getCampaign());
                          $donationDatabase->setClassroom($classroom);
                          $donationDatabase->setDonation($donation);
                          $donationDatabase->setAmount(round($donation->getAmount()/$counter, 2)); //Evenly distributed
                          $donationDatabase->setType($donation->getType());
                          $donationDatabase->setPaymentMethod($donation->getPaymentMethod());
                          $this->em->persist($donationDatabase);
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
    }


    private function truncateDonationDatabase(array $options)
    {

        $qb = $this->em->createQueryBuilder();

        if (isset($options['campaign'])) {
          $qb->delete('AppBundle:DonationDatabase', 'd');
          $qb->where('d.campaign = :campaign');
          $qb->setParameter('campaign', $options['campaign']);
        } else {
          $qb->delete('AppBundle:DonationDatabase', 'd');
        }

        $result = $qb->getQuery()->getResult();
    }


    public function convertToDay($inDate)
    {
        $dateString = $inDate->format('Y-m-d').' 00:00:00';

        return DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
    }
}
