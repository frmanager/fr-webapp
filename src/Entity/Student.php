<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="student",uniqueConstraints={@ORM\UniqueConstraint(columns={"name", "classroom_id"})})
 * @UniqueEntity(
 *     fields={"name", "classroom"},
 *     errorPath="name",
 *     message="Duplicate Student for Identified Classroom"
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
     * @var Classroom
     *
     * @ORM\ManyToOne(targetEntity="Classroom", inversedBy="students")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $classroom;

    /**
     * @ORM\OneToMany(targetEntity="Donation", mappedBy="student")
     */
    private $donations;

    /**
     * @ORM\OneToMany(targetEntity="DonationDatabase", mappedBy="student")
     */
    private $donationDatabases;

    /**
     * @var Grade
     *
     * @ORM\ManyToOne(targetEntity="Grade", inversedBy="students")
     * @ORM\JoinColumn(name="grade_id", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $grade;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="students")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $campaign;

    /**
     * @ORM\OneToMany(targetEntity="TeamStudent", mappedBy="student", cascade={"remove"})
     */
    private $teamStudents;


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
     * Set classroom.
     *
     * @param string $classroom
     *
     * @return Student
     */
    public function setClassroom($classroom)
    {
        $this->classroom = $classroom;

        return $this;
    }

    /**
     * Get classroom.
     *
     * @return string
     */
    public function getClassroom()
    {
        return $this->classroom;
    }


    /**
     * Constructor.
     */
    public function __construct()
    {
    }



    public function getStudentAndClassroom()
    {
        return sprintf('%s - %s - %s', $this->classroom->getGrade()->getName(), $this->classroom->getClassroomName(), $this->name);
    }

    /**
     * Add donation.
     *
     * @param \App\Entity\Donation $donation
     *
     * @return Student
     */
    public function addDonation(\App\Entity\Donation $donation)
    {
        $this->donations[] = $donation;

        return $this;
    }

    /**
     * Remove donation.
     *
     * @param \App\Entity\Donation $donation
     */
    public function removeDonation(\App\Entity\Donation $donation)
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
     * Set campaign
     *
     * @param \App\Entity\Campaign $campaign
     *
     * @return Student
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
     * Set Grade from provided Classroom
     *
     * @param \App\Entity\Grade $grade
     *
     * @return Grade
     */
    public function setGradeFromClassroom(\App\Entity\Grade $grade = null)
    {
        $this->grade = $this->classroom->getGrade();

        return $this;
    }


    /**
     * Add teamStudent
     *
     * @param \App\Entity\TeamStudent $teamStudent
     *
     * @return Student
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeamStudents()
    {
        return $this->teamStudents;
    }

    /**
     * Set grade.
     *
     * @param string $grade
     *
     * @return Grade
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
     * Add donationDatabase
     *
     * @param \App\Entity\DonationDatabase $donationDatabase
     *
     * @return Student
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
