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
 * @ORM\Table(name="donation",uniqueConstraints={@ORM\UniqueConstraint(columns={"donated_at", "student_id", "transaction_id", "source"})})
 * @UniqueEntity(
 *     fields={"student", "donatedAt", "transactionId", "source"},
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
     * @var Teacher
     *
     * @ORM\ManyToOne(targetEntity="Teacher", inversedBy="donations")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $teacher;


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
     * @ORM\Column(name="causevox_fee", type="float", precision=10, scale=2, nullable=true)
     */
    private $causevoxFee;

    /**
     * @var string
     *
     * @ORM\Column(name="donation_page", type="string", length=100, nullable=true)
     */
    private $donationPage;

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
     * @var string
     *
     * @ORM\Column(name="source", type="string", nullable=false)
     *
     */
    private $source;

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
     * Set donationPage
     *
     * @param string $donationPage
     *
     * @return Donation
     */
    public function setDonationPage($donationPage)
    {
        $this->donationPage = $donationPage;

        return $this;
    }

    /**
     * Get donationPage
     *
     * @return string
     */
    public function getDonationPage()
    {
        return $this->donationPage;
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
     * Set source
     *
     * @param string $source
     *
     * @return Donation
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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
     * Set teacher
     *
     * @param \AppBundle\Entity\Teacher $teacher
     *
     * @return Donation
     */
    public function setTeacher(\AppBundle\Entity\Teacher $teacher = null)
    {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * Get teacher
     *
     * @return \AppBundle\Entity\Teacher
     */
    public function getTeacher()
    {
        return $this->teacher;
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
    public function getTeacherCampaign()
    {
        return $this->teacher->campaign;
    }

    /**
     * Set campaign from provided Grade
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Grade
     */
    public function setCampaignFromTeacher(\AppBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $this->teacher->getCampaign();

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
     * Set teacher from provided Student
     *
     * @param \AppBundle\Entity\Teacher $teacher
     *
     * @return Teacher
     */
    public function setTeacherFromStudent(\AppBundle\Entity\Teacher $teacher = null)
    {
        $this->teacher = $this->student->getTeacher();

        return $this;
    }
}
