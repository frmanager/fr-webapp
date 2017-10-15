<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user",uniqueConstraints={@ORM\UniqueConstraint(columns={"email"})})
 * @codeCoverageIgnore
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $emailConfirmationCode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $emailConfirmationCodeTimestamp;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $passwordResetCode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordResetCodeTimestamp;

    /**
     * @var UserStatus
     *
     * @ORM\ManyToOne(targetEntity="UserStatus", inversedBy="users")
     * @ORM\JoinColumn(name="user_status_id", referencedColumnName="id")
     */
    private $userStatus;

    /**
     * @ORM\OneToMany(targetEntity="CampaignUser", mappedBy="user", cascade={"remove"})
     */
    private $campaignUsers;

    /**
     * @ORM\OneToMany(targetEntity="Team", mappedBy="user")
     */
    private $teams;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=55)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=55)
     */
    private $lastName;

    /**
     *
     * @ORM\Column(type="integer", nullable = true)
     */
    private $defaultCampaignId = null;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $fundraiserFlag = false;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $campaignManagerFlag = false;

    /**
    * @ORM\Column(type="datetime")
    */
   protected $createdAt;


   /**
    * @ORM\Column(type="datetime")
    */
   protected $updatedAt;


    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt= new \DateTime();
        $this->updatedAt= new \DateTime();
        $this->joinDate= new \DateTime();
    }


    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt= new \DateTime();
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
        list(
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


    /**
     * Set fundraiserFlag
     *
     * @param boolean $fundraiserFlag
     *
     * @return User
     */
    public function setFundraiserFlag($fundraiserFlag)
    {
        $this->fundraiserFlag = $fundraiserFlag;

        return $this;
    }

    /**
     * Get fundraiserFlag
     *
     * @return boolean
     */
    public function getFundraiserFlag()
    {
        return $this->fundraiserFlag;
    }

    /**
     * Set campaignManagerFlag
     *
     * @param boolean $campaignManagerFlag
     *
     * @return User
     */
    public function setCampaignManagerFlag($campaignManagerFlag)
    {
        $this->campaignManagerFlag = $campaignManagerFlag;

        return $this;
    }

    /**
     * Get campaignManagerFlag
     *
     * @return boolean
     */
    public function getCampaignManagerFlag()
    {
        return $this->campaignManagerFlag;
    }

    /**
     * Add team
     *
     * @param \AppBundle\Entity\Team $team
     *
     * @return User
     */
    public function addTeam(\AppBundle\Entity\Team $team)
    {
        $this->teams[] = $team;

        return $this;
    }

    /**
     * Remove team
     *
     * @param \AppBundle\Entity\Team $team
     */
    public function removeTeam(\AppBundle\Entity\Team $team)
    {
        $this->teams->removeElement($team);
    }

    /**
     * Get teams
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeams()
    {
        return $this->teams;
    }



    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
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
     * @return User
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
     * Set passwordResetCode
     *
     * @param string $passwordResetCode
     *
     * @return User
     */
    public function setPasswordResetCode($passwordResetCode)
    {
        $this->passwordResetCode = $passwordResetCode;

        return $this;
    }

    /**
     * Get passwordResetCode
     *
     * @return string
     */
    public function getPasswordResetCode()
    {
        return $this->passwordResetCode;
    }

    /**
     * Set passwordResetCodeTimestamp
     *
     * @param \DateTime $passwordResetCodeTimestamp
     *
     * @return User
     */
    public function setPasswordResetCodeTimestamp($passwordResetCodeTimestamp)
    {
        $this->passwordResetCodeTimestamp = $passwordResetCodeTimestamp;

        return $this;
    }

    /**
     * Get passwordResetCodeTimestamp
     *
     * @return \DateTime
     */
    public function getPasswordResetCodeTimestamp()
    {
        return $this->passwordResetCodeTimestamp;
    }
}
