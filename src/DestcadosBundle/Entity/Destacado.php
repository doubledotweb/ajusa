<?php

namespace DestcadosBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="destacados")
 * @ORM\HasLifecycleCallbacks()
 */
class Destacado extends Entity
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
	private $titulo;

	/**
     * @ORM\Column(type="boolean")
     */
	private $visible;

	/**
     * @ORM\Column(type="string")
     */
	private $tipo;

	/**
     * @ORM\Column(type="string")
     */
	private $imagen;

	/**
     * @ORM\Column(type="string",length=500)
     */
	private $resumen;

	/**
     * @ORM\Column(type="string",length=100)
     */
	private $texto_enlace;

	/**
     * @ORM\Column(type="string",length=300)
     */
	private $enlace;
	

	public function __construct	()
	{
		$this->visible=false;
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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return Destacado
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     *
     * @return Destacado
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Destacado
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return Destacado
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * Get imagen
     *
     * @return string
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set resumen
     *
     * @param string $resumen
     *
     * @return Destacado
     */
    public function setResumen($resumen)
    {
        $this->resumen = $resumen;

        return $this;
    }

    /**
     * Get resumen
     *
     * @return string
     */
    public function getResumen()
    {
        return $this->resumen;
    }

    /**
     * Set textoEnlace
     *
     * @param string $textoEnlace
     *
     * @return Destacado
     */
    public function setTextoEnlace($textoEnlace)
    {
        $this->texto_enlace = $textoEnlace;

        return $this;
    }

    /**
     * Get textoEnlace
     *
     * @return string
     */
    public function getTextoEnlace()
    {
        return $this->texto_enlace;
    }

    /**
     * Set enlace
     *
     * @param string $enlace
     *
     * @return Destacado
     */
    public function setEnlace($enlace)
    {
        $this->enlace = $enlace;

        return $this;
    }

    /**
     * Get enlace
     *
     * @return string
     */
    public function getEnlace()
    {
        return $this->enlace;
    }

    /**
     * Set creado
     *
     * @param \DateTime $creado
     *
     * @return Destacado
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
     * @return Destacado
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
     * Set idioma
     *
     * @param string $idioma
     *
     * @return Destacado
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
}
