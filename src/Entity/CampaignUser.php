<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity
 * @ORM\Table(name="campaign_users",uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id", "campaign_id"})})
 * @UniqueEntity(
 *     fields={"user_id", "campaign_id"},
 *     errorPath="name",
 *     message="Duplicate Campaign Entry for Identified User"
 * )
 */
class CampaignUser
{


  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;


  /**
   * @var User
   *
   * @ORM\ManyToOne(targetEntity="User", inversedBy="campaignUsers")
   * @ORM\JoinColumn(referencedColumnName="id")
   * @Assert\NotNull()
   */
  private $user;


  /**
   * @var Campaign
   *
   * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="campaignUsers")
   * @ORM\JoinColumn(referencedColumnName="id")
   * @Assert\NotNull()
   */
  private $campaign;


    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=100, nullable=true)
     */
    private $role;


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
     * Set role
     *
     * @param string $role
     *
     * @return CampaignUser
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     *
     * @return CampaignUser
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set campaign
     *
     * @param \App\Entity\Campaign $campaign
     *
     * @return CampaignUser
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
}
