<?php

namespace FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Controller\BaseController;

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
        $aux=$request->query->get("texto"); 

        $iv = hex2bin('000000000000000000000000000000');


        //$aux=openssl_encrypt($aux, "AES-256-CBC", $_SERVER["REMOTE_ADDR"]);

        echo "<pre>";
        var_dump($iv);
        var_dump($aux);

        //$aux=openssl_decrypt($aux, "AES-256-CBC", $_SERVER["REMOTE_ADDR"]);

        var_dump($aux);

        echo "</pre>";

        return new Response();

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
}



