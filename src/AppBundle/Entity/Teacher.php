<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="teacher",uniqueConstraints={@ORM\UniqueConstraint(columns={"teacher_name", "grade_id", "campaign_id"})})
 */
class Teacher
{
    /**
     * @ORM\OneToMany(targetEntity="Student", mappedBy="teacher", cascade={"remove"})
     */
    private $students;

    /**
     * @ORM\OneToMany(targetEntity="Team", mappedBy="teacher", cascade={"remove"})
     */
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity="Causevoxteam", mappedBy="teacher", cascade={"remove"})
     */
    private $causevoxteams;

    /**
     * @ORM\OneToMany(targetEntity="Causevoxfundraiser", mappedBy="teacher", cascade={"remove"})
     */
    private $causevoxfundraisers;

    /**
     * @ORM\OneToMany(targetEntity="Donation", mappedBy="teacher", cascade={"remove"})
     */
    private $donations;


    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="teachers")
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
     * @ORM\ManyToOne(targetEntity="Grade", inversedBy="teachers", cascade={"remove"})
     * @ORM\JoinColumn(name="grade_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $grade;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotNull()
     */
    private $teacherName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     * )
     */
    private $email;

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
     * @return Teacher
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
     * @return Teacher
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
    }

    /**
     * Add student.
     *
     * @param \AppBundle\Entity\Student $student
     *
     * @return Teacher
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
     * @return Teacher
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

    public function getTeacherAndGrade()
    {
        return sprintf('%s - %s', $this->grade->getName(), $this->teacherName);
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Teacher
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
     * @return Teacher
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
     * @return Teacher
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
     * @return Teacher
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
     * @return Teacher
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
}
