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

    public function getTeachersData(array $options)
    {
        return $this->getData($this->getTeachersDataQuery($options));
    }

    public function getData($queryString)
    {
        //$em = $this->em->getManager();
        return $this->em->createQuery($queryString)->getResult();
    }

    public function getTeacherDonationsByDay(array $options)
    {
        return $this->getData($this->getTeacherDonationsByDayQuery($options));
    }

    public function getTotalDonations(array $options)
    {
        $data = $this->getData($this->getTotalDonationsQuery($options));

        return $data[0];
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
                      t.id as teacher_id,
                      t.email as teacher_email,
                      t.teacherName as teacher_name,
                      g.id as grade_id,
                      g.name as grade_name,
                      sum(d.amount) as donation_amount,
                      count(d.amount) as total_donations
                 FROM AppBundle:Student s
      LEFT OUTER JOIN AppBundle:Teacher t
                 WITH t.id = s.teacher
      LEFT OUTER JOIN AppBundle:Donation d
                 WITH s.id = d.student
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
                                  %s', $campaign->getId(), $date);

        $this->logger->debug('Query : '.$queryString);

        return $queryString;
    }

    public function getTeachersDataQuery(array $options)
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
                                       t.email as teacher_email,
                                       g.id as grade_id,
                                       g.name as grade_name,
                                       sum(d.amount) as donation_amount,
                                       count(d.amount) as total_donations
                                  FROM AppBundle:Teacher t
                       LEFT OUTER JOIN AppBundle:Donation d
                                  WITH t.id = d.teacher
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

    public function getTeacherDonationsByDayQuery(array $options)
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
                        t.email as teacher_email,
                        g.id as grade_id,
                        g.name as grade_name,
                        d.donatedAt as donated_at,
                        sum(d.amount) as donation_amount,
                        count(d.amount) as total_donations
                   FROM AppBundle:Teacher t
                   JOIN AppBundle:Donation d
                   WITH t.id = d.teacher
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


    /*
    *
    * This loops trhough the "order_by" array in reverse and sorts it
    *
    */
    public function sortObject(array $objects, array $settings)
    {

        $this->logger->debug('sortObject Settings: '.dump($settings));
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

        $campaignawardtype = $this->em->getRepository('AppBundle:Campaignawardtype')->findOneByValue('teacher');
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

    public function getTeacherRanks(array $options)
    {
        return $this->getRanks($this->getTeachersData($options), $options);
    }

    public function getTeacherRank($id, array $options)
    {
        return $this->getRank($this->getTeachersData($options), $id, $options);
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
     * Gets teacher ID and donation amounts by Date
     * returns object.
     */
    public function getNewTeacherAwards(array $options)
    {
        if (isset($options['before_date'])) {
            $todaysDate = $this->convertToDay($options['before_date']);
        } else {
            $todaysDate = $this->convertToDay(new DateTime());
        }

        $teacherDonationAmountsByDay = $this->getTeacherAwards($options);

        $todaysTeachersWithAwards = [];
        $yesterdaysTeachersWithAwards = [];
        foreach ($teacherDonationAmountsByDay as $outerLoop) {
            if (isset($outerLoop['campaignaward_id'])) {
                //$this->logger->debug("Comparing Today: ".$todaysDate->format('Y-m-d H:i:s')." To outerLoop date: ".$outerLoop['donated_at']->format('Y-m-d H:i:s'));
                if ($todaysDate == $outerLoop['donated_at']) { //Today
                    //$this->logger->debug("Found award for today: ".print_r($outerLoop, true));
                    array_push($todaysTeachersWithAwards, $outerLoop);
                } elseif ($todaysDate > $outerLoop['donated_at']) { //Yesterday
                    $existsFlag = false;
                    //$this->logger->debug("Found award for yesterday: ".print_r($outerLoop, true));
                    foreach ($yesterdaysTeachersWithAwards as $key => $innerLoop) {
                        if ($innerLoop['id'] == $outerLoop['id']) {
                            $existsFlag = true;
                            if ($outerLoop['donated_at'] >= $innerLoop['donated_at']) {
                                unset($yesterdaysTeachersWithAwards[$key]);
                                array_push($yesterdaysTeachersWithAwards, $outerLoop);
                            }
                        }
                    }
                    if (!$existsFlag) {
                        array_push($yesterdaysTeachersWithAwards, $outerLoop);
                    }
                }
            }
        }

          //NOW TAKE THAT NEW LIST
          foreach ($todaysTeachersWithAwards as $key => $outerLoop) {
              foreach ($yesterdaysTeachersWithAwards as $innerLoop) {
                  if ($outerLoop['id'] == $innerLoop['id'] && $outerLoop['campaignaward_amount'] <= $innerLoop['campaignaward_amount']) {
                      unset($todaysTeachersWithAwards[$key]);
                  }
              }
          }

        $this->logger->debug('Classes with Awards before today!');
        $this->logger->debug(print_r($yesterdaysTeachersWithAwards, true));

        $this->logger->debug('Todays Classes with New Awards!');
        $this->logger->debug(print_r($todaysTeachersWithAwards, true));

        return $todaysTeachersWithAwards;
    }

    /**
     * Gets the date that various awards were met
     * returns object.
     */
    public function getTeacherAwards(array $options)
    {

        $teacherDonationAmountsByDay = $this->getTeacherDonationsByDay($options);
        $teacherCampaignawards = $this->getCampaignAwards($options['campaign'], 'teacher', 'level');

          //ADDING AWARD DATA TO $teacherDonationAmountsByDay. WE WILL COMPARE THIS AGAINST TODAYS TOTALS
          $loaddedAwardArray = [];

          foreach ($teacherDonationAmountsByDay as $teacher) {
              $sumAmount = 0;
             //GETTING CUMULATIVE SUM FOR EACH DAY
              foreach ($teacherDonationAmountsByDay as $thisRecord) {
                  if ($teacher['id'] == $thisRecord['id'] && $teacher['donated_at'] >= $thisRecord['donated_at']) {
                      $sumAmount += $thisRecord['donation_amount'];
                  }
              }
              $teacher['cumulative_donation_amount'] = $sumAmount;
              //$this->logger->debug(print_r($teacher, true));

              foreach ($teacherCampaignawards as $teacherCampaignaward) {
                  if ($teacherCampaignaward->getAmount() <= $sumAmount) {
                      $teacher['campaignaward_id'] = $teacherCampaignaward->getId();
                      $teacher['campaignaward_name'] = $teacherCampaignaward->getName();
                      $teacher['campaignaward_amount'] = $teacherCampaignaward->getAmount();
                      array_push($loaddedAwardArray, $teacher);
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
