<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="campaign",uniqueConstraints={@ORM\UniqueConstraint(columns={"url"})})
 */
Class Campaign
{

  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\NotNull()
   */
  private $name;

  /**
   * @ORM\Column(type="string", length=2000, nullable=true)
   */
  private $description;

  /**
   * @ORM\Column(type="text", length=255, nullable=true)
   */
  private $theme;

  /**
   * @var string
   * @ORM\Column(name="url", type="string", length=100, nullable=false)
   */
  private $url;

  /**
   * @var string
   *
   * @ORM\Column(name="email", type="string", length=100, nullable=false)
   * @Assert\NotBlank()
   */
  private $email;

  /**
   * @ORM\OneToMany(targetEntity="CampaignUser", mappedBy="campaign", cascade={"remove"})
   */
  private $campaignUsers;

  /**
   * @ORM\OneToMany(targetEntity="Grade", mappedBy="campaign", cascade={"remove"})
   */
  private $grades;


  /**
   * @ORM\OneToMany(targetEntity="Team", mappedBy="campaign", cascade={"remove"})
   */
  private $teams;

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
   * @var datetime
   *
   * @ORM\Column(name="start_date", type="datetime")
   * @Assert\Date()
   */
  private $startDate;

  /**
   * @var datetime
   *
   * @ORM\Column(name="end_date", type="datetime")
   * @Assert\Date()
   */
  private $endDate;

  /**
   *  @var boolean
   *
   * @ORM\Column(type="boolean", length=100)
   */
  private $onlineFlag = false;

  /**
   *  @var boolean
   *
   * @ORM\Column(type="boolean", length=100)
   */
  private $teamsFlag = false;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private $paypalEmail;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  private $schoolCoordinatorEmail;

  /**
   *  @var boolean
   *
   * @ORM\Column(type="boolean", length=100)
   */
  private $paypalSandboxFlag = true;

  /**
   *  @var boolean
   *
   * @ORM\Column(type="boolean", length=100)
   */
  private $donationFlag = false;

  /**
   * @var boolean
   *
   * @ORM\Column(type="boolean", length=100)
   */
  private $tippingFlag = false;

  /**
   *  @var blob
   *
   * @ORM\Column(type="blob", nullable=true)
   */
  private $donationReceiptText;

  /**
   * @var float
   *
   * @ORM\Column(name="funding_goal", type="float", precision=10, scale=2, nullable=false)
   * @Assert\Type(
   *     type="float",
   *     message="The value {{ value }} is not a valid {{ type }}."
   * )
   * @Assert\NotNull()
   */
  private $fundingGoal;

  /**/

  /**
   * @ORM\OneToMany(targetEntity="Classroom", mappedBy="campaign", cascade={"remove"})
   */
  private $classrooms;

  /**
   * @ORM\OneToMany(targetEntity="Donation", mappedBy="campaign", cascade={"remove"})
   */
  private $donations;

  /**
   * @ORM\OneToMany(targetEntity="DonationDatabase", mappedBy="campaign", cascade={"remove"})
   */
  private $donationDatabases;

  /**
   * @ORM\OneToMany(targetEntity="Campaignaward", mappedBy="campaign", cascade={"remove"})
   */
  private $campaignawards;

  /**
   * @ORM\OneToMany(targetEntity="Student", mappedBy="campaign", cascade={"remove"})
   */
  private $students;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->campaignUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->classrooms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->donations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->campaignawards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
        $this->teams = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Campaign
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Campaign
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Campaign
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Campaign
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Campaign
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Campaign
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set fundingGoal
     *
     * @param float $fundingGoal
     *
     * @return Campaign
     */
    public function setFundingGoal($fundingGoal)
    {
        $this->fundingGoal = $fundingGoal;

        return $this;
    }

    /**
     * Get fundingGoal
     *
     * @return float
     */
    public function getFundingGoal()
    {
        return $this->fundingGoal;
    }

    /**
     * Add campaignUser
     *
     * @param \App\Entity\CampaignUser $campaignUser
     *
     * @return Campaign
     */
    public function addCampaignUser(\App\Entity\CampaignUser $campaignUser)
    {
        $this->campaignUsers[] = $campaignUser;

        return $this;
    }

    /**
     * Remove campaignUser
     *
     * @param \App\Entity\CampaignUser $campaignUser
     */
    public function removeCampaignUser(\App\Entity\CampaignUser $campaignUser)
    {
        $this->campaignUsers->removeElement($campaignUser);
    }

    /**
     * Get campaignUsers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCampaignUsers()
    {
        return $this->campaignUsers;
    }

    /**
     * Add classroom
     *
     * @param \App\Entity\Classroom $classroom
     *
     * @return Campaign
     */
    public function addClassroom(\App\Entity\Classroom $classroom)
    {
        $this->classrooms[] = $classroom;

        return $this;
    }

    /**
     * Remove classroom
     *
     * @param \App\Entity\Classroom $classroom
     */
    public function removeClassroom(\App\Entity\Classroom $classroom)
    {
        $this->classrooms->removeElement($classroom);
    }

    /**
     * Get classrooms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClassrooms()
    {
        return $this->classrooms;
    }

    /**
     * Add donation
     *
     * @param \App\Entity\Donation $donation
     *
     * @return Campaign
     */
    public function addDonation(\App\Entity\Donation $donation)
    {
        $this->donations[] = $donation;

        return $this;
    }

    /**
     * Remove donation
     *
     * @param \App\Entity\Donation $donation
     */
    public function removeDonation(\App\Entity\Donation $donation)
    {
        $this->donations->removeElement($donation);
    }

    /**
     * Get donations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDonations()
    {
        return $this->donations;
    }

    /**
     * Add campaignaward
     *
     * @param \App\Entity\Campaignaward $campaignaward
     *
     * @return Campaign
     */
    public function addCampaignaward(\App\Entity\Campaignaward $campaignaward)
    {
        $this->campaignawards[] = $campaignaward;

        return $this;
    }

    /**
     * Remove campaignaward
     *
     * @param \App\Entity\Campaignaward $campaignaward
     */
    public function removeCampaignaward(\App\Entity\Campaignaward $campaignaward)
    {
        $this->campaignawards->removeElement($campaignaward);
    }

    /**
     * Get campaignawards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCampaignawards()
    {
        return $this->campaignawards;
    }

    /**
     * Add student
     *
     * @param \App\Entity\Student $student
     *
     * @return Campaign
     */
    public function addStudent(\App\Entity\Student $student)
    {
        $this->students[] = $student;

        return $this;
    }

    /**
     * Remove student
     *
     * @param \App\Entity\Student $student
     */
    public function removeStudent(\App\Entity\Student $student)
    {
        $this->students->removeElement($student);
    }

    /**
     * Get students
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Campaign
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
     * @return Campaign
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
     * @param \App\Entity\User $createdBy
     *
     * @return Campaign
     */
    public function setCreatedBy(\App\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \App\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Add grade
     *
     * @param \App\Entity\Grade $grade
     *
     * @return Campaign
     */
    public function addGrade(\App\Entity\Grade $grade)
    {
        $this->grades[] = $grade;

        return $this;
    }

    /**
     * Remove grade
     *
     * @param \App\Entity\Grade $grade
     */
    public function removeGrade(\App\Entity\Grade $grade)
    {
        $this->grades->removeElement($grade);
    }

    /**
     * Get grades
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGrades()
    {
        return $this->grades;
    }

    /**
     * Set theme
     *
     * @param string $theme
     *
     * @return Campaign
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }


    /**
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));

        if($this->getCreatedAt() == null)
        {
            $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

    /**
     * Set onlineFlag
     *
     * @param boolean $onlineFlag
     *
     * @return Campaign
     */
    public function setOnlineFlag($onlineFlag)
    {
        $this->onlineFlag = $onlineFlag;

        return $this;
    }

    /**
     * Get onlineFlag
     *
     * @return boolean
     */
    public function getOnlineFlag()
    {
        return $this->onlineFlag;
    }


    /**
     * Set schoolCoordinatorEmail
     *
     * @param string $schoolCoordinatorEmail
     *
     * @return Campaign
     */
    public function setSchoolCoordinatorEmail($schoolCoordinatorEmail)
    {
        $this->schoolCoordinatorEmail = $schoolCoordinatorEmail;

        return $this;
    }


    /**
     * Get schoolCoordinatorEmail
     *
     * @return string
     */
    public function getSchoolCoordinatorEmail()
    {
        return $this->schoolCoordinatorEmail;
    }   



    /**
     * Set paypalEmail
     *
     * @param string $paypalEmail
     *
     * @return Campaign
     */
    public function setPaypalEmail($paypalEmail)
    {
        $this->paypalEmail = $paypalEmail;

        return $this;
    }

    
    /**
     * Get paypalEmail
     *
     * @return string
     */
    public function getPaypalEmail()
    {
        return $this->paypalEmail;
    }

    /**
     * Set donationFlag
     *
     * @param boolean $donationFlag
     *
     * @return Campaign
     */
    public function setDonationFlag($donationFlag)
    {
        $this->donationFlag = $donationFlag;

        return $this;
    }

    /**
     * Get donationFlag
     *
     * @return boolean
     */
    public function getDonationFlag()
    {
        return $this->donationFlag;
    }

    /**
     * Set tippingFlag
     *
     * @param boolean $tippingFlag
     *
     * @return Campaign
     */
    public function setTippingFlag($tippingFlag)
    {
        $this->tippingFlag = $tippingFlag;

        return $this;
    }

    /**
     * Get tippingFlag
     *
     * @return boolean
     */
    public function getTippingFlag()
    {
        return $this->tippingFlag;
    }

    /**
     * Set donationReceiptText
     *
     * @param string $donationReceiptText
     *
     * @return Campaign
     */
    public function setDonationReceiptText($donationReceiptText)
    {
        $this->donationReceiptText = $donationReceiptText;

        return $this;
    }

    /**
     * Get donationReceiptText
     *
     * @return string
     */
    public function getDonationReceiptText()
    {
        return $this->donationReceiptText;
    }

    /**
     * Set teamsFlag
     *
     * @param boolean $teamsFlag
     *
     * @return Campaign
     */
    public function setTeamsFlag($teamsFlag)
    {
        $this->teamsFlag = $teamsFlag;

        return $this;
    }

    /**
     * Get teamsFlag
     *
     * @return boolean
     */
    public function getTeamsFlag()
    {
        return $this->teamsFlag;
    }

    /**
     * Set paypalSanboxFlag
     *
     * @param boolean $paypalSandboxFlag
     *
     * @return Campaign
     */
    public function setPaypalSanboxFlag($paypalSandboxFlag)
    {
        $this->paypalSanboxFlag = $paypalSandboxFlag;

        return $this;
    }

    /**
     * Get paypalSanboxFlag
     *
     * @return boolean
     */
    public function getPaypalSanboxFlag()
    {
        return $this->paypalSanboxFlag;
    }

    /**
     * Set paypalSandboxFlag
     *
     * @param boolean $paypalSandboxFlag
     *
     * @return Campaign
     */
    public function setPaypalSandboxFlag($paypalSandboxFlag)
    {
        $this->paypalSandboxFlag = $paypalSandboxFlag;

        return $this;
    }

    /**
     * Get paypalSandboxFlag
     *
     * @return boolean
     */
    public function getPaypalSandboxFlag()
    {
        return $this->paypalSandboxFlag;
    }

    /**
     * Add team
     *
     * @param \App\Entity\Team $team
     *
     * @return Campaign
     */
    public function addTeam(\App\Entity\Team $team)
    {
        $this->teams[] = $team;

        return $this;
    }

    /**
     * Remove team
     *
     * @param \App\Entity\Team $team
     */
    public function removeTeam(\App\Entity\Team $team)
    {
        $this->teams->removeElement($team);
    }

    /**
     * Get teams
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * Add donationDatabase
     *
     * @param \App\Entity\DonationDatabase $donationDatabase
     *
     * @return Campaign
     */
    public function addDonationDatabase(\App\Entity\DonationDatabase $donationDatabase)
    {
        $this->donationDatabases[] = $donationDatabase;

        return $this;
    }

    /**
     * Remove donationDatabase
     *
     * @param \App\Entity\DonationDatabase $donationDatabase
     */
    public function removeDonationDatabase(\App\Entity\DonationDatabase $donationDatabase)
    {
        $this->donationDatabases->removeElement($donationDatabase);
    }

    /**
     * Get donationDatabases
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDonationDatabases()
    {
        return $this->donationDatabases;
    }
}
