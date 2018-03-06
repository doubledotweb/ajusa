<?php

namespace TipsBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Archivo;

/**
 * @ORM\Entity
 * @ORM\Table(name="tips")
 * @ORM\HasLifecycleCallbacks()
 */
class Tip extends Archivo
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
     * @ORM\Column(type="boolean")
     */
    private $destacado;	

	/**
     * @ORM\Column(type="string")
     */
	private $archivo;

    /**
     * Many Users have Many Groups.
     * @ORM\ManyToMany(targetEntity="\TipsBundle\Entity\Categoria", inversedBy="tips")
     * @ORM\JoinTable(name="tips_categorias")
     */
    private $categorias;

    /**
     * Many Users have Many Groups.
     * @ORM\ManyToMany(targetEntity="\TipsBundle\Entity\Keyword", inversedBy="tips")
     * @ORM\JoinTable(name="tips_keywords")
     */
    private $keywords;

	public $archivo_aux;

	public function __construct()
	{
		$this->visible=true;
        $this->destacado=false;
        $this->keywords=new \Doctrine\Common\Collections\ArrayCollection();
        $this->categorias=new \Doctrine\Common\Collections\ArrayCollection();
	}

    /** 
     * @ORM\PrePersist
     */
    public function subir_archivo()
    {
        $fecha=date("Y/m/d");

        $this->archivo=$this->subir("/archivos/".$fecha."/",$this->archivo_aux);
    }


    
    public function actualizar_archivo()
    {
        $fecha=date("Y/m/d");        

        if($this->archivo_aux!=null)
        {
            $this->borrar($this->archivo);

            $this->archivo=$this->subir("/archivos/".$fecha."/",$this->archivo_aux);
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
     * @return Tip
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
     * @return Tip
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
     * @return Tip
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
     * @return Tip
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
     * @return Tip
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
     * @return Tip
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
     * @return Tip
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
     * Set destacado
     *
     * @param boolean $destacado
     *
     * @return Tip
     */
    public function setDestacado($destacado)
    {
        $this->destacado = $destacado;

        return $this;
    }

    /**
     * Get destacado
     *
     * @return boolean
     */
    public function getDestacado()
    {
        return $this->destacado;
    }

    /**
     * Add categoria
     *
     * @param \TipsBundle\Entity\Categoria $categoria
     *
     * @return Tip
     */
    public function addCategoria(\TipsBundle\Entity\Categoria $categoria)
    {
        $this->categorias[] = $categoria;

        return $this;
    }

    /**
     * Remove categoria
     *
     * @param \TipsBundle\Entity\Categoria $categoria
     */
    public function removeCategoria(\TipsBundle\Entity\Categoria $categoria)
    {
        $this->categorias->removeElement($categoria);
    }

    /**
     * Get categorias
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategorias()
    {
        return $this->categorias;
    }

    /**
     * Add keyword
     *
     * @param \TipsBundle\Entity\Keyword $keyword
     *
     * @return Tip
     */
    public function addKeyword(\TipsBundle\Entity\Keyword $keyword)
    {
        $this->keywords[] = $keyword;

        return $this;
    }

    /**
     * Remove keyword
     *
     * @param \TipsBundle\Entity\Keyword $keyword
     */
    public function removeKeyword(\TipsBundle\Entity\Keyword $keyword)
    {
        $this->keywords->removeElement($keyword);
    }

    /**
     * Get keywords
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getKeywords()
    {
        return $this->keywords;
    }
}
