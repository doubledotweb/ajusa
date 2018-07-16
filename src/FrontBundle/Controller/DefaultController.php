<?php

namespace FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Controller\BaseController;

use ComentariosBundle\Entity\Comentario;

use Abraham\TwitterOAuth\TwitterOAuth;

use Psr\Log\LoggerInterface;

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
            $aux["imagen"]="/bundles/destcados/img/".$destacados[$i]->getTipo()."_".$destacados[$i]->getImagen().".jpg";
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
     * @Route("/tips")
     */
    public function tips(Request $request)
    {
        $lang=$request->request->get("lang");

        if($lang=="")
        {
            $lang=$request->query->get("lang");

            if($lang=="")
                $lang="es";
        }

        $conditions=array("lang"=>$lang);

        $select_tips='
            SELECT a.titulo as tip_titulo, a.id as tip_id, a.archivo as archivo, b.titulo as categoria
            FROM tips as a, categorias as b, tips_categorias as d
            WHERE b.id = d.categoria_id AND d.tip_id = a.id AND a.idioma = :lang
            ORDER BY a.creado DESC';

        $tips = $this->query($select_tips,$conditions);
        
        foreach ($tips as &$tip)
        {
            $conditions=array("id"=>$tip["tip_id"]);
            $select_keywords = 'SELECT titulo FROM keywords as a, tips_keywords as b WHERE a.id = b.keyword_id AND b.tip_id = :id';
            $keywords = $this->query($select_keywords,$conditions);
            $keywords_json = [];
            foreach ($keywords as $key)
            {
                $keywords_json[] = $key["titulo"];
            }
            $tip['keywords'] = $keywords_json;
        }


        return new JsonResponse($tips);
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
     * @Route("/like/{slug}")
     */
    public function addlike(Request $request,$slug)
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
        $noticia[0]->setLikes($noticia[0]->getLikes()+1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($noticia[0]);
        $em->flush();        
    
        return new JsonResponse(array("likes"=>$noticia[0]->getLikes(),"code"=>200));
    }

    /**
     * @Route("/hint/{slug}")
     */
    public function addhint(Request $request,$slug)
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
        $noticia[0]->setHints($noticia[0]->getHints()+1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($noticia[0]);
        $em->flush();        
    
        return new JsonResponse(array("hints"=>$noticia[0]->getHints(),"code"=>200));
    }

    /**
     * @Route("/contacto")
     * @Method("POST")
     */

    public function contacto(Request $request) {

        $datos = $request->request->all();        
        $lang = $request->request->get("lang");
        $asunto = $request->get("asunto");
        $consulta = $request->get("consulta");
        $email = $request->get("email");
        $firstpolitica = $request->get("firstpolitica");
        $nombre = $request->get("nombre");
        $telefono = $request->get("telefono");
        $tipo_consulta = $request->get("tipo_consulta");

        $subject    = $asunto;
        $message = \Swift_Message::newInstance();
        
            
       /*  $message->setSubject($subject)
        ->setFrom('web@corporacionhms.com')
        ->setTo("millan.hermana@doubledot.es")
        ->setBody(                  
            $this->renderView(
                'base.html.twig'
                ,
                array("email"=>$datos) 
                
            ),
            'text/html'
        ); */
        try
    	{
    	
            $sendmail=$this->container->get("app.sendmail");

            $params["subject"]   = "[Ajusa]: Se ha solicitado restablecido la contraseña";
            $params["to"]     = "millan.hermana@doubledot.es";
            $params["from"]     = "web@corporacionhms.com";
            $params["template"] = "'base.html.twig";
            //$params["perfil"]   = "http://".$_SERVER["HTTP_HOST"]."/perfil";
            
            
            return $sendmail->send($params);
    	}
    	catch(\Exception $e)
    	{    		
    		
            $this->addFlash("error",$this->mensaje_error($e));
    		return $this->json(array("mensaje"=>$e->getMessage()));
        }
        

        /* if (!$this->get('mailer')->send($message,$failures)) {
            
            return $failures;
        }; */
        return new JsonResponse(array('asunto' => $asunto, 'consulta' => $consulta, 'email' => $email, 'code' => 200));
    }

    /**
     * @Route("/addnewsletter")
     * @Method("POST")
     */

    public function addnewsletter(Request $request) {

        $datos = $request->request->all();
        /*
        Id Listas
        ES -> Ajusa Web ES -> id: 1537892
        EN -> Ajusa Web EN -> id: 1537891
        */

        $logger = $this->get("logger");
        $logger->info(print_r($request, true));
        $lang = $request->request->get("lang");
        $asunto = $request->get("asunto");
        $consulta = $request->get("consulta");
        $email = $request->get("email");
        $firstpolitica = $request->get("firstpolitica");
        $nombre = $request->get("nombre");
        $telefono = $request->get("telefono");
        $tipo_consulta = $request->get("tipo_consulta");

        $subject    = $datos["asunto"]; 
        $message = \Swift_Message::newInstance();
        
            
        $message->setSubject($subject)
        ->setFrom('millanhermana@gmail.com')
        ->setTo("millan.hermana@doubledot.es")
        ->setBody(                  
            $this->renderView(
                'base.html.twig'
                ,
                array("email"=>$datos) 
                
            ),
            'text/html'
        );
        $response = $this->get('mailer')->send($message);
        
        return $response;
        //return new JsonResponse(array('asunto' => $asunto, 'consulta' => $consulta, 'email' => $email, 'code' => $response));
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
                    $new_comentario->setEmail($comentario["email"]);
                    
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
        $aux["destacado"]=$noticia->getDestacado();
        $aux["comentarios"]=count($noticia->getComentarios());        
        $aux["hints"]=$noticia->getHints();
        $aux["fecha"]=$noticia->getCreated()->format("Y-m-d");
        

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
        if(!isset($comentario["email"]) or $comentario["email"]=="")
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
                $aux["fecha"]=$comentario->getCreado()->format("U");
                $aux["texto"]=$comentario->getTexto();
                $comentarios[]=$aux;
            }

        }
        return $comentarios;
    }

    /**
     * @Route("/tweets")
     */
    public function tweets(Request $request)
    {

        $connection = new TwitterOAuth('jK2s2Kow0oNCZ0CAXYf2IyvXK', 'EuGvFjqf2Kb0ThqCijNB93Nf29iAoJfIqEJIds6O0FyHQ6acen', '1010836765803012099-CrKfWRDGubqBF1eu06gVwmwpkmISbY', '8ZXuKi1bxKZ0iZTojhzHV2f5dkS9ZAszvBhYNHj0Vd4Z5');
        $content = $connection->get("account/verify_credentials");
        
        $tweets_result = $connection->get("search/tweets", ["q" => "@Ajusa_Spain", "count" => 3, "exclude_replies" => true]);
        return new JsonResponse(array("tweets"=>$tweets_result,"code"=>200));
            
       /*  $method = 'GET';
        
        // Twitter still uses Oauth1 (which is a pain)
        $oauth = array(
            'oauth_consumer_key'=>'jK2s2Kow0oNCZ0CAXYf2IyvXK',
            //'oauth_nonce'=>random(32),
            'oauth_signature_method'=>'HMAC-SHA1',
            'oauth_timestamp'=>time(),
            'oauth_token'=>'1010836765803012099-CrKfWRDGubqBF1eu06gVwmwpkmISbY',
            'oauth_version'=>'1.0',
        );                    
        
        $url = "https://api.twitter.com/1.1/search/tweets.json?q=@Ajusa_Spain";
        
        $oauth['oauth_signature'] = $this->generateSignature($oauth,$url,$method,'');                                
        
        ksort($oauth);
        
        foreach ($oauth as $k=>$v){
            $auths[] = $k.'="'.$v.'"';
        }
        
        $stream = array('http' =>
            array(
                'method' => $method,
                // ignore_errors should be true
                'ignore_errors'=>true, // http://php.net/manual/en/context.http.php - otherwise browser returns error not error content
                'follow_location'=>false,
                'max_redirects'=>0,
                'header'=> array(
                'Authorization: OAuth '.implode(', ',$auths),
                'Connection: close'
                )                                             
            )
        );                                                                                                                 
        
        echo $url;                                                 
        $response = file_get_contents($url, false, stream_context_create($stream)); 

        return new JsonResponse(array("mensaje"=>$response ,"code"=>200));*/
    }

    private function generateSignature($oauth,$fullurl,$http_method){        

        // Take params from url
        $main_url = explode('?',$fullurl);        
        
        $urls = explode('&',$main_url[1]);
        
        foreach ($urls as $param){
            $bits = explode('=',$param);
            if (strlen($bits[0])){
                $oauth[$bits[0]] = rawurlencode($bits[1]);
            }    
        }
    }
}
