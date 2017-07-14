<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user",uniqueConstraints={@ORM\UniqueConstraint(columns={"email"})})
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $username;


    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $apiKey;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $emailConfirmationCode;

    /**
     * @ORM\Column(type="datetime")
     */
    private $emailConfirmationCodeTimestamp;

    /**
     * @var UserStatus
     *
     * @ORM\ManyToOne(targetEntity="UserStatus", inversedBy="users")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $userStatus;

    /**
     * @ORM\OneToMany(targetEntity="CampaignUser", mappedBy="user", cascade={"remove"})
     */
    private $campaignUsers;

    /**
     * @ORM\OneToMany(targetEntity="Campaign", mappedBy="user", cascade={"remove"})
     */
    private $campaigns;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=55)
     * @Assert\NotNull()
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=55)
     * @Assert\NotNull()
     */
    private $lastName;

    /**
     *
     * @ORM\Column(type="integer", nullable = true)
     */
    private $defaultCampaignId = null;


    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    public function __construct()
    {
        $this->isActive = true;
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid(null, true));
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return User
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set defaultCampaignId
     *
     * @param integer $defaultCampaignId
     *
     * @return User
     */
    public function setDefaultCampaignId($defaultCampaignId)
    {
        $this->defaultCampaignId = $defaultCampaignId;

        return $this;
    }

    /**
     * Get defaultCampaignId
     *
     * @return integer
     */
    public function getDefaultCampaignId()
    {
        return $this->defaultCampaignId;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Add campaignUser
     *
     * @param \AppBundle\Entity\CampaignUser $campaignUser
     *
     * @return User
     */
    public function addCampaignUser(\AppBundle\Entity\CampaignUser $campaignUser)
    {
        $this->campaignUsers[] = $campaignUser;

        return $this;
    }

    /**
     * Remove campaignUser
     *
     * @param \AppBundle\Entity\CampaignUser $campaignUser
     */
    public function removeCampaignUser(\AppBundle\Entity\CampaignUser $campaignUser)
    {
        $this->campaignUsers->removeElement($campaignUser);
    }

    /**
     * Get campaignUsers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCampaignUsers()
    {
        return $this->campaignUsers;
    }

    /**
     * Add campaign
     *
     * @param \AppBundle\Entity\Campaign $campaign
     *
     * @return User
     */
    public function addCampaign(\AppBundle\Entity\Campaign $campaign)
    {
        $this->campaigns[] = $campaign;

        return $this;
    }

    /**
     * Remove campaign
     *
     * @param \AppBundle\Entity\Campaign $campaign
     */
    public function removeCampaign(\AppBundle\Entity\Campaign $campaign)
    {
        $this->campaigns->removeElement($campaign);
    }

    /**
     * Get campaigns
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }



    /**
     * Set emailConfirmationCode
     *
     * @param string $emailConfirmationCode
     *
     * @return User
     */
    public function setEmailConfirmationCode($emailConfirmationCode)
    {
        $this->emailConfirmationCode = $emailConfirmationCode;

        return $this;
    }

    /**
     * Get emailConfirmationCode
     *
     * @return string
     */
    public function getEmailConfirmationCode()
    {
        return $this->emailConfirmationCode;
    }

    /**
     * Set emailConfirmationCodeTimestamp
     *
     * @param \DateTime $emailConfirmationCodeTimestamp
     *
     * @return User
     */
    public function setEmailConfirmationCodeTimestamp($emailConfirmationCodeTimestamp)
    {
        $this->emailConfirmationCodeTimestamp = $emailConfirmationCodeTimestamp;

        return $this;
    }

    /**
     * Get emailConfirmationCodeTimestamp
     *
     * @return \DateTime
     */
    public function getEmailConfirmationCodeTimestamp()
    {
        return $this->emailConfirmationCodeTimestamp;
    }


    /**
     * Set userStatus
     *
     * @param \AppBundle\Entity\UserStatus $userStatus
     *
     * @return User
     */
    public function setUserStatus(\AppBundle\Entity\UserStatus $userStatus = null)
    {
        $this->userStatus = $userStatus;

        return $this;
    }

    /**
     * Get userStatus
     *
     * @return \AppBundle\Entity\UserStatus
     */
    public function getUserStatus()
    {
        return $this->userStatus;
    }
}
