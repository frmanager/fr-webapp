<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="campaignaward",uniqueConstraints={@ORM\UniqueConstraint(columns={"campaignawardtype_id","campaignawardstyle_id", "place", "amount"})})
 * @UniqueEntity(
 *     fields={"campaignawardtype", "campaignawardstyle", "place", "amount"},
 *     errorPath="campaignawardtype",
 *     message="Cannot have duplicative places or amounts for this type and style"
 * )
 */
class Campaignaward
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Campaignawardtype", inversedBy="campaignawards")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $campaignawardtype;

    /**
     * @ORM\ManyToOne(targetEntity="Campaignawardstyle", inversedBy="campaignawards")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Assert\NotNull()
     */
    private $campaignawardstyle;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="float", length=17, nullable=true)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;


    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="campaignawards")
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
     * @return Campaignaward
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
     * Set place.
     *
     * @param int $place
     *
     * @return Campaignaward
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place.
     *
     * @return int
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return Campaignaward
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Campaignaward
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set campaignawardtype.
     *
     * @param \AppBundle\Entity\Campaignawardtype $campaignawardtype
     *
     * @return Campaignaward
     */
    public function setCampaignawardtype(\AppBundle\Entity\Campaignawardtype $campaignawardtype = null)
    {
        $this->campaignawardtype = $campaignawardtype;

        return $this;
    }

    /**
     * Get campaignawardtype.
     *
     * @return \AppBundle\Entity\Campaignawardtype
     */
    public function getCampaignawardtype()
    {
        return $this->campaignawardtype;
    }

    /**
     * Set campaignawardstyle.
     *
     * @param \AppBundle\Entity\Campaignawardstyle $campaignawardstyle
     *
     * @return Campaignaward
     */
    public function setCampaignawardstyle(\AppBundle\Entity\Campaignawardstyle $campaignawardstyle = null)
    {
        $this->campaignawardstyle = $campaignawardstyle;

        return $this;
    }

    /**
     * Get campaignawardstyle.
     *
     * @return \AppBundle\Entity\Campaignawardstyle
     */
    public function getCampaignawardstyle()
    {
        return $this->campaignawardstyle;
    }

    /**
     * Set campaign
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return Campaignaward
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
