<?php

namespace NoticiasBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="noticias") 
 * @ORM\HasLifecycleCallbacks()
 */
class Noticia
{ 
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @ORM\Column(type="date")
    */
    private $fecha_publicacion;
    

    /**
    * @ORM\Column(type="boolean")
    */
    private $visible;

    /**
    * @ORM\Column(type="array")
    */
    private $idioma;

    /**
     * @ORM\Column(type="array")
     */
    private $titulo;

    /**
     * @ORM\Column(type="array")
     */
    private $entradilla;

    /**
     * @ORM\Column(type="array")
     */
    private $imagen;

    /**
     * @ORM\Column(type="array")
     */
    private $descargable;

    /**
     * @ORM\Column(type="array")
     */
    private $slug;

    /**
     * @ORM\Column(type="array")
     */
    private $cuerpo;

    /**
	* @ORM\Column(type="datetime")
    */
    private $created;

    /**
	* @ORM\Column(type="datetime",nullable=true)
    */
    private $modification;

    /**
     * Many Features have One Product.
     * @ORM\ManyToOne(targetEntity="\CategoriasBundle\Entity\Categoria", inversedBy="noticias")
     * @ORM\JoinColumn(name="categoria_id", referencedColumnName="id")
     */ 
    private $categoria;

    /**
    * @ORM\Column(type="boolean")
    */
    private $destacado;

    /**
    * @ORM\Column(type="smallint")
    */
    private $likes;

    /**
    * @ORM\Column(type="smallint")
    */
    private $hints;

     /**
     * One Product has Many Features.
     * @ORM\OneToMany(targetEntity="\ComentariosBundle\Entity\Comentario", mappedBy="noticia")
     */

    private $comentarios;

    public $imagen_aux;
    public $descargable_aux;

    public $rename;
    

