<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="team_students",uniqueConstraints={@ORM\UniqueConstraint(columns={"name", "team_id", "classroom_id"})})
 * @UniqueEntity(
 *     fields={"team_id", "classroom_id", "name"},
 *     errorPath="name",
 *     message="Duplicate Student Entry for Identified Team"
 * )
 */
class TeamStudent
{


  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;


  /**
   * @var student
   *
   * @ORM\ManyToOne(targetEntity="Student", inversedBy="teamStudents")
   * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
   *
   */
  private $student;


  /**
   * @var Team
   *
   * @ORM\ManyToOne(targetEntity="Team", inversedBy="teamStudents")
   * @ORM\JoinColumn(referencedColumnName="id")
   * @Assert\NotNull()
   */
  private $team;

  /**
   * @ORM\Column(type="string", length=100)
   * @Assert\NotNull()
   */
  private $name;

  /**
   * @ORM\Column(type="boolean", length=100)
   */
  private $confirmedFlag = false;


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
  * @var Classroom
  *
  * @ORM\ManyToOne(targetEntity="Classroom", inversedBy="teamStudents")
  * @ORM\JoinColumn(referencedColumnName="id")
  * @Assert\NotNull()
  */
 private $classroom;


 /**
  * @var Grade
  *
  * @ORM\ManyToOne(targetEntity="Grade", inversedBy="classrooms")
  * @ORM\JoinColumn(name="grade_id", referencedColumnName="id")
  * @Assert\NotNull()
  */
 private $grade;

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
     * @return TeamStudent
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
     * @return TeamStudent
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
     * @return TeamStudent
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
     * Set student
     *
     * @param \App\Entity\Student $student
     *
     * @return TeamStudent
     */
    public function setStudent(\App\Entity\Student $student = null)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \App\Entity\Student
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set team
     *
     * @param \App\Entity\Team $team
     *
     * @return TeamStudent
     */
    public function setTeam(\App\Entity\Team $team = null)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return \App\Entity\Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set createdBy
     *
     * @param \App\Entity\User $createdBy
     *
     * @return TeamStudent
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
     * Set confirmedFlag
     *
     * @param boolean $confirmedFlag
     *
     * @return TeamStudent
     */
    public function setConfirmedFlag($confirmedFlag)
    {
        $this->confirmedFlag = $confirmedFlag;

        return $this;
    }

    /**
     * Get confirmedFlag
     *
     * @return boolean
     */
    public function getConfirmedFlag()
    {
        return $this->confirmedFlag;
    }

    /**
     * Set classroom
     *
     * @param \App\Entity\Classroom $classroom
     *
     * @return TeamStudent
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
     * Set grade
     *
     * @param \App\Entity\Grade $grade
     *
     * @return TeamStudent
     */
    public function setGrade(\App\Entity\Grade $grade = null)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return \App\Entity\Grade
     */
    public function getGrade()
    {
        return $this->grade;
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
     * Set Grade from provided Classroom
     *
     * @param \App\Entity\Grade $grade
     *
     * @return Grade
     */
    public function setGradeFromStudent(\App\Entity\Grade $grade = null)
    {
        $this->grade = $this->student->getGrade();

        return $this;
    }


    /**
     * Set Classroom from provided Student
     *
     * @param \App\Entity\Classroom $classroom
     *
     * @return Classroom
     */
    public function setClassroomFromStudent(\App\Entity\Classroom $classroom = null)
    {
        $this->classroom = $this->getStudent()->getClassroom();

        return $this;
    }

}
