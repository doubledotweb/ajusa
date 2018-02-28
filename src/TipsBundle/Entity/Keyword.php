<?php

namespace TipsBundle\Entity;



use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="keywords")
 * @ORM\HasLifecycleCallbacks()
 */
class Keyword extends Entity
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
     * @ORM\Column(type="string")
     */
    private $slug;

	/**
     * Many Groups have Many Users.
     * @ORM\ManyToMany(targetEntity="\TipsBundle\Entity\Tip", mappedBy="keywords")
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
     * Set idioma
     *
     * @param string $idioma
     *
     * @return Keyword
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
     * @return Keyword
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
     * @return Keyword
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
     * @return Keyword
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
     * @return Keyword
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
     * @return Keyword
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
}
