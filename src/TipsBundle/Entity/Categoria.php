<?php

namespace TipsBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="categorias")
 * @ORM\HasLifecycleCallbacks()
 */
class Categoria extends Entity
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
    private $titulo;

    /**
     * @ORM\Column(type="string")
     */
    private $titulo_en;

	/**
     * @ORM\Column(type="string")
 	 */
	private $slug;	

    /**
     * Many Groups have Many Users.
     * @ORM\ManyToMany(targetEntity="\TipsBundle\Entity\Tip", mappedBy="categorias")
     */
    private $tips;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tips = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Categoria
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
     * Set slug
     *
     * @param string $slug
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
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
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
     * Add tip
     *
     * @param \TipsBundle\Entity\Tip $tip
     *
     * @return Categoria
     */
    public function addTip(\TipsBundle\Entity\Tip $tip)
    {
        $this->tips[] = $tip;

        return $this;
    }

    /**
     * Remove tip
     *
     * @param \TipsBundle\Entity\Tip $tip
     */
    public function removeTip(\TipsBundle\Entity\Tip $tip)
    {
        $this->tips->removeElement($tip);
    }

    /**
     * Get tips
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTips()
    {
        return $this->tips;
    }

    /**
     * Set tituloEn
     *
     * @param string $tituloEn
     *
     * @return Categoria
     */
    public function setTituloEn($tituloEn)
    {
        $this->titulo_en = $tituloEn;

        return $this;
    }

    /**
     * Get tituloEn
     *
     * @return string
     */
    public function getTituloEn()
    {
        return $this->titulo_en;
    }
}
