<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * donation.
 *
 * @ORM\Entity
 * @ORM\Table(name="donation_database")
 */
class DonationDatabase
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Student
     *
     * @ORM\ManyToOne(targetEntity="Student", inversedBy="donationDatabases")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $student;

    /**
     * @var Student
     *
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="donationDatabases")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
     private $team;

    /**
     * @var Classroom
     *
     * @ORM\ManyToOne(targetEntity="Classroom", inversedBy="donationDatabases")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     * @Assert\NotNull()
     */
    private $classroom;

    /**
     * @var Donation
     *
     * @ORM\ManyToOne(targetEntity="Donation", inversedBy="donationDatabases")
     * @ORM\JoinColumn(referencedColumnName="id",  nullable=true)
     * @Assert\NotNull()
     */
    private $donation;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="donationDatabases")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $campaign;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=10, scale=2, nullable=false)
     * @Assert\Type(
     *     type="float",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\NotNull()
     */
    private $amount;


    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100)
     */
    private $type;


    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", length=100)
     */
    private $paymentMethod;


    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $studentName;

    /**
     * @var datetime
     *
     * @ORM\Column(name="donated_at", type="datetime", nullable=false)
     * @Assert\Date()
     * @Assert\NotNull()
     */
    private $donatedAt;


   /**
    * Constructor
    */
   public function __construct()
   {

   }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return DonationDatabase
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return DonationDatabase
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     *
     * @return DonationDatabase
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set student
     *
     * @param \App\Entity\Student $student
     *
     * @return DonationDatabase
     */
    public function setStudent(\App\Entity\Student $student = null)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \App\Entity\Student
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set team
     *
     * @param \App\Entity\Team $team
     *
     * @return DonationDatabase
     */
    public function setTeam(\App\Entity\Team $team = null)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return \App\Entity\Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set classroom
     *
     * @param \App\Entity\Classroom $classroom
     *
     * @return DonationDatabase
     */
    public function setClassroom(\App\Entity\Classroom $classroom = null)
    {
        $this->classroom = $classroom;

        return $this;
    }

    /**
     * Get classroom
     *
     * @return \App\Entity\Classroom
     */
    public function getClassroom()
    {
        return $this->classroom;
    }

    /**
     * Set donation
     *
     * @param \App\Entity\Donation $donation
     *
     * @return DonationDatabase
     */
    public function setDonation(\App\Entity\Donation $donation = null)
    {
        $this->donation = $donation;

        return $this;
    }

    /**
     * Get donation
     *
     * @return \App\Entity\Donation
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * Set campaign
     *
     * @param \App\Entity\Campaign $campaign
     *
     * @return DonationDatabase
     */
    public function setCampaign(\App\Entity\Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return \App\Entity\Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Set studentName
     *
     * @param string $studentName
     *
     * @return DonationDatabase
     */
    public function setStudentName($studentName)
    {
        $this->studentName = $studentName;

        return $this;
    }

    /**
     * Get studentName
     *
     * @return string
     */
    public function getStudentName()
    {
        return $this->studentName;
    }

    /**
     * Set donatedAt
     *
     * @param \DateTime $donatedAt
     *
     * @return DonationDatabase
     */
    public function setDonatedAt($donatedAt)
    {
        $this->donatedAt = $donatedAt;

        return $this;
    }

    /**
     * Get donatedAt
     *
     * @return \DateTime
     */
    public function getDonatedAt()
    {
        return $this->donatedAt;
    }
}
