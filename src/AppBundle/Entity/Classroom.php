<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="classroom",uniqueConstraints={@ORM\UniqueConstraint(columns={"name", "campaign_id"})})
 */
class Classroom
{
    /**
     * @ORM\OneToMany(targetEntity="Student", mappedBy="classroom", cascade={"remove"})
     */
    private $students;

    /**
     * @ORM\OneToMany(targetEntity="Team", mappedBy="classroom", cascade={"remove"})
     */
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity="Causevoxteam", mappedBy="classroom", cascade={"remove"})
     */
    private $causevoxteams;

    /**
     * @ORM\OneToMany(targetEntity="Causevoxfundraiser", mappedBy="classroom", cascade={"remove"})
     */
    private $causevoxfundraisers;

    /**
     * @ORM\OneToMany(targetEntity="Donation", mappedBy="classroom", cascade={"remove"})
     */
    private $donations;


    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="classrooms")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $campaign;


    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Grade
     *
     * @ORM\ManyToOne(targetEntity="Grade", inversedBy="classrooms", cascade={"remove"})
     * @ORM\JoinColumn(name="grade_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull()
     */
    private $grade;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $teacherName;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     * )
     */
    private $email;


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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set grade.
     *
     * @param string $grade
     *
     * @return Classroom
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade.
     *
     * @return string
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set teacherName.
     *
     * @param string $teacherName
     *
     * @return Classroom
     */
    public function setTeacherName($teacherName)
    {
        $this->teacherName = $teacherName;

        return $this;
    }

    /**
     * Get teacherName.
     *
     * @return string
     */
    public function getTeacherName()
    {
        return $this->teacherName;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
        $this->causevoxteams = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add student.
     *
     * @param \AppBundle\Entity\Student $student
     *
     * @return Classroom
     */
    public function addStudent(\AppBundle\Entity\Student $student)
    {
        $this->students[] = $student;

        return $this;
    }

    /**
     * Remove student.
     *
     * @param \AppBundle\Entity\Student $student
     */
    public function removeStudent(\AppBundle\Entity\Student $student)
    {
        $this->students->removeElement($student);
    }

    /**
     * Get students.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * Add causevoxteam.
     *
     * @param \AppBundle\Entity\Causevoxteam $causevoxteams
     *
     * @return Classroom
     */
    public function addCausevoxteam(\AppBundle\Entity\Causevoxteam $causevoxteams)
    {
        $this->causevoxteams[] = $causevoxteams;

        return $this;
    }

    /**
     * Remove causevoxteams.
     *
     * @param \AppBundle\Entity\Causevoxteam $causevoxteams
     */
    public function removeCausevoxteam(\AppBundle\Entity\Causevoxteam $causevoxteams)
    {
        $this->causevoxteams->removeElement($causevoxteams);
    }

    /**
     * Get causevoxteams.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCausevoxteams()
    {
        return $this->causevoxteams;
    }

    public function getClassroomAndGrade()
    {
        return sprintf('%s - %s', $this->grade->getName(), $this->teacherName);
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Classroom
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
     * Add causevoxfundraiser
     *
     * @param \AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser
     *
     * @return Classroom
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
     * Add donation
     *
     * @param \AppBundle\Entity\Donation $donation
     *
     * @return Classroom
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
     * Set campaign
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Classroom
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
    public function getGradeCampaign()
    {
        return $this->grade->campaign;
    }

    /**
     * Set campaign from provided Grade
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Grade
     */
    public function setCampaignFromGrade(\AppBundle\Entity\Campaign $campaign = null)
    {
        $this->campaign = $this->grade->getCampaign();

        return $this;
    }

    /**
     * Add team
     *
     * @param \AppBundle\Entity\Team $team
     *
     * @return Classroom
     */
    public function addTeam(\AppBundle\Entity\Team $team)
    {
        $this->teams[] = $team;

        return $this;
    }

    /**
     * Remove team
     *
     * @param \AppBundle\Entity\Team $team
     */
    public function removeTeam(\AppBundle\Entity\Team $team)
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
     * Set name
     *
     * @param string $name
     *
     * @return Classroom
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Classroom
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
     * @return Classroom
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
     * @return Classroom
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
