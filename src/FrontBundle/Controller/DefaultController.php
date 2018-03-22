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
     * @Route("/noticias")
     */
    public function listado(Request $request)
    {
    	$lang=$request->request->get("lang");

    	$lang=$lang==""?"es":$lang;

    	$response=array();

    	$query='SELECT n
    		FROM NoticiasBundle:Noticia n
    		WHERE 	n.idioma LIKE :idioma and 
    				n.fecha_publicacion <= :now and
    				n.visible = 1
    		ORDER BY n.fecha_publicacion DESC';

    	$conditions=array(

    		"idioma"=>'%"'.$lang.'";b:1;%',
    		"now"=>(new \DateTime("now"))->format("Y-m-d"),
    	);

    	$response["listado"]=array();
    	$result=$this->query_builder($query,$conditions);

    	foreach ($result as $key => $noticia) 
    	{
    		$aux=$this->get_comunes($noticia,$lang);

			$aux["categorias"]=$this->get_categorias($noticia,$lang);
    		

    		$response["listado"][]=$aux;
    	}
        return new JsonResponse($response);
    }    

    /**
     * @Route("/noticias/{slug}")
     */
    public function noticia(Request $request,$slug)
    {   	

    	$lang=$request->request->get("lang");

    	$lang=$lang==""?"es":"en";

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
			
			$aux["descargable"]["enlace"]=$noticia[0]->getDescargable()[$lang]["valor"];
			$aux["descargable"]["pie"]=$noticia[0]->getDescargable()[$lang]["pie"];
			$aux["categorias"]=$this->get_categorias($noticia[0],$lang);
			$aux["likes"]=$noticia[0]->getLikes();
    		$aux["modulos"]=$this->get_modulos($noticia[0],$lang);
            $aux["comentarios"]=$this->get_comentarios($noticia[0],$lang);

    		$response["noticia"]=$aux;
    	}
    	else
    	{
    		$response["status_code"]=404;
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
                        return new JsonResponse(array("mensaje"=>"No se ha podido procesar su comentario"));
                    }

                    return new JsonResponse(array("mensaje"=>"Almacenado correctamente"));

                }
                else
                {
                    return new JsonResponse(array("mensaje"=>"No existe la noticia"));
                }

            }
            else
            {
                return new JsonResponse(array("mensaje"=>"Formato de datos inválido"));       
            }
        }
        else
        {
            return new JsonResponse(array("mensaje"=>"I'm sorry but we don't like script guys ;)"));
        }        

    }

    private function get_comunes($noticia,$lang)
    {
    	$aux["titulo"]=$noticia->getTitulo()[$lang];
		$aux["img"]["enlace"]=$noticia->getImagen()[$lang]["valor"];
		$aux["img"]["pie"]=$noticia->getImagen()[$lang]["pie"];
		$aux["slug"]=$noticia->getSlug()[$lang];
		$aux["entradilla"]=$noticia->getEntradilla()[$lang];

		return $aux;
    }

    private function get_categorias($noticia,$lang)
    {
    	$aux=array();
		foreach ($noticia->getCategorias()->getValues() as $key => $categoria) 
		{
			$aux[]=$categoria->getNombre()[$lang];
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
                $aux["fecha"]=$comentario->getCreado()->format("d/m/Y H:i");
                $aux["texto"]=$comentario->getTexto();
                $comentarios[]=$aux;
            }

        }
        return $comentarios;
    }
}



