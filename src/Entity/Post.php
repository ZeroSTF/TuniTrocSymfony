<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Post
 *
 * @ORM\Table(name="post", indexes={@ORM\Index(name="id_categorie", columns={"id_categorie"}), @ORM\Index(name="id_user", columns={"id_user"})})
 * @ORM\Entity
 */
#[Vich\Uploadable]
class Post
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_post", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPost;

    /**
     * @var string
     *@Assert\NotBlank
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
    
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_p", type="date", nullable=false)
     */
    private $dateP;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=false)
     */
    private $image;

    #[Vich\UploadableField(mapping: "post_image", fileNameProperty: "image")]


    private $imageFile;

    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     * })
     */
    private $idUser;

    /**
     * @var \Categorieevent
     *
     * @ORM\ManyToOne(targetEntity="Categorieevent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_categorie", referencedColumnName="id")
     * })
     */
    private $idCategorie;

    public function getIdPost(): ?int
    {
        return $this->idPost;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateP(): ?\DateTimeInterface
    {
        return $this->dateP;
    }

    public function setDateP(\DateTimeInterface $dateP): self
    {
        $this->dateP = $dateP;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getIdCategorie(): ?Categorieevent
    {
        return $this->idCategorie;
    }

    public function setIdCategorie(?Categorieevent $idCategorie): self
    {
        $this->idCategorie = $idCategorie;

        return $this;
    }







}
