<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transporteur
 *
 * @ORM\Table(name="transporteur", indexes={@ORM\Index(name="echange_transporteur", columns={"id_echange"})})
 * @ORM\Entity
 */
class Transporteur
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
     * @ORM\Column(name="nom", type="string", length=255, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=false)
     */
    private $prenom;

    /**
     * @var int
     *
     * @ORM\Column(name="num_tel", type="integer", nullable=false)
     */
    private $numTel;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="blob", length=16777215, nullable=false)
     */
    private $photo;

    /**
     * @var \Echange
     *
     * @ORM\ManyToOne(targetEntity="Echange")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_echange", referencedColumnName="id")
     * })
     */
    private $idEchange;


}
