<?php

namespace CategoriasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Archivo;

/**
 * @ORM\Entity
 * @ORM\Table(name="categorias_noticias") 
 * @ORM\HasLifecycleCallbacks()
 */
class Categoria extends Archivo
{
 
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $nombre;

    /**
     * @ORM\Column(type="array")
     */
    private $slug;    

    /**
	* @ORM\Column(type="datetime")
    */
    private $created;

    /**
	* @ORM\Column(type="datetime",nullable=true)
    */
    private $modification;

    /**
    * @ORM\Column(type="string",nullable=true)
    */
    private $imagen;

    public $imagen_aux;


    /**
     * One Product has Many Features.
     * @ORM\OneToMany(targetEntity="\NoticiasBundle\Entity\Noticia", mappedBy="categoria")
     */
    private $noticias;

    public function __construct()
    {    	
        $this->noticias     = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->nombre 		= array("es"=>"","en"=>"");
    	$this->slug      	= array("es"=>"","en"=>"");    	
    }

    /**
	* @ORM\PrePersist
    */
    public function created()
    {
    	$this->created=new \DateTime("now");
    }

    /**
	* @ORM\PreUpdate
    */
    public function modificated()
    {
    	$this->modification=new \DateTime("now");
    }

    public function actualizar_imagen()
    {
        if($this->imagen_aux)
            $this->imagen=$this->subir("/archivos/categorias/img/",$this->imagen_aux);   
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
     * Set nombre
     *
     * @param array $nombre
     *
     * @return Categoria
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return array
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set slug
     *
     * @param array $slug
     *
     * @return Categoria
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return array
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Categoria
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modification
     *
     * @param \DateTime $modification
     *
     * @return Categoria
     */
    public function setModification($modification)
    {
        $this->modification = $modification;

        return $this;
    }

    /**
     * Get modification
     *
     * @return \DateTime
     */
    public function getModification()
    {
        return $this->modification;
    }

    /**
     * Set imagen
     *
     * @param string $imagen
     *
     * @return Categoria
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
     * Set creado
     *
     * @param \DateTime $creado
     *
     * @return Categoria
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
     * @return Categoria
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
     * Add noticia
     *
     * @param \NoticiasBundle\Entity\Noticia $noticia
     *
     * @return Categoria
     */
    public function addNoticia(\NoticiasBundle\Entity\Noticia $noticia)
    {
        $noticia->setCategoria($this);
        $this->noticias[] = $noticia;


        return $this;
    }

    /**
     * Remove noticia
     *
     * @param \NoticiasBundle\Entity\Noticia $noticia
     */
    public function removeNoticia(\NoticiasBundle\Entity\Noticia $noticia)
    {
        $this->noticias->removeElement($noticia);
    }

    /**
     * Get noticias
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNoticias()
    {
        return $this->noticias;
    }
}
