<?php

// src/AppBundle/Utils/QueryHelper.php

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManager;
use DateTime;
use Monolog\Logger;

class QueryHelper
{
    protected $em;
    protected $logger;

    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function getStudentsData(array $options)
    {
        return $this->getData($this->getStudentsDataQuery($options));
    }

    public function getClassroomsData(array $options)
    {
        return $this->getData($this->getClassroomsDataQuery($options));
    }

    public function getTeamsData(array $options)
    {
        return $this->getData($this->getTeamsDataQuery($options));
    }

    public function getData($queryString)
    {
        //$em = $this->em->getManager();
        return $this->em->createQuery($queryString)->getResult();
    }

    public function getClassroomDonationsByDay(array $options)
    {
        return $this->getData($this->getClassroomDonationsByDayQuery($options));
    }

    public function getTotalDonations(array $options)
    {
        $data = $this->getData($this->getTotalDonationsQuery($options));

        return $data[0];
    }


    public function getTeamsDataQuery(array $options)
    {
        $campaign = $options['campaign'];

        if (isset($options['before_date'])) {
            $date = "AND d.donatedAt <= '".$this->convertToDay($options['before_date'])->format('Y-m-d H:i:s')."' ";
        } else {
            $date = '';
        }

        if (isset($options['id'])) {
            $whereId = 'WHERE t.id = '.$options['id'];
        } else {
            $whereId = '';
        }

        $queryString = sprintf('SELECT t.id as id,
                      t.name as team_name,
                      t.description as team_description,
                      t.url as team_url,
                      tt.name as team_type_name,
                      tt.value as team_type_value,
                      t.fundingGoal as team_funding_goal,
                      sum(d.amount) as donation_amount,
                      count(d.amount) as total_donations
                 FROM AppBundle:Team t
      LEFT OUTER JOIN AppBundle:TeamType tt
                 WITH tt.id = t.teamType
      LEFT OUTER JOIN AppBundle:Donation d
                 WITH t.id = d.team
                  AND d.donationStatus = \'ACCEPTED\'
                   %s
                   %s
                WHERE t.campaign = %s
             GROUP BY t.id
             ORDER BY donation_amount DESC', $date, $whereId, $campaign->getId());

        $this->logger->debug('Query : '.$queryString);

        return $queryString;
    }


    public function getStudentsDataQuery(array $options)
    {
        $campaign = $options['campaign'];

        if (isset($options['before_date'])) {
            $date = "AND d.donatedAt <= '".$this->convertToDay($options['before_date'])->format('Y-m-d H:i:s')."' ";
        } else {
            $date = '';
        }

        if (isset($options['id'])) {
            $whereId = 'WHERE s.id = '.$options['id'];
        } else {
            $whereId = '';
        }

        $queryString = sprintf('SELECT s.id as id,
                      s.name as student_name,
                      t.id as classroom_id,
                      t.email as classroom_email,
                      t.teacherName as teacher_name,
                      t.name as classroom_name,
                      g.id as grade_id,
                      g.name as grade_name,
                      sum(d.amount) as donation_amount,
                      count(d.amount) as total_donations
                 FROM AppBundle:Student s
      LEFT OUTER JOIN AppBundle:Classroom t
                 WITH t.id = s.classroom
      LEFT OUTER JOIN AppBundle:Donation d
                 WITH s.id = d.student
                  AND d.donationStatus = \'ACCEPTED\'
                   %s
      LEFT OUTER JOIN AppBundle:Grade g
                 WITH g.id = t.grade
                   %s
                WHERE s.campaign = %s
             GROUP BY s.id
             ORDER BY donation_amount DESC', $date, $whereId, $campaign->getId());

        $this->logger->debug('Query : '.$queryString);

        return $queryString;
    }

    public function getTotalDonationsQuery(array $options)
    {
        $campaign = $options['campaign'];

        if (isset($options['before_date'])) {
            $date = "AND d.donatedAt <= '".$this->convertToDay($options['before_date'])->format('Y-m-d H:i:s')."' ";
        } else {
            $date = '';
        }

        $queryString = sprintf('SELECT sum(d.amount) as donation_amount,
                                       count(d.amount) as total_donations
                                  FROM AppBundle:Donation d
                                 WHERE d.campaign = %s
                                   AND d.donationStatus = \'ACCEPTED\'
                                  %s', $campaign->getId(), $date);

        $this->logger->debug('Query : '.$queryString);

        return $queryString;
    }

    public function getClassroomsDataQuery(array $options)
    {
        $campaign = $options['campaign'];

        if (isset($options['before_date'])) {
            $date = "AND d.donatedAt <= '".$this->convertToDay($options['before_date'])->format('Y-m-d H:i:s')."' ";
        } else {
            $date = '';
        }

        if (isset($options['id'])) {
            $whereId = 'AND t.id = '.$options['id'];
        } else {
            $whereId = '';
        }

        $queryString = sprintf('SELECT t.id as id,
                                       t.teacherName as teacher_name,
                                       t.name as classroom_name,
                                       t.email as classroom_email,
                                       g.id as grade_id,
                                       g.name as grade_name,
                                       sum(d.amount) as donation_amount,
                                       count(d.amount) as total_donations
                                  FROM AppBundle:Classroom t
                       LEFT OUTER JOIN AppBundle:Donation d
                                  WITH t.id = d.classroom
                                   AND d.donationStatus = \'ACCEPTED\'
                                   %s
                                  JOIN AppBundle:Grade g
                                  WITH g.id = t.grade
                                    %s
                                 WHERE t.campaign = %s
                              GROUP BY t.id
                              ORDER BY donation_amount DESC', $date, $whereId, $campaign->getId());

        $this->logger->debug('Query : '.$queryString);

        return $queryString;
    }

    public function getClassroomDonationsByDayQuery(array $options)
    {
        $campaign = $options['campaign'];

        if (isset($options['before_date'])) {
            $date = "AND d.donatedAt <= '".$this->convertToDay($options['before_date'])->format('Y-m-d H:i:s')."' ";
        } else {
            $date = '';
        }

        if (isset($options['id'])) {
            $whereId = 'AND t.id = '.$options['id'];
        } else {
            $whereId = '';
        }

        $queryString = sprintf('SELECT t.id as id,
                        t.teacherName as teacher_name,
                        t.name as classroom_name,
                        t.email as classroom_email,
                        g.id as grade_id,
                        g.name as grade_name,
                        d.donatedAt as donated_at,
                        sum(d.amount) as donation_amount,
                        count(d.amount) as total_donations
                   FROM AppBundle:Classroom t
                   JOIN AppBundle:Donation d
                   WITH t.id = d.classroom
                    AND d.donationStatus = \'ACCEPTED\'
                      %s
                   JOIN AppBundle:Grade g
                   WITH g.id = t.grade
                      %s
                  WHERE t.campaign = %s
               GROUP BY d.donatedAt, t.id
               ORDER BY t.id ASC, d.donatedAt ASC', $date, $whereId, $campaign->getId());

        $this->logger->debug('Query : '.$queryString);

        return $queryString;
    }


    public function getTeamDonationsByDayQuery(array $options)
    {
        $campaign = $options['campaign'];

        if (isset($options['before_date'])) {
            $date = "AND d.donatedAt <= '".$this->convertToDay($options['before_date'])->format('Y-m-d H:i:s')."' ";
        } else {
            $date = '';
        }

        if (isset($options['id'])) {
            $whereId = 'WHERE t.id = '.$options['id'];
        } else {
            $whereId = '';
        }

        $queryString = sprintf('SELECT s.id as id,
                      t.name as team_name,
                      t.description as team_description,
                      t.url as team_url,
                      tt.name as team_type_name,
                      tt.value as team_type_value,
                      t.funding_goal as team_funding_goal
                      sum(d.amount) as donation_amount,
                      count(d.amount) as total_donations
                 FROM AppBundle:Teams t
      LEFT OUTER JOIN AppBundle:TeamType tt
                 WITH tt.id = t.teamType
      LEFT OUTER JOIN AppBundle:Donation d
                 WITH t.id = d.team
                  AND d.donationStatus = \'ACCEPTED\'
                   %s
                   %s
                WHERE s.campaign = %s
             GROUP BY d.donatedAt, t.id
             ORDER BY donation_amount DESC', $date, $whereId, $campaign->getId());

        $this->logger->debug('Query : '.$queryString);

        return $queryString;
    }



    /*
    *
    * This loops trhough the "order_by" array in reverse and sorts it
    *
    */
    public function sortObject(array $objects, array $settings)
    {

        if (isset($settings['order_by'])) {
            $order_by = $settings['order_by'];

        } else {
           //Order By must be set
           return false;
        }

          $tempObjectArray = [];

          $listOfAmounts = [];
          foreach ($objects as $key => $value) {
              $this->logger->debug('Before sortObject: row ['.$key.'] - '.print_r($value, true));
              $listOfAmounts[$key] = $value[$order_by['field']];
          }
          arsort($listOfAmounts);

          $newObjectArray = [];
          foreach ($listOfAmounts as $key => $value) {
              $this->logger->debug('After sortObject: row ['.$key.'] - '.print_r($value, true));
              array_push($newObjectArray, $objects[$key]);
          }

        return $newObjectArray;
    }


    public function getRanks(array $objects, array $settings)
    {
        foreach ($objects as $object) {
            $this->logger->debug('getRanks Input Data: '.print_r($object, true));
        }

        if (isset($settings['amount_field'])) {
            $amountField = $settings['amount_field'];
        } else {
            $amountField = 'donation_amount';
        }

        if (isset($settings['limit'])) {
            $limit = $settings['limit'];
        } else {
            $limit = 0;
        }

        //Sorting DESC
        $sortedObjects = $this->sortObject($objects, array('order_by' => array('field' => 'donation_amount', 'order' => "asc")));

        $rank = 0;
        $amount = 9999999999999999999; //some astronomical number
        foreach ($sortedObjects as $sortedObject) {
            if ($sortedObject[$amountField] < $amount) {
                ++$rank;
            }

            foreach ($objects as &$object) {
                if ($object['id'] == $sortedObject['id']) {
                    $object['rank'] = $rank;
                    break;
                }
            }
            $amount = $sortedObject[$amountField];
        }

        if ($limit > 0) {
            $counter = 0;
            $newArray = [];
            foreach ($objects as $object) {
                if ($counter < $limit) {
                    array_push($newArray, $object);
                }
                ++$counter;
            }
            $objects = $newArray;
        }

        return $objects;
    }

    public function getRank(array $objects, $id, array $settings)
    {
        foreach ($objects as $object) {
            $this->logger->debug('getRanks Input Data: '.print_r($object, true));
        }

        if (isset($settings['amount_field'])) {
            $amountField = $settings['amount_field'];
        } else {
            $amountField = 'donation_amount';
        }

        if (isset($settings['limit'])) {
            $limit = $settings['limit'];
        } else {
            $limit = 0;
        }

        //Sorting DESC
        $sortedObjects = $this->sortObject($objects, array('order_by' => array('field' => 'donation_amount', 'order' => "asc")));

        $rank = 0;
        $amount = 9999999999999999999; //some astronomical number
        foreach ($sortedObjects as $sortedObject) {
            if ($sortedObject[$amountField] < $amount) {
                ++$rank;
            }

            foreach ($objects as $object) {
                if ($object['id'] == $sortedObject['id'] && $sortedObject['id'] == $id) {
                    return $rank;
                    break;
                }
            }
            $amount = $sortedObject[$amountField];
        }

        return false;
    }

    public function getCampaignAwards($campaign, $type, $style)
    {

        $campaignawardtype = $this->em->getRepository('AppBundle:Campaignawardtype')->findOneByValue('classroom');
        $campaignawardstyle = $this->em->getRepository('AppBundle:Campaignawardstyle')->findOneByValue('level');
        $qb = $this->em->createQueryBuilder()->select('u')
             ->from('AppBundle:Campaignaward', 'u')
             ->andWhere('u.campaignawardtype = :awardType')
             ->andWhere('u.campaignawardstyle = :awardStyle')
             ->andWhere('u.campaign = :campaignId')
             ->setParameter('campaignId', $campaign->getId())
             ->setParameter('awardStyle', $campaignawardstyle->getId())
             ->setParameter('awardType', $campaignawardtype->getId())
             ->orderBy('u.amount', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getClassroomRanks(array $options)
    {
        return $this->getRanks($this->getClassroomsData($options), $options);
    }

    public function getClassroomRank($id, array $options)
    {
        return $this->getRank($this->getClassroomsData($options), $id, $options);
    }

    public function getTeamRanks(array $options)
    {
        return $this->getRanks($this->getTeamsData($options), $options);
    }

    public function getTeamRank($id, array $options)
    {
        return $this->getRank($this->getTeamsData($options), $id, $options);
    }

    public function getStudentRanks(array $options)
    {
        return $this->getRanks($this->getStudentsData($options), $options);
    }

    public function getStudentRank($id, array $options)
    {
        return $this->getRank($this->getStudentsData($options), $id, $options);
    }

    /**
     * Gets classroom ID and donation amounts by Date
     * returns object.
     */
    public function getNewClassroomAwards(array $options)
    {
        if (isset($options['before_date'])) {
            $todaysDate = $this->convertToDay($options['before_date']);
        } else {
            $todaysDate = $this->convertToDay(new DateTime());
        }

        $classroomDonationAmountsByDay = $this->getClassroomAwards($options);

        $todaysClassroomsWithAwards = [];
        $yesterdaysClassroomsWithAwards = [];
        foreach ($classroomDonationAmountsByDay as $outerLoop) {
            if (isset($outerLoop['campaignaward_id'])) {
                //$this->logger->debug("Comparing Today: ".$todaysDate->format('Y-m-d H:i:s')." To outerLoop date: ".$outerLoop['donated_at']->format('Y-m-d H:i:s'));
                if ($todaysDate == $outerLoop['donated_at']) { //Today
                    //$this->logger->debug("Found award for today: ".print_r($outerLoop, true));
                    array_push($todaysClassroomsWithAwards, $outerLoop);
                } elseif ($todaysDate > $outerLoop['donated_at']) { //Yesterday
                    $existsFlag = false;
                    //$this->logger->debug("Found award for yesterday: ".print_r($outerLoop, true));
                    foreach ($yesterdaysClassroomsWithAwards as $key => $innerLoop) {
                        if ($innerLoop['id'] == $outerLoop['id']) {
                            $existsFlag = true;
                            if ($outerLoop['donated_at'] >= $innerLoop['donated_at']) {
                                unset($yesterdaysClassroomsWithAwards[$key]);
                                array_push($yesterdaysClassroomsWithAwards, $outerLoop);
                            }
                        }
                    }
                    if (!$existsFlag) {
                        array_push($yesterdaysClassroomsWithAwards, $outerLoop);
                    }
                }
            }
        }

          //NOW TAKE THAT NEW LIST
          foreach ($todaysClassroomsWithAwards as $key => $outerLoop) {
              foreach ($yesterdaysClassroomsWithAwards as $innerLoop) {
                  if ($outerLoop['id'] == $innerLoop['id'] && $outerLoop['campaignaward_amount'] <= $innerLoop['campaignaward_amount']) {
                      unset($todaysClassroomsWithAwards[$key]);
                  }
              }
          }

        $this->logger->debug('Classes with Awards before today!');
        $this->logger->debug(print_r($yesterdaysClassroomsWithAwards, true));

        $this->logger->debug('Todays Classes with New Awards!');
        $this->logger->debug(print_r($todaysClassroomsWithAwards, true));

        return $todaysClassroomsWithAwards;
    }

    /**
     * Gets the date that various awards were met
     * returns object.
     */
    public function getClassroomAwards(array $options)
    {

        $classroomDonationAmountsByDay = $this->getClassroomDonationsByDay($options);
        $classroomCampaignawards = $this->getCampaignAwards($options['campaign'], 'classroom', 'level');

          //ADDING AWARD DATA TO $classroomDonationAmountsByDay. WE WILL COMPARE THIS AGAINST TODAYS TOTALS
          $loaddedAwardArray = [];

          foreach ($classroomDonationAmountsByDay as $classroom) {
              $sumAmount = 0;
             //GETTING CUMULATIVE SUM FOR EACH DAY
              foreach ($classroomDonationAmountsByDay as $thisRecord) {
                  if ($classroom['id'] == $thisRecord['id'] && $classroom['donated_at'] >= $thisRecord['donated_at']) {
                      $sumAmount += $thisRecord['donation_amount'];
                  }
              }
              $classroom['cumulative_donation_amount'] = $sumAmount;
              //$this->logger->debug(print_r($classroom, true));

              foreach ($classroomCampaignawards as $classroomCampaignaward) {
                  if ($classroomCampaignaward->getAmount() <= $sumAmount) {
                      $classroom['campaignaward_id'] = $classroomCampaignaward->getId();
                      $classroom['campaignaward_name'] = $classroomCampaignaward->getName();
                      $classroom['campaignaward_amount'] = $classroomCampaignaward->getAmount();
                      array_push($loaddedAwardArray, $classroom);
                  }
              }

          }

        foreach ($loaddedAwardArray as $key => $outerLoop) {
            foreach ($loaddedAwardArray as $innerLoop) {
                //If award already happend on a previous day, we remove it
              if (!isset($outerLoop['campaignaward_id'])){
                  unset($loaddedAwardArray[$key]);
              }else if ($innerLoop['id'] == $outerLoop['id'] && $innerLoop['campaignaward_id'] == $outerLoop['campaignaward_id'] && $innerLoop['donated_at'] < $outerLoop['donated_at']) {
                  unset($loaddedAwardArray[$key]);
              }
            }
        }

          $this->logger->debug('Classes with Awards before today!');
          foreach ($loaddedAwardArray as $outerLoop) {
              //$this->logger->debug(print_r($outerLoop, true));
          }

          $this->logger->debug('Ordering $loaddedAwardArray');
          if (isset($options['order_by'])) {
              $loaddedAwardArray = $this->sortObject($loaddedAwardArray, $options);
          } else {
              $order_by = [];
          }

          $this->logger->debug('Applying limit');
          if (isset($options['limit'])) {
              $limit = $options['limit'];
          } else {
              $limit = 0;
          }


          if ($limit > 0) {
              $counter = 0;
              $newArray = [];
              foreach ($loaddedAwardArray as $object) {
                  if ($counter < $limit) {
                      array_push($newArray, $object);
                  }else{
                    break;
                  }
                  ++$counter;
              }
              $loaddedAwardArray = $newArray;
          }


        return $loaddedAwardArray;
    }


    public function convertToDay($inDate)
    {
        $dateString = $inDate->format('Y-m-d').' 00:00:00';

        return DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
    }
}
