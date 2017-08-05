<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="campaignawardstyle",uniqueConstraints={@ORM\UniqueConstraint(columns={"value"}), @ORM\UniqueConstraint(columns={"name"})})
 * @UniqueEntity(fields={"name"})
 * @UniqueEntity(fields={"value"})
 */
class Campaignawardstyle
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotNull()
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Campaignaward", mappedBy="campaignawardstyle", cascade={"remove"})
     */
    private $campaignawards;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->campaignawards = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Campaignawardstyle
     */
    public function setDisplayName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Campaignawardstyle
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Campaignawardstyle
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
     * Add campaignaward
     *
     * @param \AppBundle\Entity\Campaignaward $campaignaward
     *
     * @return Campaignawardstyle
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
     * Set name
     *
     * @param string $name
     *
     * @return Campaignawardstyle
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
}
