<?php

namespace NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\NewsletterBundle;

/**
 * @ORM\Entity
 * @ORM\Table(name="pdfs") 
 * @ORM\HasLifecycleCallbacks()
 */
class Newsletter
{
 
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $nombre;

    public $imagen_aux;


    /**
     * One Product has Many Features.
     * @ORM\OneToMany(targetEntity="\NoticiasBundle\Entity\Noticia", mappedBy="categoria")
     */
    private $noticias;

    public function __construct()
    {    	
        
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
    public function actualizar_imagen()
    {
        if($this->imagen_aux)
            $this->nombre=$this->subir("/archivos/categorias/img/",$this->imagen_aux);   
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
     * @return Newsletter
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Newsletter
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
}
