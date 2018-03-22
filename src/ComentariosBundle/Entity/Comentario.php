<?php

namespace ComentariosBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="comentarios")
 * @ORM\HasLifecycleCallbacks()
 */
class Comentario extends Entity
{

	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
	private $id;

    /**
     * @ORM\Column(type="string",length=3)
     */
    private $idioma;

	/**
     * @ORM\Column(type="string")
 	 */
	private $nombre;

    /**
     * @ORM\Column(type="text")
     */
    private $texto;

    /**
     * @ORM\Column(type="boolean")
     */
    private $permitido;

    /**
	 * @ORM\ManyToOne(targetEntity="\NoticiasBundle\Entity\Noticia", inversedBy="comentarios")
     * @ORM\JoinColumn(name="noticia_id", referencedColumnName="id")
     */
    private $noticia;


    public function __construct()
    {
        $this->permitido=false;
    }
    /**
     * @ORM\PrePersist
     */
    public function sanitize()
    {
        $this->nombre=strip_tags($this->nombre);
        $this->texto=strip_tags($this->texto);
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
     * Set idioma
     *
     * @param string $idioma
     *
     * @return Comentario
     */
    public function setIdioma($idioma)
    {
        $this->idioma = $idioma;

        return $this;
    }

    /**
     * Get idioma
     *
     * @return string
     */
    public function getIdioma()
    {
        return $this->idioma;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Comentario
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set texto
     *
     * @param string $texto
     *
     * @return Comentario
     */
    public function setTexto($texto)
    {
        $this->texto = $texto;

        return $this;
    }

    /**
     * Get texto
     *
     * @return string
     */
    public function getTexto()
    {
        return $this->texto;
    }

    /**
     * Set creado
     *
     * @param \DateTime $creado
     *
     * @return Comentario
     */
    public function setCreado($creado)
    {
        $this->creado = $creado;

        return $this;
    }

    /**
     * Get creado
     *
     * @return \DateTime
     */
    public function getCreado()
    {
        return $this->creado;
    }

    /**
     * Set modificado
     *
     * @param \DateTime $modificado
     *
     * @return Comentario
     */
    public function setModificado($modificado)
    {
        $this->modificado = $modificado;

        return $this;
    }

    /**
     * Get modificado
     *
     * @return \DateTime
     */
    public function getModificado()
    {
        return $this->modificado;
    }

    /**
     * Set noticia
     *
     * @param \NoticiasBundle\Entity\Noticia $noticia
     *
     * @return Comentario
     */
    public function setNoticia(\NoticiasBundle\Entity\Noticia $noticia = null)
    {
        $this->noticia = $noticia;

        return $this;
    }

    /**
     * Get noticia
     *
     * @return \NoticiasBundle\Entity\Noticia
     */
    public function getNoticia()
    {
        return $this->noticia;
    }

    /**
     * Set permitido
     *
     * @param boolean $permitido
     *
     * @return Comentario
     */
    public function setPermitido($permitido)
    {
        $this->permitido = $permitido;

        return $this;
    }

    /**
     * Get permitido
     *
     * @return boolean
     */
    public function getPermitido()
    {
        return $this->permitido;
    }
}
