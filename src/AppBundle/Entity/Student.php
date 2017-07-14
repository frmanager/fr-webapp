<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="student",uniqueConstraints={@ORM\UniqueConstraint(columns={"name", "teacher_id", "campaign_id"})})
 * @UniqueEntity(
 *     fields={"name", "teacher"},
 *     errorPath="name",
 *     message="Duplicate Student for Identified Teacher"
 * )
 */
class Student
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotNull()
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var Teacher
     *
     * @ORM\ManyToOne(targetEntity="Teacher", inversedBy="students")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $teacher;

    /**
     * @ORM\OneToMany(targetEntity="Donation", mappedBy="student", cascade={"remove"})
     */
    private $donations;

    /**
     * @ORM\OneToMany(targetEntity="Causevoxfundraiser", mappedBy="student", cascade={"remove"})
     */
    private $causevoxfundraisers;


    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="students")
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
     * Get id.
     *
     * @return array
     */
    public function getGrades()
    {
        return $this;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Student
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set teacher.
     *
     * @param string $teacher
     *
     * @return Student
     */
    public function setTeacher($teacher)
    {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * Get teacher.
     *
     * @return string
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * Set grade.
     *
     * @param string $grade
     *
     * @return Student
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
     * Constructor.
     */
    public function __construct()
    {
        $this->causevoxfundraisers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add causevoxfundraiser.
     *
     * @param \AppBundle\Entity\Causevoxfundraiser $causevoxfundraisers
     *
     * @return Student
     */
    public function addCausevoxfundraisers(\AppBundle\Entity\Causevoxfundraiser $causevoxfundraisers)
    {
        $this->causevoxfundraisers[] = $causevoxfundraisers;

        return $this;
    }

    /**
     * Remove causevoxfundraisers.
     *
     * @param \AppBundle\Entity\Causevoxfundraiser $causevoxfundraisers
     */
    public function removeCausevoxfundraisers(\AppBundle\Entity\Causevoxfundraiser $causevoxfundraisers)
    {
        $this->causevoxfundraisers->removeElement($causevoxfundraisers);
    }

    /**
     * Get causevoxfundraisers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCausevoxfundraisers()
    {
        return $this->causevoxfundraisers;
    }

    public function getStudentAndTeacher()
    {
        return sprintf('%s - %s - %s', $this->teacher->getGrade()->getName(), $this->teacher->getTeacherName(), $this->name);
    }

    /**
     * Add donation.
     *
     * @param \AppBundle\Entity\Donation $donation
     *
     * @return Student
     */
    public function addDonation(\AppBundle\Entity\Donation $donation)
    {
        $this->donations[] = $donation;

        return $this;
    }

    /**
     * Remove donation.
     *
     * @param \AppBundle\Entity\Donation $donation
     */
    public function removeDonation(\AppBundle\Entity\Donation $donation)
    {
        $this->donations->removeElement($donation);
    }

    /**
     * Get donations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDonations()
    {
        return $this->donations;
    }

    /**
     * Add causevoxfundraiser.
     *
     * @param \AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser
     *
     * @return Student
     */
    public function addCausevoxfundraiser(\AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser)
    {
        $this->causevoxfundraisers[] = $causevoxfundraiser;

        return $this;
    }

    /**
     * Remove causevoxfundraiser.
     *
     * @param \AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser
     */
    public function removeCausevoxfundraiser(\AppBundle\Entity\Causevoxfundraiser $causevoxfundraiser)
    {
        $this->causevoxfundraisers->removeElement($causevoxfundraiser);
    }

    /**
     * Set campaign
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Student
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
}
