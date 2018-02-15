<?php

namespace PrensaBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Archivo;

/**
 * @ORM\Entity
 * @ORM\Table(name="descargables")
 * @ORM\HasLifecycleCallbacks()
 */
class Descargable extends Archivo
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
	private $categoria;

	/**
     * @ORM\Column(type="string")
     */
	private $archivo;

	public $archivo_aux;
    public $imagen;

	public function __construct()
	{
		$this->visible=false;        
	}

    /** 
     * @ORM\PrePersist
     */
    public function subir_archivo()
    {
        $fecha=date("Y/m/d");

        $this->archivo=$this->subir($this->archivo_aux,"/descargables/".$fecha.
            "/");
    }


    
    public function actualizar_archivo()
    {
        $fecha=date("Y/m/d");        


        switch ($this->categoria) 
        {
            case 'clipping-de-prensa':
                if($this->archivo_aux!=null)
                {
                    $this->borrar($this->archivo);
                    
                    $this->archivo=$this->subir($this->archivo_aux,"/archivos/".$fecha."/");
                }
            break;
            
            case "logotipo":
            case "imagen":
                if($this->imagen!=null)
                {
                    $this->borrar($this->archivo);

                    $this->archivo=$this->subir_base64("/archivos/".$fecha."/",$this->imagen);
                }
            break;
        }
        
    }

    /** 
     * @ORM\PostRemove
     */
    public function eliminar_archivo()
    {
        $this->borrar($this->archivo);
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
     * @return Descargable
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
     * Set titulo
     *
     * @param string $titulo
     *
     * @return Descargable
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
     * @return Descargable
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
     * Set categoria
     *
     * @param string $categoria
     *
     * @return Descargable
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return string
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     *
     * @return Descargable
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;

        return $this;
    }

    /**
     * Get archivo
     *
     * @return string
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set creado
     *
     * @param \DateTime $creado
     *
     * @return Descargable
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
     * @return Descargable
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
}
