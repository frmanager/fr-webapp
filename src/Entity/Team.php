<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="team",uniqueConstraints={@ORM\UniqueConstraint(columns={"url", "campaign_id"})})
 * @UniqueEntity(
 *     fields={"url","campaign_id"},
 *     errorPath="url",
 *     message="This URL is already registered for this campaign"
 * )
 */
class Team
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotNull()
     */
    private $name;


    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotNull()
     */
    private $url;


    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $imageName;


    /**
     * @ORM\Column(type="float", length=5, nullable=true)
     */
    private $fundsRaised = 0.00;


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
    private $fundingGoal = 0.00;


    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="teams")
     * @ORM\JoinColumn(referencedColumnName="id")
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
    * @ORM\OneToMany(targetEntity="Donation", mappedBy="team")
    */
   private $donations;

   /**
    * @ORM\OneToMany(targetEntity="DonationDatabase", mappedBy="team")
    */
   private $donationDatabases;


   /**
    * @var TeamType
    *
    * @ORM\ManyToOne(targetEntity="TeamType", inversedBy="teams")
    * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
    * @Assert\NotNull()
    */
   private $teamType;

   /**
    * @var Classroom
    *
    * @ORM\ManyToOne(targetEntity="Classroom", inversedBy="teams")
    * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
    */
   private $classroom;


   /**
    * @ORM\OneToMany(targetEntity="TeamStudent", mappedBy="team", cascade={"remove"})
    */
   private $teamStudents;

   /**
    * @var User
    *
    * @ORM\ManyToOne(targetEntity="User", inversedBy="teams")
    * @ORM\JoinColumn(referencedColumnName="id")
    * @Assert\NotNull()
    */
   private $user;

   /**
    * Constructor
    */
   public function __construct()
   {
       $this->donations = new \ArrayCollection();
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
     * @return Team
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
     * Set url
     *
     * @param string $url
     *
     * @return Team
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
     * Set fundsRaised
     *
     * @param float $fundsRaised
     *
     * @return Team
     */
    public function setFundsRaised($fundsRaised)
    {
        $this->fundsRaised = $fundsRaised;

        return $this;
    }

    /**
     * Get fundsRaised
     *
     * @return float
     */
    public function getFundsRaised()
    {
        return $this->fundsRaised;
    }

    /**
     * Set fundingGoal
     *
     * @param float $fundingGoal
     *
     * @return Team
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Team
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
     * @return Team
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
     * Set campaign
     *
     * @param \App\Entity\Campaign $campaign
     *
     * @return Team
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
     * Set createdBy
     *
     * @param \App\Entity\User $createdBy
     *
     * @return Team
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
     * Add donation
     *
     * @param \App\Entity\Donation $donation
     *
     * @return Team
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
     * @return \Collection
     */
    public function getDonations()
    {
        return $this->donations;
    }

    /**
     * Set teamType
     *
     * @param \App\Entity\TeamType $teamType
     *
     * @return Team
     */
    public function setTeamType(\App\Entity\TeamType $teamType = null)
    {
        $this->teamType = $teamType;

        return $this;
    }

    /**
     * Get teamType
     *
     * @return \App\Entity\TeamType
     */
    public function getTeamType()
    {
        return $this->teamType;
    }

    /**
     * Get campaign
     *
     * @return \App\Entity\Campaign
     */
    public function getClassroomCampaign()
    {
        return $this->classroom->campaign;
    }

    /**
     * Set campaign from provided Grade
     *
     * @param \App\Entity\Campaign $campaign
     *
     * @return Grade
     */
    public function setCampaignFromClassroom(\App\Entity\Campaign $campaign = null)
    {
        $this->campaign = $this->classroom->getCampaign();

        return $this;
    }

    /**
     * Set classroom
     *
     * @param \App\Entity\Classroom $classroom
     *
     * @return Team
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
     * Add teamStudent
     *
     * @param \App\Entity\TeamStudent $teamStudent
     *
     * @return Team
     */
    public function addTeamStudent(\App\Entity\TeamStudent $teamStudent)
    {
        $this->teamStudents[] = $teamStudent;

        return $this;
    }

    /**
     * Remove teamStudent
     *
     * @param \App\Entity\TeamStudent $teamStudent
     */
    public function removeTeamStudent(\App\Entity\TeamStudent $teamStudent)
    {
        $this->teamStudents->removeElement($teamStudent);
    }

    /**
     * Get teamStudents
     *
     * @return \Collection
     */
    public function getTeamStudents()
    {
        return $this->teamStudents;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     *
     * @return Team
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Team
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
     * Add donationDatabase
     *
     * @param \App\Entity\DonationDatabase $donationDatabase
     *
     * @return Team
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
     * @return \Collection
     */
    public function getDonationDatabases()
    {
        return $this->donationDatabases;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     *
     * @return Team
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }
}
