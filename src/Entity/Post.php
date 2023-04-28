<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */   
class Post
{
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le titre ne peut pas être vide.")
     * @Assert\Regex(pattern="/^[a-zA-Z\s]+$/", message="Le titre ne peut contenir que des lettres et des espaces.")
     */
    private $titre;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Le contenu ne peut pas être vide.")
     * @Assert\Regex(pattern="/^[a-zA-Z\s]+$/", message="Le contenu ne peut contenir que des lettres et des espaces.")
     */
    private $contenu;



    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $id_user;

    /**
     * @ORM\Column(type="integer")
     */
    private $likes;

    /**
     * @ORM\Column(type="integer")
     */
    private $dislikes;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Image(
     *     mimeTypesMessage = "Please upload a valid image"
     * )
     */
    private $img;

    /**
     * @Vich\UploadableField(mapping="post_images", fileNameProperty="img")
     */
    private $imgFile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $id_user): self
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    public function getDislikes(): ?int
    {
        return $this->dislikes;
    }

    public function setDislikes(int $dislikes): self
    {
        $this->dislikes = $dislikes;

        return $this;
    }


    


    public function __toString()
{
    return sprintf(
        'Post #%d - %s (written by user #%d on %s) - %d likes, %d dislikes',
        $this->id,
        $this->titre,
        $this->id_user,
        $this->date->format('Y-m-d H:i:s'),
        $this->likes, 
        $this->dislikes,
        $this->img
    );
}
public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getImgFile(): ?File
    {
        return $this->imgFile;
    }

    public function setImgFile(?File $imgFile = null): void
    {
        $this->imgFile = $imgFile;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload(): void
    {
        if (null !== $this->imgFile) {
            // do whatever you want to generate a unique name
            $this->img = uniqid('', true).'.'.$this->imgFile->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload(): void
    {
        if (null === $this->imgFile) {
            return;
        }

        $this->imgFile->move(
            $this->getUploadDir(),
            $this->img
        );

        unset($this->imgFile);
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeUpload(): void
    {
        if ($img = $this->getAbsolutePath()) {
            unlink($img);
        }
    }

    public function getAbsolutePath(): ?string
    {
        return null === $this->img ? null : $this->getUploadDir().'/'.$this->img;
    }

    public function getWebPath(): ?string
    {
        return null === $this->img ? null : $this->getUploadDir().'/'.$this->img;
    }

    protected function getUploadDir(): string
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/post';
    }
}
