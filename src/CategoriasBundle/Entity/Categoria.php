<?php

namespace CategoriasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;



/**
 * @ORM\Entity
 * @ORM\Table(name="Categorias_noticias") 
 * @ORM\HasLifecycleCallbacks()
 */
class Categoria
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
    * @ORM\ManyToMany(targetEntity="NoticiasBundle\Entity\Noticia", mappedBy="categorias")
    */
    private $noticias;

    public function __construct()
    {    	
    	$this->nombre 		= array("es"=>"","en"=>"");
    	$this->slug      	= array("es"=>"","en"=>"");    	
    }

    /**
	* @ORM\PrePersist
    */
    public function creado()
    {
    	$this->created=new \DateTime("now");
    }

    /**
	* @ORM\PreUpdate
    */
    public function modificado()
    {
    	$this->modification=new \DateTime("now");
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
     * Add noticia
     *
     * @param \NoticiasBundle\Entity\Noticia $noticia
     *
     * @return Categoria
     */
    public function addNoticia(\NoticiasBundle\Entity\Noticia $noticia)
    {
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
