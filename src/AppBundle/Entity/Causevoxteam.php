<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="causevoxteam",uniqueConstraints={@ORM\UniqueConstraint(columns={"url"})})
 * @UniqueEntity(
 *     fields={"url"},
 *     errorPath="url",
 *     message="This URL is already registered"
 * )
 */
class Causevoxteam
{
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
     * @ORM\Column(type="string", length=100)
     * @Assert\NotNull()
     */
    private $url;

    /**
     * @ORM\Column(type="float", length=5, nullable=true)
     */
    private $fundsRaised = null;

    /**
     * @ORM\Column(type="float", length=5, nullable=true)
     */
    private $fundsNeeded = null;

    /**
     * @var Classroom
     *
     * @ORM\ManyToOne(targetEntity="Classroom", inversedBy="causevoxteams")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $classroom;


    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="causevoxteams")
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
     * @return CauseVoxTeam
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
     * Set url.
     *
     * @param string $url
     *
     * @return CauseVoxTeam
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set fundsRaised.
     *
     * @param string $fundsRaised
     *
     * @return CauseVoxTeam
     */
    public function setFundsRaised($fundsRaised)
    {
        $this->fundsRaised = $fundsRaised;

        return $this;
    }

    /**
     * Get fundsRaised.
     *
     * @return string
     */
    public function getFundsRaised()
    {
        return $this->fundsRaised;
    }

    /**
     * Set fundsNeeded.
     *
     * @param string $fundsNeeded
     *
     * @return CauseVoxTeam
     */
    public function setFundsNeeded($fundsNeeded)
    {
        $this->fundsNeeded = $fundsNeeded;

        return $this;
    }

    /**
     * Get fundsNeeded.
     *
     * @return string
     */
    public function getFundsNeeded()
    {
        return $this->fundsNeeded;
    }

    /**
     * Set classroom.
     *
     * @param string $classroom
     *
     * @return CauseVoxTeam
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
     * Set campaign
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Causevoxteam
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
