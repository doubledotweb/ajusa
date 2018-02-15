<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;



/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class Entity
{

	

	/**
     * @ORM\Column(type="datetime")     
     */
	protected $creado;

	/**
     * @ORM\Column(type="datetime",nullable=true)
     */
	protected $modificado;


     /** 
     * @ORM\PrePersist
     */
     public function creado()
     {
          $this->creado=new \DateTime("now");
     }

     /** 
     * @ORM\PreUpdate
     */
     public function modificado()
     {
          $this->modificado=new \DateTime("now");
     }


}
