<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
// DON'T forget this use statement!!!
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Causevoxfundraiser.
 *
 * @ORM\Entity
 * @ORM\Table(name="causevoxfundraiser",uniqueConstraints={@ORM\UniqueConstraint(columns={"email"})})
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="This email is already registered"
 * )
 */
class Causevoxfundraiser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=100, nullable=false)
     */
    private $url;

    /**
     * @var float
     *
     * @ORM\Column(name="funds_raised", type="float", precision=10, scale=0, nullable=true)
     */
    private $fundsRaised;

    /**
     * @var float
     *
     * @ORM\Column(name="funds_needed", type="float", precision=10, scale=0, nullable=true)
     */
    private $fundsNeeded;

    /**
     * @var Student
     *
     * @ORM\ManyToOne(targetEntity="Student", inversedBy="causevoxfundraisers")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * @Assert\NotBlank()
     */
    private $student;

    /**
     * @var Classroom
     *
     * @ORM\ManyToOne(targetEntity="Classroom", inversedBy="causevoxfundraisers")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * @Assert\NotBlank()
     */
    private $classroom;


    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="causevoxfundraisers")
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
     * Set email.
     *
     * @param string $email
     *
     * @return Causevoxfundraiser
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Causevoxfundraiser
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
     * @param float $fundsRaised
     *
     * @return Causevoxfundraiser
     */
    public function setFundsRaised($fundsRaised)
    {
        $this->fundsRaised = $fundsRaised;

        return $this;
    }

    /**
     * Get fundsRaised.
     *
     * @return float
     */
    public function getFundsRaised()
    {
        return $this->fundsRaised;
    }

    /**
     * Set fundsNeeded.
     *
     * @param float $fundsNeeded
     *
     * @return Causevoxfundraiser
     */
    public function setFundsNeeded($fundsNeeded)
    {
        $this->fundsNeeded = $fundsNeeded;

        return $this;
    }

    /**
     * Get fundsNeeded.
     *
     * @return float
     */
    public function getFundsNeeded()
    {
        return $this->fundsNeeded;
    }

    /**
     * Set student.
     *
     * @param \AppBundle\Entity\Student $student
     *
     * @return Causevoxfundraiser
     */
    public function setStudent(\AppBundle\Entity\Student $student = null)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student.
     *
     * @return \AppBundle\Entity\Student
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return Causevoxfundraiser
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return Causevoxfundraiser
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set classroom
     *
     * @param \AppBundle\Entity\Classroom $classroom
     *
     * @return Causevoxfundraiser
     */
    public function setClassroom(\AppBundle\Entity\Classroom $classroom = null)
    {
        $this->classroom = $classroom;

        return $this;
    }

    /**
     * Get classroom
     *
     * @return \AppBundle\Entity\Classroom
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
     * @return Causevoxfundraiser
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
