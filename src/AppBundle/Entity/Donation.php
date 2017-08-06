<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * donation.
 *
 * @ORM\Entity
 * @ORM\Table(name="donation",uniqueConstraints={@ORM\UniqueConstraint(columns={"donated_at", "student_id", "transaction_id"})})
 * @UniqueEntity(
 *     fields={"student", "donatedAt", "transactionId"},
 *     errorPath="student",
 *     message="Already received a donation from this student on this day...."
 * )
 */
class Donation
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
     * @ORM\ManyToOne(targetEntity="Student", inversedBy="donations")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $student;

    /**
     * @var Student
     *
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="donations")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
     private $team;


    /**
     * @var Classroom
     *
     * @ORM\ManyToOne(targetEntity="Classroom", inversedBy="donations")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * @Assert\NotNull()
     */
    private $classroom;


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
     * @var datetime
     *
     * @ORM\Column(name="donated_at", type="datetime", nullable=false)
     * @Assert\Date()
     * @Assert\NotNull()
     */
    private $donatedAt;

    /**
     * @var float
     *
     * @ORM\Column(name="tip", type="float", precision=10, scale=2, nullable=true)
     */
    private $tip;

    /**
     * @var float
     *
     * @ORM\Column(name="estimated_cc_fee", type="float", precision=10, scale=2, nullable=true)
     */
    private $estimatedCcFee;

    /**
     * @var float
     *
     * @ORM\Column(name="fee", type="float", precision=10, scale=2, nullable=true)
     */
    private $fee;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="donor_email", type="string", length=100, nullable=true)
     */
    private $donorEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="donor_comment", type="string", length=255, nullable=true)
     */
    private $donorComment;

    /**
     * @var string
     *
     * @ORM\Column(name="donor_first_name", type="string", length=100, nullable=true)
     */
    private $donorFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="donor_last_name", type="string", length=100, nullable=true)
     */
    private $donorLastName;



    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="donations")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $campaign;


    /**
     * @var User
     *
     * Many Campaigns have One User.
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id")
     *
     */
    private $createdBy;


    /**
    * @ORM\Column(type="datetime")
    */
   protected $createdAt;


   /**
    * @ORM\Column(type="datetime")
    */
   protected $updatedAt;



   /**
    * Constructor
    */
   public function __construct()
   {
       $this->createdAt= new \DateTime();
       $this->updatedAt= new \DateTime();
   }


   /**
    * @ORM\PreUpdate()
    */
   public function preUpdate()
   {
       $this->updatedAt= new \DateTime();
   }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", nullable=true)
     *
     */
    private $transactionId;

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Donation
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
     * Set donatedAt
     *
     * @param \DateTime $donatedAt
     *
     * @return Donation
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

    /**
     * Set tip
     *
     * @param float $tip
     *
     * @return Donation
     */
    public function setTip($tip)
    {
        $this->tip = $tip;

        return $this;
    }

    /**
     * Get tip
     *
     * @return float
     */
    public function getTip()
    {
        return $this->tip;
    }

    /**
     * Set estimatedCcFee
     *
     * @param float $estimatedCcFee
     *
     * @return Donation
     */
    public function setEstimatedCcFee($estimatedCcFee)
    {
        $this->estimatedCcFee = $estimatedCcFee;

        return $this;
    }

    /**
     * Get estimatedCcFee
     *
     * @return float
     */
    public function getEstimatedCcFee()
    {
        return $this->estimatedCcFee;
    }

    /**
     * Set causevoxFee
     *
     * @param float $causevoxFee
     *
     * @return Donation
     */
    public function setCausevoxFee($causevoxFee)
    {
        $this->causevoxFee = $causevoxFee;

        return $this;
    }

    /**
     * Get causevoxFee
     *
     * @return float
     */
    public function getCausevoxFee()
    {
        return $this->causevoxFee;
    }


    /**
     * Set type
     *
     * @param string $type
     *
     * @return Donation
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
     * Set donorEmail
     *
     * @param string $donorEmail
     *
     * @return Donation
     */
    public function setDonorEmail($donorEmail)
    {
        $this->donorEmail = $donorEmail;

        return $this;
    }

    /**
     * Get donorEmail
     *
     * @return string
     */
    public function getDonorEmail()
    {
        return $this->donorEmail;
    }

    /**
     * Set donorComment
     *
     * @param string $donorComment
     *
     * @return Donation
     */
    public function setDonorComment($donorComment)
    {
        $this->donorComment = $donorComment;

        return $this;
    }

    /**
     * Get donorComment
     *
     * @return string
     */
    public function getDonorComment()
    {
        return $this->donorComment;
    }

    /**
     * Set donorFirstName
     *
     * @param string $donorFirstName
     *
     * @return Donation
     */
    public function setDonorFirstName($donorFirstName)
    {
        $this->donorFirstName = $donorFirstName;

        return $this;
    }

    /**
     * Get donorFirstName
     *
     * @return string
     */
    public function getDonorFirstName()
    {
        return $this->donorFirstName;
    }

    /**
     * Set donorLastName
     *
     * @param string $donorLastName
     *
     * @return Donation
     */
    public function setDonorLastName($donorLastName)
    {
        $this->donorLastName = $donorLastName;

        return $this;
    }

    /**
     * Get donorLastName
     *
     * @return string
     */
    public function getDonorLastName()
    {
        return $this->donorLastName;
    }

    /**
     * Set transactionId
     *
     * @param string $transactionId
     *
     * @return Donation
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Get transactionId
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set student
     *
     * @param \AppBundle\Entity\Student $student
     *
     * @return Donation
     */
    public function setStudent(\AppBundle\Entity\Student $student = null)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \AppBundle\Entity\Student
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set classroom
     *
     * @param \AppBundle\Entity\Classroom $classroom
     *
     * @return Donation
     */
    public function setClassroom(\AppBundle\Entity\Classroom $classroom = null)
    {
        $this->classroom = $classroom;

        return $this;
    }

    /**
     * Get classroom
     *
     * @return \AppBundle\Entity\Classroom
     */
    public function getClassroom()
    {
        return $this->classroom;
    }

    /**
     * Set campaign
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Donation
     */
    public function setCampaign(\AppBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return \AppBundle\Entity\Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Get campaign
     *
     * @return \AppBundle\Entity\Campaign
     */
    public function getClassroomCampaign()
    {
        return $this->classroom->campaign;
    }

    /**
     * Set campaign from provided Grade
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Grade
     */
    public function setCampaignFromClassroom(\AppBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $this->classroom->getCampaign();

        return $this;
    }

    /**
     * Get campaign
     *
     * @return \AppBundle\Entity\Campaign
     */
    public function getStudentCampaign()
    {
        return $this->student->campaign;
    }

    /**
     * Set campaign from provided Student
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Campaign
     */
    public function setCampaignFromStudent(\AppBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $this->student->getCampaign();

        return $this;
    }



        /**
         * Get campaign from Team
         *
         * @return \AppBundle\Entity\Campaign
         */
        public function getTeamCampaign()
        {
            return $this->team->campaign;
        }

        /**
         * Set campaign from provided Team
         *
         * @param \AppBundle\Entity\Campaign $campaign
         *
         * @return Campaign
         */
        public function setCampaignFromTeam(\AppBundle\Entity\Campaign $campaign = null)
        {
            $this->campaign = $this->team->getCampaign();

            return $this;
        }


    /**
     * Set classroom from provided Student
     *
     * @param \AppBundle\Entity\Classroom $classroom
     *
     * @return Classroom
     */
    public function setClassroomFromStudent(\AppBundle\Entity\Classroom $classroom = null)
    {
        $this->classroom = $this->student->getClassroom();

        return $this;
    }


    /**
     * Set team
     *
     * @param \AppBundle\Entity\Team $team
     *
     * @return Donation
     */
    public function setTeam(\AppBundle\Entity\Team $team = null)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return \AppBundle\Entity\Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set fee
     *
     * @param float $fee
     *
     * @return Donation
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee
     *
     * @return float
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Donation
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Donation
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdBy
     *
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return Donation
     */
    public function setCreatedBy(\AppBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \AppBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}
