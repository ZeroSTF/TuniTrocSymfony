<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReclamationRepository;


/**
 * Reclamation
 *
 * @ORM\Table(name="reclamation", indexes={@ORM\Index(name="userR", columns={"id_userR"}), @ORM\Index(name="userS_reclamation", columns={"id_userS"})})
 * @ORM\Entity
 */
class Reclamation
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
     * @ORM\Column(name="cause", type="string", length=255, nullable=false)
     */
    private $cause;

    /**
     * @var bool
     *
     * @ORM\Column(name="etat", type="boolean", nullable=false)
     */
    private $etat;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_userR", referencedColumnName="id")
     * })
     */
    private $idUserr;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_userS", referencedColumnName="id")
     * })
     */
    private $idUsers;

    /**
 * @var string|null
 *
 * @ORM\Column(name="photo", type="string", length=255, nullable=true)
 */
private $photo;

/**
 * @var \DateTime
 *
 * @ORM\Column(name="date", type="datetime", nullable=false)
 */
private $date;


public function getDate(): ?\DateTime
{
    return $this->date;
}

public function setDate(\DateTime $date): self
{
    $this->date = $date;

    return $this;
}


public function getPhoto(): ?string
{
    return $this->photo;
}

public function setPhoto(?string $photo): self
{
    $this->photo = $photo;

    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCause(): ?string
    {
        return $this->cause;
    }

    public function setCause(string $cause): self
    {
        $this->cause = $cause;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getIdUserr(): ?User
    {
        return $this->idUserr;
    }

    public function setIdUserr(?User $idUserr): self
    {
        $this->idUserr = $idUserr;

        return $this;
    }

    public function getIdUsers(): ?User
    {
        return $this->idUsers;
    }

    public function setIdUsers(?User $idUsers): self
    {
        $this->idUsers = $idUsers;

        return $this;
    }


}
