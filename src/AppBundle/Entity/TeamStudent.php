<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="team_students",uniqueConstraints={@ORM\UniqueConstraint(columns={"student_id", "team_id"})})
 * @UniqueEntity(
 *     fields={"user_id", "campaign_id"},
 *     errorPath="name",
 *     message="Duplicate Campaign Entry for Identified User"
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
   * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
   * @Assert\NotNull()
   */
  private $student;


  /**
   * @var Team
   *
   * @ORM\ManyToOne(targetEntity="Team", inversedBy="teamStudents")
   * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
   * @Assert\NotNull()
   */
  private $team;

  /**
   * @ORM\Column(type="string", length=100)
   * @Assert\NotNull()
   */
  private $name;


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
     * @param \AppBundle\Entity\Student $student
     *
     * @return TeamStudent
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
     * Set team
     *
     * @param \AppBundle\Entity\Team $team
     *
     * @return TeamStudent
     */
    public function setTeam(\AppBundle\Entity\Team $team = null)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return \AppBundle\Entity\Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set createdBy
     *
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return TeamStudent
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
