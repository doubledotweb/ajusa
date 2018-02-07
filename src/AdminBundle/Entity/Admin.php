<?php 
namespace AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use AppBundle\Entity\Usuario;
/**
 * @ORM\Entity
 * @ORM\Table(name="administradores")
 */
class Admin extends Usuario
{
    /**
     * @ORM\Column(type="string")
     */
    private $nombre;
    /**
     * @ORM\Column(type="string")
     */
    private $apellido;
    


    public function __construct()
    {
        parent::__construct();
        $this->role="Administrador";
        
    }


    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Admin
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
     * Set apellido
     *
     * @param string $apellido
     *
     * @return Admin
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;

        return $this;
    }

    /**
     * Get apellido
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    
}
