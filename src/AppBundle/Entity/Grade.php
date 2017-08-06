<?php

namespace AppBundle\Entity;

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
     * @param \AppBundle\Entity\Classroom $classroom
     *
     * @return Grade
     */
    public function addClassroom(\AppBundle\Entity\Classroom $classroom)
    {
        $this->classrooms[] = $classroom;

        return $this;
    }

    /**
     * Remove classroom.
     *
     * @param \AppBundle\Entity\Classroom $classroom
     */
    public function removeClassroom(\AppBundle\Entity\Classroom $classroom)
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
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Grade
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

}
