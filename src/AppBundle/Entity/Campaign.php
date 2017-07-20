<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="campaign",uniqueConstraints={@ORM\UniqueConstraint(columns={"url"})})
 */
class Campaign
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
   * @ORM\Column(type="string", length=255, nullable=true)
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
   * @ORM\OneToMany(targetEntity="Teacher", mappedBy="campaign", cascade={"remove"})
   */
  private $teachers;

  /**
   * @ORM\OneToMany(targetEntity="Donation", mappedBy="campaign", cascade={"remove"})
   */
  private $donations;


  /**
   * @ORM\OneToMany(targetEntity="Campaignaward", mappedBy="campaign", cascade={"remove"})
   */
  private $campaignawards;



/**
 * @ORM\OneToMany(targetEntity="Causevoxfundraiser", mappedBy="campaign", cascade={"remove"})
 */
private $causevoxfundraisers;


/**
 * @ORM\OneToMany(targetEntity="Causevoxteam", mappedBy="campaign", cascade={"remove"})
 */
private $causevoxteams;


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
        $this->teachers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->donations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->campaignawards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->causevoxfundraisers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->causevoxteams = new \Doctrine\Common\Collections\ArrayCollection();
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \AppBundle\Entity\CampaignUser $campaignUser
     *
     * @return Campaign
     */
    public function addCampaignUser(\AppBundle\Entity\CampaignUser $campaignUser)
    {
        $this->campaignUsers[] = $campaignUser;

        return $this;
    }

    /**
     * Remove campaignUser
     *
     * @param \AppBundle\Entity\CampaignUser $campaignUser
     */
    public function removeCampaignUser(\AppBundle\Entity\CampaignUser $campaignUser)
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
     * Add teacher
     *
     * @param \AppBundle\Entity\Teacher $teacher
     *
     * @return Campaign
     */
    public function addTeacher(\AppBundle\Entity\Teacher $teacher)
    {
        $this->teachers[] = $teacher;

        return $this;
    }

    /**
     * Remove teacher
     *
     * @param \AppBundle\Entity\Teacher $teacher
     */
    public function removeTeacher(\AppBundle\Entity\Teacher $teacher)
    {
        $this->teachers->removeElement($teacher);
    }

    /**
     * Get teachers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeachers()
    {
        return $this->teachers;
    }

    /**
     * Add donation
     *
     * @param \AppBundle\Entity\Donation $donation
     *
     * @return Campaign
     */
    public function addDonation(\AppBundle\Entity\Donation $donation)
    {
        $this->donations[] = $donation;

        return $this;
    }

    /**
     * Remove donation
     *
     * @param \AppBundle\Entity\Donation $donation
     */
    public function removeDonation(\AppBundle\Entity\Donation $donation)
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
     * @param \AppBundle\Entity\Campaignaward $campaignaward
     *
     * @return Campaign
     */
    public function addCampaignaward(\AppBundle\Entity\Campaignaward $campaignaward)
    {
        $this->campaignawards[] = $campaignaward;

        return $this;
    }

    /**
     * Remove campaignaward
     *
     * @param \AppBundle\Entity\Campaignaward $campaignaward
     */
    public function removeCampaignaward(\AppBundle\Entity\Campaignaward $campaignaward)
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
     * Add causevoxfundraiser
     *
     * @param \AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser
     *
     * @return Campaign
     */
    public function addCausevoxfundraiser(\AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser)
    {
        $this->causevoxfundraisers[] = $causevoxfundraiser;

        return $this;
    }

    /**
     * Remove causevoxfundraiser
     *
     * @param \AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser
     */
    public function removeCausevoxfundraiser(\AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser)
    {
        $this->causevoxfundraisers->removeElement($causevoxfundraiser);
    }

    /**
     * Get causevoxfundraisers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCausevoxfundraisers()
    {
        return $this->causevoxfundraisers;
    }

    /**
     * Add causevoxteam
     *
     * @param \AppBundle\Entity\Causevoxteam $causevoxteam
     *
     * @return Campaign
     */
    public function addCausevoxteam(\AppBundle\Entity\Causevoxteam $causevoxteam)
    {
        $this->causevoxteams[] = $causevoxteam;

        return $this;
    }

    /**
     * Remove causevoxteam
     *
     * @param \AppBundle\Entity\Causevoxteam $causevoxteam
     */
    public function removeCausevoxteam(\AppBundle\Entity\Causevoxteam $causevoxteam)
    {
        $this->causevoxteams->removeElement($causevoxteam);
    }

    /**
     * Get causevoxteams
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCausevoxteams()
    {
        return $this->causevoxteams;
    }

    /**
     * Add student
     *
     * @param \AppBundle\Entity\Student $student
     *
     * @return Campaign
     */
    public function addStudent(\AppBundle\Entity\Student $student)
    {
        $this->students[] = $student;

        return $this;
    }

    /**
     * Remove student
     *
     * @param \AppBundle\Entity\Student $student
     */
    public function removeStudent(\AppBundle\Entity\Student $student)
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
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return Campaign
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

    /**
     * Add grade
     *
     * @param \AppBundle\Entity\Grade $grade
     *
     * @return Campaign
     */
    public function addGrade(\AppBundle\Entity\Grade $grade)
    {
        $this->grades[] = $grade;

        return $this;
    }

    /**
     * Remove grade
     *
     * @param \AppBundle\Entity\Grade $grade
     */
    public function removeGrade(\AppBundle\Entity\Grade $grade)
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

}
