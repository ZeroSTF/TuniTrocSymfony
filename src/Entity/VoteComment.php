<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * Vote
 *
 * @ORM\Table(name="votecomment")
 * @ORM\Entity
 */
class VoteComment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;
    /**
     *
     *
     * @ORM\ManyToOne(targetEntity="Commentaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_comm", referencedColumnName="id_commentaire",onDelete="CASCADE")
     * })
     */
    private $idcomment;
    /**
     *
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="iduser", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $idClient;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getIdcomment()
    {
        return $this->idcomment;
    }

    /**
     * @param mixed $idcomment
     */
    public function setIdcomment($idcomment): void
    {
        $this->idcomment = $idcomment;
    }

    /**
     * @return mixed
     */
    public function getIdClient()
    {
        return $this->idClient;
    }

    /**
     * @param mixed $idClient
     */
    public function setIdClient($idClient): void
    {
        $this->idClient = $idClient;
    }



}