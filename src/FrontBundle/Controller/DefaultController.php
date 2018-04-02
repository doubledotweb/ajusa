<?php

namespace FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Controller\BaseController;

Use ComentariosBundle\Entity\Comentario;

class DefaultController extends BaseController
{
    /**
     * @Route("/destacados")
     */
    public function destacados(Request $request)
    {


        $tipos=array(
            "actualidad"=>"Actualidad",
             "catalogo"=>"Catálogo" ,
            "ferias"=>"Ferias",
            "energias-alternativas"=>"Energías alternativas",
            "videos"=>"Vídeos",
            "informes-tecnicos"=>"Informes técnicos",
            "webinarios"=>"Webinarios",
            "otros"=>"Otros",
        );
        $lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }



        $params["destacados"]=array();
        $destacados=$this->findByField("DestcadosBundle:Destacado",array("visible"=>1,"idioma"=>$lang),array("creado"=>"desc"));
        
        for($i=0;$i<count($destacados) and $i<4;$i++)
        {
            $aux["titulo"]=$destacados[$i]->getTitulo();
            $aux["tipo"]=$tipos[$destacados[$i]->getTipo()];
            $aux["imagen"]="/bundles/destacados/img/".$destacados[$i]->getTipo()."_".$destacados[$i]->getImagen().".jpg";
            $aux["resumen"]=$destacados[$i]->getResumen();
            $aux["enlace"]=$destacados[$i]->getEnlace();

            $params["destacados"][]=$aux;
        }
        return new JsonResponse($params);
        

    }

    /**
     * @Route("/zona_tecnica")
     */
    public function zona_tecnica(Request $request)
    {
        $lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }

        $select_categoria='
            SELECT titulo'.($lang=="en"?"_en":"").'"titulo", slug 
            FROM categorias 
            ORDER BY titulo'.($lang=="en"?"_en":"").' DESC';

        $params["categorias"]=$this->query($select_categoria);

        $select_keywords='
            SELECT titulo, slug 
            FROM keywords 
            WHERE idioma = :lang
            ORDER BY titulo DESC';

        $conditions=array("lang"=>$lang);


        $params["keywords"]=$this->query($select_keywords,$conditions);

        $select_novedades='
            SELECT titulo,archivo 
            FROM tips 
            WHERE idioma = :lang
            ORDER BY creado DESC
            LIMIT 3';

        $params["novedades"]=$this->query($select_novedades,$conditions);


        return new JsonResponse($params);
    }  


    /**
     * @Route("/actualidad")
     */
    public function actualidad(Request $request)
    {       
        $lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }


        $noticias=$this->get_noticias($lang);
        $params["noticias"]=array();
        for($i=0;$i<count($noticias) and $i<6;$i++)
        {
            $aux=$this->get_comunes($noticias[$i],$lang);

            $params["noticias"][]=$aux;
        }
        return new JsonResponse($params);
    }

     /**
     * @Route("/pressroom")
     */
    public function pressroom(Request $request)
    {   
        $lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }


        $select_clipping="
            SELECT archivo
            FROM descargables
            WHERE idioma = :lang AND categoria='clipping-de-prensa' AND visible=1
            ORDER BY creado DESC
            LIMIT 1";

        $select_images="
            SELECT titulo,miniatura,archivo
            FROM descargables
            WHERE idioma= :lang AND categoria=:tipo AND visible=1
            ORDER BY creado DESC";

        $conditions["lang"]=$lang;

        $params["clipping"]=$this->query($select_clipping,$conditions);

        $conditions["tipo"]="imagen";

        $params["imagenes"]=$this->query($select_images,$conditions);

        $conditions["tipo"]="logotipo";

        $params["logotipos"]=$this->query($select_images,$conditions);

        return new JsonResponse($params);


    }



    /**
     * @Route("/blog")
     */
    public function blog(Request $request)
    {
    	$lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }


    	$response=array();

        $response["noticias"]=array();
        

    	$result=$this->get_noticias($lang);

    	foreach ($result as $key => $noticia) 
    	{
    		$aux=$this->get_comunes($noticia,$lang);

			$aux["categoria"]=$this->get_categoria_noticia($noticia,$lang);
    		

    		$response["noticias"][]=$aux;
    	}

        $response["categorias"]=$this->get_categorias($lang);
        return new JsonResponse($response);
    }

    /**
     * @Route("/blog/{slug_categoria}")
     */
    public function blog_categoria(Request $request,$slug_categoria)
    {
        $lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }


        $response=array();

        $response["noticias"]=array();
            
        $select_categoria="
            SELECT c
            FROM CategoriasBundle:Categoria c
            Where c.slug LIKE :slug";

        $conditions=array("slug"=>"%".$slug_categoria."%");

        $result=$this->query_builder($select_categoria,$conditions);

        $categoria=null;

        foreach ($result as $key => $cat) 
        {            
            if($cat->getSlug()[$lang]==$slug_categoria)
            {                
                $categoria=$cat;
                break;
            }
        }

        if($categoria)
        {            
            foreach ($categoria->getNoticias() as $key => $noticia) 
            {
                $response["noticias"][]=$this->get_comunes($noticia,$lang);
            }
            return new JsonResponse($response);    
        }
        else
        {
            return new JsonResponse(array("mensaje"=>"no existe esta categoria","code"=>0));
        }        
    }




    /**
     * @Route("/post/{slug}")
     */
    public function noticia(Request $request,$slug)
    {   	

    	$lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }

    	$response=array();
    	$response["status_code"]=200;
    	$response["noticia"]=array();
    	

    	$query='SELECT n
    		FROM NoticiasBundle:Noticia n
    		WHERE 	n.slug LIKE :slug and     		
    				n.visible = 1
    		ORDER BY n.fecha_publicacion DESC';

    	$conditions=array(

    		"slug"=>'%'.$slug.'%',    		
    	);

    	
    	$noticia=$this->query_builder($query,$conditions);

    	if(count($noticia))
    	{
    		$aux=$this->get_comunes($noticia[0],$lang);
			
			$aux["descargable"]["enlace"]=isset($noticia[0]->getDescargable()[$lang]["valor"])?$noticia[0]->getDescargable()[$lang]["valor"]:"";
			$aux["descargable"]["pie"]=isset($noticia[0]->getDescargable()[$lang]["pie"])?$noticia[0]->getDescargable()[$lang]["pie"]:"";
			$aux["categoria"]=$this->get_categoria_noticia($noticia[0],$lang);
    		$aux["modulos"]=$this->get_modulos($noticia[0],$lang);
            $aux["comentarios"]=$this->get_comentarios($noticia[0],$lang);

    		$response["noticia"]=$aux;
    	}
    	else
    	{
    		$response["code"]=0;
    		$response["mensaje"]="Esta noticia no existe";
    	}
        return new JsonResponse($response);
    }

    /**
     * @Route("/comentar")
     */
    public function comentario(Request $request)
    {



        $user=$request->server->get("PHP_AUTH_USER");
        $pass=$request->server->get("PHP_AUTH_PW");



        if($user=="mario" and $pass=="1234")
        {
            $data=$request->request->get("datos");                        

            $json=base64_decode($data);
            $comentario=json_decode($json,1);

            if($this->validar_comentario($comentario))
            {

                $noticia=null;

                $select_noticia="SELECT n FROM NoticiasBundle:Noticia n where n.slug LIKE :slug";

                $conditions=array("slug"=>'%'.$comentario["slug"].'%',);

                
                $noticias=$this->query_builder($select_noticia,$conditions);
                $new_comentario=new Comentario();
                foreach ($noticias as $key => $aux) 
                {
                    if($aux->getSlug()["es"]==$comentario["slug"] )
                    {
                        $noticia=$aux;
                        $new_comentario->setIdioma("es");
                        break;
                    }
                    elseif ($aux->getSlug()["en"]==$comentario["slug"]) 
                    {
                        $noticia=$aux;
                        $new_comentario->setIdioma("en");
                        break;
                    }
                }

                if($noticia)
                {
                    

                    $new_comentario->setNoticia($noticia);
                    $new_comentario->setNombre($comentario["nombre"]);
                    $new_comentario->setTexto($comentario["texto"]);
                    
                    try
                    {
                        $this->insertar_entity($new_comentario);
                    }
                    catch(\Exception $e)
                    {                        
                        return new JsonResponse(array("mensaje"=>"No se ha podido procesar su comentario","code"=>0));
                    }

                    return new JsonResponse(array("mensaje"=>"Almacenado correctamente","code"=>1));

                }
                else
                {
                    return new JsonResponse(array("mensaje"=>"No existe la noticia","code"=>-1));
                }

            }
            else
            {
                return new JsonResponse(array("mensaje"=>"Formato de datos inválido","code"=>-2));
            }
        }
        else
        {
            return new JsonResponse(array("mensaje"=>"I'm sorry but we don't like script guys ;)","code"=>-3));
        }        

    }


    private function get_noticias($lang)
    {
        $query='SELECT n
            FROM NoticiasBundle:Noticia n
            WHERE   n.idioma LIKE :idioma and 
                    n.fecha_publicacion <= :now and
                    n.visible = 1
            ORDER BY n.fecha_publicacion DESC';

        $conditions=array(

            "idioma"=>'%"'.$lang.'";b:1;%',
            "now"=>(new \DateTime("now"))->format("Y-m-d"),
        );

        return $this->query_builder($query,$conditions);
    }
    private function get_comunes($noticia,$lang)
    {
    	$aux["titulo"]=$noticia->getTitulo()[$lang];
		$aux["img"]["enlace"]=isset($noticia->getImagen()[$lang]["valor"])?$noticia->getImagen()[$lang]["valor"]:"";
		$aux["img"]["pie"]=isset($noticia->getImagen()[$lang]["pie"])?$noticia->getImagen()[$lang]["pie"]:"";
		$aux["slugs"]=$noticia->getSlug();
		$aux["entradilla"]=$noticia->getEntradilla()[$lang];
        $aux["likes"]=$noticia->getLikes();
        $aux["hints"]=$noticia->getHints();
        $aux["fecha"]=$noticia->getCreado()->format("Y-m-d");

		return $aux;
    }

    private function get_categorias($lang)
    {
        $select_categorias="SELECT c FROM CategoriasBundle:Categoria c";

        $result=$this->query_builder($select_categorias);

        $categorias=array();


        foreach ($result as $key => $categoria) 
        {
            $aux["nombre"]=$categoria->getNombre()[$lang];
            $aux["slug"]=$categoria->getSlug()[$lang];

            $categorias[]=$aux;
        }

        return $categorias;
    }

    private function get_categoria_noticia($noticia,$lang)
    {
    	$aux=array();
		if($noticia->getCategoria()!=null)
        {            
          $aux["nombre"]=$noticia->getCategoria()->getNombre()[$lang];
          $aux["slug"]=$noticia->getCategoria()->getSlug()[$lang];
        }
		

		return $aux;
    }

    private function get_modulos($noticia,$lang)
    {
    	$modulos=array();

    	if($noticia->getCuerpo()[$lang])
    	{    		
	    	foreach ($noticia->getCuerpo()[$lang] as $key => $modulo) 
	    	{
	    		$aux=array();
	    		switch ($modulo["tipo"]) 
	    		{
	    			case 'texto':
	    			case "destacado":
	    				$aux["tipo"]=$modulo["tipo"];
	    				$aux["texto"]=$modulo["valor"];
    				break;

    				case 'imagen':
    				case "video":
	    				$aux["tipo"]=$modulo["tipo"];
	    				$aux["enlace"]=$modulo["valor"];
	    				$aux["pie"]=$modulo["pie"];
    				break;

    				case "despiece":
    					$aux["tipo"]=$modulo["tipo"];
    					$aux["titulo"]=$modulo["titulo"];
    					foreach ($modulo["enlace"] as $key => $enlace) 
    					{
    						$aux2["texto"]=$enlace["texto"];
    						$aux2["url"]=$enlace["enlace"];
    						$aux["enlaces"][]=$aux2;
    					}
    				break;
	    			
	    			default:
	    				
    				break;
	    		}

	    		$modulos[]=$aux;
	    	}
    	}
    	return $modulos;
    }

    private function validar_comentario($comentario)
    {
        if(!isset($comentario["nombre"]) or $comentario["nombre"]=="")
            return false;
        if(!isset($comentario["texto"]) or $comentario["texto"]=="")
            return false;
        if(!isset($comentario["slug"]) or $comentario["slug"]=="")
            return false;

        return true;
    }

    private function get_comentarios($noticia,$lang)
    {
        $comentarios=array();

        foreach ($noticia->getComentarios()->getValues() as $key => $comentario) 
        {   
            if($comentario->getIdioma()==$lang)
            {
                $aux["nombre"]=$comentario->getNombre();
                $aux["fecha"]=$comentario->getCreated()->format("d/m/Y H:i");
                $aux["texto"]=$comentario->getTexto();
                $comentarios[]=$aux;
            }

        }
        return $comentarios;
    }
}