    public function __construct()
    {   
        
        $this->comentarios = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fecha_publicacion= new \DateTime("now");
        $this->visible      = true;

    	$this->titulo 		= array("es"=>null,"en"=>null);
        $this->idioma       = array("es"=>true,"en"=>false);
        $this->entradilla   = array("es"=>null,"en"=>null);
        $this->imagen       = array("es"=>null,"en"=>null);
        $this->imagen_aux   = array("es"=>null,"en"=>null);
        $this->descargable_aux   = array("es"=>null,"en"=>null);
        $this->cuerpo         = array("es"=>null,"en"=>null);
    	$this->slug      	= array("es"=>null,"en"=>null);

        $this->likes=0;
        $this->hints=0;
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
    * @ORM\PreRemove
    **/
    public function borrar_media()
    {      
        
        foreach ($this->idioma as $lang => $value) 
        {
            
            if(isset($this->imagen[$lang]["valor"]))
                is_file(".".$this->imagen[$lang]["valor"])?unlink(".".$this->imagen[$lang]["valor"]):"";
            
            if(isset($this->descargable[$lang]["valor"]))
                is_file(".".$this->descargable[$lang]["valor"])?unlink(".".$this->descargable[$lang]["valor"]):"";
            
            if(isset($this->cuerpo[$lang]) && $this->cuerpo[$lang]!=null)
            {                
                foreach ($this->cuerpo[$lang] as $key => $item) 
                {
                    if($item["tipo"]=="imagen")
                        is_file(".".$item["valor"])?unlink(".".$item["valor"]):"";
                }
            }
            if($this->slug[$lang]!=null)
            {
                if(count(glob("./archivos/posts/".$this->slug[$lang]."/media/*"))==0)
                    is_dir("./archivos/posts/".$this->slug[$lang]."/media")?rmdir("./archivos/posts/".$this->slug[$lang]."/media"):"";
                if(count(glob("./archivos/posts/".$this->slug[$lang]."/*"))==0)
                    is_dir("./archivos/posts/".$this->slug[$lang])?rmdir("./archivos/posts/".$this->slug[$lang]):"";
                
            }
        }

    }
    /**
    * @ORM\PostUpdate
    **/
    public function limpiar_archivos()
    {   
        foreach ($this->idioma as $lang_code => $value) 
        {
            $borrar=array();
            $paths=array();

            if($this->imagen[$lang_code]!=null && isset($this->imagen[$lang_code]["valor"]))
                $paths[]=".".$this->imagen[$lang_code]["valor"];

            if($this->descargable[$lang_code]!=null && isset($this->descargable[$lang_code]))
                $paths[]=".".$this->descargable[$lang_code];

            if(isset($this->cuerpo[$lang_code]))
            {
                foreach ($this->cuerpo[$lang_code] as $index => $item) 
                {
                    if($item["tipo"]=="imagen")
                        $paths[]=".".$item["valor"];                    
                }
            }

            $total=glob("./archivos/posts/".$this->slug[$lang_code]."/media/*");

            $paths=array_flip($paths);            

            foreach ($total as $key => $value) 
            {
                if(!isset($paths[$value]))
                    $borrar[]=$value;
            }            

            foreach ($borrar as $key => $value) 
            {
                is_file($value)?unlink($value):"";
            }
        }
    }

    public function borrar($path)
    {
        if(is_file(".".$path))
        {
            unlink(".".$path);
        }
        return null;
    }

    public function subir($file,$carpeta)
    {
        if($file!=null)
        {
            $fecha=date("Y/m/d");

            

            $ruta="/archivos/posts/".$carpeta."/media/";


            $format=$file->getClientOriginalExtension();

            $name=str_replace(".".$format, "",$file->getClientOriginalName());

            $name=str_replace(" ","_", $name);          



            $files=glob(".".$ruta.$name."*.".$format);

            
            $total=count($files);
            if($total)
                $name=$name."_".$total.".".$format;
            else
                $name=$name.".".$format;

            $file->move(".".$ruta,$name);

            return $ruta.$name;
            
        }
       
    }

    /**
    * @ORM\PreUpdate
    */
    public function rename_media_folder()
    {   
        $is_dir=is_dir("./archivos/posts/".$this->rename["old"]);        
        
        if($is_dir)
        {
            
            $renamed=rename("./archivos/posts/".$this->rename["old"], "./archivos/posts/".$this->rename["new"]);
        
            if($renamed)
            {   
                if(isset($this->imagen[$this->rename["lang"]]))
                    $this->imagen[$this->rename["lang"]]=str_replace($this->rename["old"], $this->rename["new"], $this->imagen[$this->rename["lang"]]);
                if(isset($this->descargable[$this->rename["lang"]]))
                    $this->descargable[$this->rename["lang"]]=str_replace($this->rename["old"], $this->rename["new"], $this->descargable[$this->rename["lang"]]);


                if(isset($this->cuerpo[$this->rename["lang"]]))
                {
                    foreach ($this->cuerpo[$this->rename["lang"]] as $index => $item) 
                    {
                        if($item["tipo"]=="imagen")
                            $this->cuerpo[$this->rename["lang"]][$index]["valor"]=str_replace($this->rename["old"], $this->rename["new"], $this->cuerpo[$this->rename["lang"]][$index]["valor"]);                    
                    }
                }
            }
        }
        
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
     * Set fechaPublicacion
     *
     * @param \DateTime $fechaPublicacion
     *
     * @return Noticia
     */
    public function setFechaPublicacion($fechaPublicacion)
    {
        $this->fecha_publicacion = $fechaPublicacion;

        return $this;
    }

    /**
     * Get fechaPublicacion
     *
     * @return \DateTime
     */
    public function getFechaPublicacion()
    {
        return $this->fecha_publicacion;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     *
     * @return Noticia
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
     * Set idioma
     *
     * @param array $idioma
     *
     * @return Noticia
     */
    public function setIdioma($idioma)
    {
        $this->idioma = $idioma;

        return $this;
    }

    /**
     * Get idioma
     *
     * @return array
     */
    public function getIdioma()
    {
        return $this->idioma;
    }

    /**
     * Set titulo
     *
     * @param array $titulo
     *
     * @return Noticia
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return array
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set entradilla
     *
     * @param array $entradilla
     *
     * @return Noticia
     */
    public function setEntradilla($entradilla)
    {
        $this->entradilla = $entradilla;

        return $this;
    }

    /**
     * Get entradilla
     *
     * @return array
     */
    public function getEntradilla()
    {
        return $this->entradilla;
    }

    /**
     * Set imagen
     *
     * @param array $imagen
     *
     * @return Noticia
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * Get imagen
     *
     * @return array
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set descargable
     *
     * @param array $descargable
     *
     * @return Noticia
     */
    public function setDescargable($descargable)
    {
        $this->descargable = $descargable;

        return $this;
    }

    /**
     * Get descargable
     *
     * @return array
     */
    public function getDescargable()
    {
        return $this->descargable;
    }

    /**
     * Set slug
     *
     * @param array $slug
     *
     * @return Noticia
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
     * Set cuerpo
     *
     * @param array $cuerpo
     *
     * @return Noticia
     */
    public function setCuerpo($cuerpo)
    {
        $this->cuerpo = $cuerpo;

        return $this;
    }

    /**
     * Get cuerpo
     *
     * @return array
     */
    public function getCuerpo()
    {
        return $this->cuerpo;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Noticia
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
     * @return Noticia
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
     * Set destacado
     *
     * @param boolean $destacado
     *
     * @return Noticia
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
     * Set likes
     *
     * @param integer $likes
     *
     * @return Noticia
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;

        return $this;
    }

    /**
     * Get likes
     *
     * @return integer
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * Set hints
     *
     * @param integer $hints
     *
     * @return Noticia
     */
    public function setHints($hints)
    {
        $this->hints = $hints;

        return $this;
    }

    /**
     * Get hints
     *
     * @return integer
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * Set categoria
     *
     * @param \CategoriasBundle\Entity\Categoria $categoria
     *
     * @return Noticia
     */
    public function setCategoria(\CategoriasBundle\Entity\Categoria $categoria = null)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return \CategoriasBundle\Entity\Categoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Add comentario
     *
     * @param \ComentariosBundle\Entity\Comentario $comentario
     *
     * @return Noticia
     */
    public function addComentario(\ComentariosBundle\Entity\Comentario $comentario)
    {
        $this->comentarios[] = $comentario;

        return $this;
    }

    /**
     * Remove comentario
     *
     * @param \ComentariosBundle\Entity\Comentario $comentario
     */
    public function removeComentario(\ComentariosBundle\Entity\Comentario $comentario)
    {
        $this->comentarios->removeElement($comentario);
    }

    /**
     * Get comentarios
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComentarios()
    {
        return $this->comentarios;
    }
}
