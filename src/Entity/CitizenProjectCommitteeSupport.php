<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

/**
 * This entity represents a link between CitizenProject and Committee.
 *
 * @ORM\Table(
 *   name="citizen_project_committee_supports"
 * )
 * @ORM\Entity()
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProjectCommitteeSupport
{
    const PENDING = 'PENDING';
    const APPROVE = 'APPROVE';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer $id
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenProject", inversedBy="committeeSupports")
     */
    private $citizenProject;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee", inversedBy="citizenProjectSupports")
     */
    private $committee;

    /**
     * @ORM\Column(length=20)
     */
    private $status;

    /**
     * The timestamp when an citizenProject ask support.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $requestedAt;

    /**
     * The timestamp when an committee accept.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedAt;

    public function __construct(
        CitizenProject $citizenProject,
        Committee $committee,
        string $status = 'PENDING',
        string $requestedAt = 'now',
        string $approvedAt = null
    ) {
        $this->citizenProject = $citizenProject;
        $this->committee = $committee;
        $this->status = $status;

        if ($requestedAt) {
            $requestedAt = new \DateTimeImmutable($requestedAt);
        }

        if ($approvedAt) {
            $approvedAt = new \DateTimeImmutable($approvedAt);
        }

        $this->requestedAt = $requestedAt;
        $this->approvedAt = $approvedAt;
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }

    public function setCitizenProject(CitizenProject $citizenProject): void
    {
        $this->citizenProject = $citizenProject;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this->status;
    }

    public function isApprove(): bool
    {
        return self::APPROVE === $this->status;
    }

    public function approve(string $timestamp = 'now'): void
    {
        $this->status = self::APPROVE;
        $this->approvedAt = new \DateTime($timestamp);
    }

    public function getRequestedAt(): ?\DateTime
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTime $requestedAt): void
    {
        $this->requestedAt = $requestedAt;
    }

    public function getApprovedAt(): ?\DateTime
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(\DateTime $approvedAt): void
    {
        $this->approvedAt = $approvedAt;
    }

}
