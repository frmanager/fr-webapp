<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="grade",uniqueConstraints={@ORM\UniqueConstraint(columns={"name", "campaign_id"})})
 */
class Grade
{
    /**
     * @ORM\OneToMany(targetEntity="Classroom", mappedBy="grade", cascade={"remove"})
     */
    private $classrooms;

    /**
     * @ORM\OneToMany(targetEntity="Student", mappedBy="grade", cascade={"remove"})
     */
    private $students;

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
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="grades")
     * @ORM\JoinColumn(referencedColumnName="id")
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
     * Set name.
     *
     * @param string $name
     *
     * @return Grade
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
     * Constructor.
     */
    public function __construct()
    {
        $this->classrooms = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add classroom.
     *
     * @param \App\Entity\Classroom $classroom
     *
     * @return Grade
     */
    public function addClassroom(\App\Entity\Classroom $classroom)
    {
        $this->classrooms[] = $classroom;

        return $this;
    }

    /**
     * Remove classroom.
     *
     * @param \App\Entity\Classroom $classroom
     */
    public function removeClassroom(\App\Entity\Classroom $classroom)
    {
        $this->classrooms->removeElement($classroom);
    }

    /**
     * Get classrooms.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClassrooms()
    {
        return $this->classrooms;
    }

    /**
     * Set campaign
     *
     * @param \App\Entity\Campaign $campaign
     *
     * @return Grade
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
     * Add student
     *
     * @param \App\Entity\Student $student
     *
     * @return Grade
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
}
