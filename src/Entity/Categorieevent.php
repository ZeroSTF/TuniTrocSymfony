<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Categorieevent
 *
 * @ORM\Table(name="categorieevent")
 * @ORM\Entity
 */
class Categorieevent
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
     * @var string|null
     * @Assert\NotBlank
     * @ORM\Column(name="descrption", type="string", length=50, nullable=true, options={"default"="NULL"})
     */
    private $descrption = 'NULL';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescrption(): ?string
    {
        return $this->descrption;
    }

    public function setDescrption(?string $descrption): self
    {
        $this->descrption = $descrption;

        return $this;
    }


}
