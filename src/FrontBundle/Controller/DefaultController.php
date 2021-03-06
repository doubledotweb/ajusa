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
use Mailjet\MailjetBundle\Resources;
use Mailjet\MailjetBundle\Event\ContactEvent;
use Mailjet\MailjetBundle\Model\Contact;

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
        $tipos_en=array(
            "actualidad"=>"Breaking news",
            "catalogo"=>"Catalogue" ,
            "ferias"=>"Fairs",
            "energias-alternativas"=>"Alternative energies",
            "videos"=>"Videos",
            "informes-tecnicos"=>"Technical area",
            "webinarios"=>"Webinars",
            "otros"=>"Others",
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
            if ($lang == "en") {
                $aux["tipo"]=$tipos_en[$destacados[$i]->getTipo()];    
            } else {
                $aux["tipo"]=$tipos[$destacados[$i]->getTipo()];
            }
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
        $lang=$request->query->get("lang");

        if($lang=="")
            $lang="es";
        

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
            WHERE idioma = :lang AND visible = 1
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
        if ($lang == "en") {
            $select_tips='
            SELECT a.titulo as tip_titulo, a.id as tip_id, a.archivo as archivo, b.titulo_en as categoria
            FROM tips as a, categorias as b, tips_categorias as d
            WHERE b.id = d.categoria_id AND d.tip_id = a.id AND a.idioma = :lang AND a.visible = 1
            ORDER BY a.creado DESC';
        } else {
            $select_tips='
            SELECT a.titulo as tip_titulo, a.id as tip_id, a.archivo as archivo, b.titulo as categoria
            FROM tips as a, categorias as b, tips_categorias as d
            WHERE b.id = d.categoria_id AND d.tip_id = a.id AND a.idioma = :lang AND a.visible = 1
            ORDER BY a.creado DESC';
        }
        

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

        $lang=$request->query->get("lang");

        if($lang=="")
            $lang="es";     

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
        //$logger=$this->container->get("logger");
        //$logger->info(var_dump($lang));   
        
        if ($slug == $noticia[0]->getSlug()["es"])
        {
            $lang = "es";
        } else {
            $lang = "en";
        }
        
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
     * @Method({"POST", "OPTIONS"})
     */

    public function contacto(Request $request) {

      $ruta = ""; $ficha_producto = "";
      $mensaje = "";
      $news_aldonza = $request->get("newsletter_aldonza");
      if (!empty($news_aldonza))
      {
        return $this->addUserAldonza($request);
      }


      if (!empty($request->request->get("lang"))) {
          $lang = $request->request->get("lang");
          $mensaje .= "lang: " . $request->request->get("lang") ;
      } else {
          $lang = "";
      }
     /*  if (!empty($request->get("asunto"))) {
          $asunto = $request->get("asunto");
          $mensaje .= "asunto: " . $request->get("asunto") . "<br/>";
      } else {
          $asunto = "";
      } */
      if (!empty($request->get("tipo_contacto"))) {
          $tipo_contacto = $request->get("tipo_contacto");
          $mensaje .= "<p>tipo_contacto: " . $request->get("tipo_contacto") . "</p>";
      } else {
          $tipo_contacto = "";
      }      
      if (!empty($request->get("nombre"))) {
          $nombre = $request->get("nombre");
          $mensaje .= "<p>nombre_apellidos: " . $request->get("nombre") . "</p>";
      } else {
          $nombre = "";
      }
      
      if (!empty($request->get("email"))) {
          $email = $request->get("email");
          $mensaje .= "<p>email: " . $request->get("email") . "</p>";
      } else {
          $email = "";
      }
      if (!empty($request->get("empresa"))) {
          $empresa = $request->get("empresa");
          $mensaje .= "<p>empresa: " . $request->get("empresa") . "</p>";
      } else {
          $empresa = "";
      }
      if (!empty($request->get("sector"))) {
          $sector = $request->get("sector");
          $mensaje .= "<p>sector: " . $request->get("sector") . "</p>";
      } else {
          $sector = "";
      }
      if (!empty($request->get("direccion"))) {
        $direccion = $request->get("direccion");
        $mensaje .= "<p>direccion: " . $request->get("direccion") . "</p>";
      } else {
        $direccion = "";
      }
      if (!empty($request->get("pais"))) {
        $pais = $request->get("pais");
        $mensaje .= "<p>pais: " . $request->get("pais") . "</p>";
      } else {
        $pais = "";
      }
      if (!empty($request->get("ciudad"))) {
        $ciudad = $request->get("ciudad");
        $mensaje .= "<p>ciudad: " . $request->get("ciudad") . "</p>";
      } else {
        $ciudad = "";
      }
      if (!empty($request->get("telefono"))) {
        $telefono = $request->get("telefono");
        $mensaje .= "<p>telefono: " . $request->get("telefono") . "</p>";
      } else {
        $telefono = "";
      }
      if (!empty($request->get("tipo_catalogo"))) {
          $tipo_catalogo = $request->get("tipo_catalogo");
          $mensaje .= "<p>tipo_catalogo: " . $request->get("tipo_catalogo") . "</p>";
      } else {
          $tipo_catalogo = "";
      }
      if (!empty($request->get("referencia_producto"))) {
          $referencia_producto = $request->get("referencia_producto");
          $mensaje .= "<p>referencia_producto: " . $request->get("referencia_producto") . "</p>";
      } else {
          $referencia_producto = "";
      }
      if (!empty($request->get("mensaje"))) {
          $consulta = $request->get("mensaje");
          $mensaje .= "<p>consulta: " . $request->get("mensaje") . "</p>";
      } else {
          $consulta = "";
      }
      if (!empty($request->get("politica"))) {
          $firstpolitica = $request->get("politica");
          $mensaje .= "<p>politica: " . $request->get("politica") . "</p>";
      } else {
          $firstpolitica = "";
      }
      if (!empty($request->get("tipo_consulta"))) {
          $tipo_consulta = $request->get("tipo_consulta");
          $mensaje .= "<p>tipo_consulta: " . $request->get("tipo_consulta") . "</p>";
      } else {
          $tipo_consulta = "";
      }
      if (!empty($request->get("newsletter") && $request->get("newsletter") == "on")) {
        if (in_array($tipo_contacto, ["aldonza","aldonza-experiencia"])) {
          $this->createContactAldonza($email, $nombre, $lang);
        } else {
          $this->createContact($email, $nombre, $lang);
        }
      }
      
      $emailgracias = $email;
      $to = "social@ajusa.es";
      switch ($tipo_contacto) {
              case "general":
                  $to = "social@ajusa.es";
                  $subject = "contacto general"; 
                  break;
              case "contacto":
                  $to = "social@ajusa.es";
                  $subject = "contacto general"; 
                  break;
              case "sucursales":
                  $to = "social@ajusa.es";
                  $subject = "contacto sucursal"; 
                  break;
              case "comercial":
                  $to = "social@ajusa.es";
                  $subject = "contacto comercial"; 
                  break;
              case "formacion":
                  $to = ["customerservice@ajusa.es","customerservice2@ajusa.es"];
                  $subject = "contacto formacion"; 
                  break;
              case "asistencia_tecnica":
                  $to = ["customerservice@ajusa.es","customerservice2@ajusa.es"];
                  $subject = "contacto Asistencia tecnia"; 
                  break;
              case "catalogo":
                  $to = "agarciag@corporacionhms.com";
                  $subject = "contacto catalogo"; 
                  break;
              case "trabaja":
                  $to = "jcifuentes@corporacionhms.com";
                  $subject = "contacto trabaja"; 
                  break;
              case "aldonza":
                  $to = ["comunicacion@aldonzagourmet.com", "angela.rojas@doubledot.es"];
                  $subject = "aldonza"; 
                  break;
              case "aldonza-experiencia":
                  $to = ["info@aldonzagourmet.com", "angela.rojas@doubledot.es"];
                  $subject = "aldonza experiencia"; 
                  break;
              default:
                  $to = "social@ajusa.es";
                  $subject = "contacto general"; 
                  break;
      }
     
      $message = \Swift_Message::newInstance();
      if ($subject == "aldonza" || $subject == "aldonza experiencia") {
        $message->setSubject( "[Aldonza]: ".$subject)
        ->setFrom("mailer@ajusa.es")
        //->setFrom("ajusa@doubledot.es")      
        ->setTo($to)
        ->setContentType("text/html")
        ->setBody(                  
            $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                "emails/base.html.twig",
                array(
                    "title" => $to,
                    "logo" => "",
                    "mensaje"=> $mensaje,
                    "politica" => "on")
            ),
            'text/html'
        );

      } else {
        $message->setSubject( "[Ajusa]: ".$subject)
        ->setFrom("mailer@ajusa.es")
        //->setFrom("ajusa@doubledot.es")      
        ->setTo($to)
        ->setContentType("text/html")
        ->setBody(                  
            $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                "emails/base.html.twig",
                array(
                    "title" => $to,
                    "logo" => "",
                    "mensaje"=> $mensaje,
                    "politica" => "on")
            ),
            'text/html'
        );
      }
      
      if ( $request->files->get('doc_adjunto') != null) {
        $ficha_producto = str_replace(' ', '%20', $request->files->get('doc_adjunto')->getClientOriginalName());
        $publicResourcesFolderPath = '/var/www/gestor_ajusa/web/bundles/front/attach/';
        $request->files->get('doc_adjunto')->move($publicResourcesFolderPath, $ficha_producto);
        $message->attach(\Swift_Attachment::fromPath($publicResourcesFolderPath.'/'.$ficha_producto));
      }
      
      $this->get('mailer')->send($message);
      
      //if ($request->files->get("doc_adjunto") != "") {
      
        
        
    /* $message = \Swift_Message::newInstance();

    $mensajegracias = "Muchas gracias por ponerte en contacto con nosotros, te responderemos lo antes posible.";
    if (!empty($emailgracias)) {
        $message->setSubject( "[Ajusa]: ".$subject)
        ->setFrom("mailer@ajusa.es")
        ->setTo( $email)
        ->setContentType("text/html")
        ->setBody(                  
            $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                "emails/base.html.twig",
                array(
                    "title" => $subject,
                    "logo" => "",
                    "mensaje"=> $mensajegracias,
                    "politica" => "")
            ),
            'text/html'
        );
        $this->get('mailer')->send($message);
    } */
          
    //return new JsonResponse(['1']);
    return new JsonResponse(['1']);

        
            
/*        
 */
    }

    /**
     * @Route("/contactoth")
     * @Method("POST")
     */

    public function contactoth(Request $request) {

        $ruta = ""; $ficha_producto = "";
        $mensaje = "";
  
       
  
  
        if (!empty($request->request->get("lang"))) {
            $lang = $request->request->get("lang");
            $mensaje .= "lang: " . $request->request->get("lang") . "<br/>";
        } else {
            $lang = "";
        }
        if (!empty($request->get("asunto"))) {
            $asunto = $request->get("asunto");
            $mensaje .= "Asunto: " . $request->get("asunto") . "<br/>";
        } else {
            $asunto = "";
        }
        if (!empty($request->get("tipo"))) {
            $tipo_contacto = $request->get("tipo");
            $mensaje .= "Tipo de contacto: " . $request->get("tipo") . "<br/>";
        } else {
            $tipo_contacto = "";
        }      
        if (!empty($request->get("nombre"))) {
            $nombre = $request->get("nombre");
            $mensaje .= "Nombre: " . $request->get("nombre") . "<br/>";
        } else {
            $nombre = "";
        }
        if (!empty($request->get("apellidos"))) {
            $apellidos = $request->get("apellidos");
            $mensaje .= "Apellidos: " . $request->get("apellidos") . "<br/>";
        } else {
            $apellidos = "";
        }
        if (!empty($request->get("email"))) {
            $email = $request->get("email");
            $mensaje .= "Email: " . $request->get("email") . "<br/>";
        } else {
            $email = "";
        }
        if (!empty($request->get("telefono"))) {
            $telefono = $request->get("telefono");
            $mensaje .= "Teléfono: " . $request->get("telefono") . "<br/>";
        } else {
            $telefono = "";
        }
        if (!empty($request->get("dia"))) {
            $fecha = $request->get("dia");
            $mensaje .= "Fecha: " . $request->get("dia") . "/" . $request->get("mes") . "/" . $request->get("anyo") . "<br/>";
        } else {
            $email = "";
        }        
        if (!empty($request->get("empresa"))) {
            $empresa = $request->get("empresa");
            $mensaje .= "Empresa: " . $request->get("empresa") . "<br/>";
        } else {
            $empresa = "";
        }        
        if (!empty($request->get("area_interes"))) {
            $area_interes = $request->get("area_interes");
            $mensaje .= "Área de interés: " . $request->get("area_interes") . "<br/>";
        } else {
            $area_interes = "";
        }
        if (!empty($request->get("mensaje"))) {
            $consulta = $request->get("mensaje");
            $mensaje .= "Mensaje: " . $request->get("mensaje") . "<br/>";
        } else {
            $consulta = "";
        }
        if (!empty($request->get("politica"))) {
            $firstpolitica = $request->get("politica");
            $mensaje .= "Politica: " . $request->get("politica") . "<br/>";
        } else {
            $firstpolitica = "";
        }
        if (!empty($request->get("tipo_consulta"))) {
            $tipo_consulta = $request->get("tipo_consulta");
            $mensaje .= "Tipo de Consulta: " . $request->get("tipo_consulta") . "<br/>";
        } else {
            $tipo_consulta = "";
        }
        if (!empty($request->get("activo"))) {
            $activo = $request->get("activo");
            $mensaje .= "Trabaja actualmente: " . $request->get("activo") . "<br/>";
        } else {
            $activo = "";
        }
        if (!empty($request->get("otros-procesos"))) {
            $otros = $request->get("otros-procesos");
            if ($request->get("otros-procesos") == "on")
            {
                $mensaje .= "Esta interesado en otros procesos? Si <br/>";
            } else {
                $mensaje .= "Esta interesado en otros procesos? No <br/>";
            }
            
        } else {
            $otros = "";
        }
        
        if (!empty($request->get("newsletter") && $request->get("newsletter") == "on")) {
          $this->createContact($email, $nombre, $lang);
        }
        
        $emailgracias = $email;
        $to = "social@ajusa.es";
        switch ($tipo_contacto) {
                case "trabaja":
                    $to = "jcifuentes@corporacionhms.com";
                    $subject = "Curriculum enviado"; 
                    break;
                case "contacto":
                    $to = "social@ajusa.es";
                    $subject = "contacto general"; 
                    break;                
                default:
                    $to = "social@ajusa.es";
                    $subject = "contacto general"; 
                    break;
        }
        
  
       
        $message = \Swift_Message::newInstance();
  
        
          //$sendmail=$this->container->get("app.sendmail");
        $message->setSubject( "[Ajusa]: ".$subject)
          ->setFrom("mailer@ajusa.es")
          ->setTo( [$to] )
          ->setContentType("text/html")
          ->setBody(                  
              $this->renderView(
                  // app/Resources/views/Emails/registration.html.twig
                  "emails/base.html.twig",
                  array(
                      "title" => $subject,
                      "logo" => "",
                      "mensaje"=> $mensaje,
                      "politica" => "")
              ),
              'text/html'
          );
        if ( $request->files->get('doc_adjunto') != null) {
            $ficha_producto = str_replace(' ', '%20', $request->files->get('doc_adjunto')->getClientOriginalName());
            $publicResourcesFolderPath = '/var/www/gestor_ajusa/web/bundles/front/attach/';
            $request->files->get('doc_adjunto')->move($publicResourcesFolderPath, $ficha_producto);
            $message->attach(\Swift_Attachment::fromPath($publicResourcesFolderPath.'/'.$ficha_producto));
        }
          
        $this->get('mailer')->send($message);
          
          //unlink("/home/www/back-dcoop/public/files/cv/" . $ficha_producto);
      
          
          
      
  
     /*  $mensajegracias = "Muchas gracias por ponerte en contacto con nosotros, te responderemos lo antes posible.";
      if (!empty($emailgracias)) {

        $sendmail=$this->container->get("app.sendmail");
  
        $params["subject"]   = "[Ajusa]: Gracis por ponerte en contacto";
        $params["to"]     = "millan.hermana@doubledot.es"; //$to;
        $params["from"]     = "mailer@corporacionhms.com";
        $params["template"] = "base.html.twig";
        $params["datos"] = $mensajegracias;
        //$params["perfil"]   = "http://".$_SERVER["HTTP_HOST"]."/perfil";
        $sendmail->send($params);
      } */
            
      return new JsonResponse(1);

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
        ->setFrom('mailer@corporacionhms.com')
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

                    if (!empty($request->get("newsletter") && $request->get("newsletter"))) {
                        $this->createContact($email, $nombre, $lang);
                    }
                    
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
      $comentarios = $noticia->getComentarios();
      $aux_comentarios = 0;
      foreach ($comentarios as $com) {
          if ($com->getPermitido()) {
              $aux_comentarios++;
          }
      }
      $aux["comentarios"]=$aux_comentarios;
      $aux["hints"]=$noticia->getHints();
      $aux["fecha"]=$noticia->getFechaPublicacion()->format("Y-m-d");
        

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
                        if (strpos($modulo["valor"],"&t=")) {
                            $aux["enlace"]= str_replace("&t=", "?start=", $modulo["valor"]);
                        } else {
                            $aux["enlace"]=$modulo["valor"];
                        }
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
            if($comentario->getIdioma()==$lang&&$comentario->getPermitido())
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
     * @Route("/pdfs")
     */
    public function pdfs(Request $request)
    {
        $json = [
            [
              "titulo" => "ACURA – STERLING",
              "archivo" => "ACURA.PDF"
            ],           
            [
              "titulo"=> "Alfa Romeo",
              "archivo" => "ALFA_ROMEO.PDF"
            ],
            [
              "titulo"=> "Asia Motor S",
              "archivo" => "ASIA_MOTOR_S.PDF"
            ],
            [
              "titulo"=> "Audi",
              "archivo" => "AUDI.PDF"
            ],
            [
              "titulo"=> "Austin",
              "archivo" => "AUSTIN.PDF"
            ],
            [
              "titulo"=> "Autobianchi",
              "archivo" => "AUTOBIANCHI.PDF"
            ],
            [
              "titulo"=> "Bedford",
              "archivo" => "BEDFORD.PDF"
            ],
            [
              "titulo"=> "Bmw",
              "archivo" => "BMW.PDF"
            ],
            [
              "titulo"=> "Campeon",
              "archivo" => "CAMPEON.PDF"
            ],
            [
              "titulo"=> "Caterpillar",
              "archivo" => "CATERPILLAR.PDF"
            ],
            [
              "titulo"=> "Chery",
              "archivo" => "CHERY.PDF"
            ],
            [
              "titulo"=> "Chevrolet – OLDSMOBILE ",
              "archivo" => "CHEVROLET.PDF"
            ],            
            [
              "titulo"=> "Chrysler",
              "archivo" => "CHRYSLER.PDF"
            ],
            [
              "titulo"=> "Citroen",
              "archivo" => "CITROEN.PDF"
            ],
            [
              "titulo"=> "Cummins",
              "archivo" => "CUMMINS.PDF"
            ],
            [
              "titulo"=> "Dacia",
              "archivo" => "DACIA.PDF"
            ],
            [
              "titulo"=> "Daewoo",
              "archivo" => "DAEWOO.PDF"
            ],
            [
              "titulo"=> "Daf",
              "archivo" => "DAF.PDF"
            ],
            [
              "titulo"=> "Daihatsu",
              "archivo" => "DAIHATSU.PDF"
            ],
            [
              "titulo"=> "Deutz",
              "archivo" => "DEUTZ.PDF"
            ],
            [
              "titulo"=> "Diter MWM",
              "archivo" => "DITER_(MWM).PDF"
            ],
            [
              "titulo"=> "Dodge",
              "archivo" => "DODGE.PDF"
            ],
            [
              "titulo"=> "Eagle",
              "archivo" => "EAGLE.PDF"
            ],
            [
              "titulo"=> "Ebro",
              "archivo" => "EBRO.PDF"
            ],            
            [
              "titulo"=> "Ebro-Kubota",
              "archivo" => "EBRO-KUBOTA.PDF"
            ],
            [
              "titulo"=> "Fendt",
              "archivo" => "FENDT.PDF"
            ],
            [
              "titulo"=> "Fiat Tractor",
              "archivo" => "FIAT_(TRACTOR).PDF"
            ],
            [
              "titulo"=> "Fiat",
              "archivo" => "FIAT.PDF"
            ],
            [
              "titulo"=> "Ford",
              "archivo" => "FORD.PDF"
            ],
            [
              "titulo"=> "Ford Industrial",
              "archivo" => "FORD_(INDUSTRIAL).PDF"
            ],
            [
              "titulo"=> "Galloper",
              "archivo" => "GALLOPER.PDF"
            ],
            [
              "titulo"=> "GEO",
              "archivo" => "GEO.PDF"
            ],
            [
              "titulo"=> "GM-Chevrolet",
              "archivo" => "GM-CHEVROLET.PDF"
            ],
            [
              "titulo"=> "Hino",
              "archivo" => "HINO.PDF"
            ],
            [
              "titulo"=> "Holden",
              "archivo" => "HOLDEN.PDF"
            ],
          
            [
              "titulo"=> "Honda",
              "archivo" => "HONDA.PDF"
            ],
            [
              "titulo"=> "Hyundai",
              "archivo" => "HYUNDAI.PDF"
            ],
            [
              "titulo"=> "Infiniti",
              "archivo" => "INFINITI.PDF"
            ],
            [
              "titulo"=> "Innocenti",
              "archivo" => "INNOCENTI.PDF"
            ],
            [
              "titulo"=> "Isuzu",
              "archivo" => "ISUZU.PDF"
            ],
            [
              "titulo"=> "Iveco-Fiat Industrial",
              "archivo" => "IVECO.PDF"
            ],            
            [
              "titulo"=> "Jeep",
              "archivo" => "JEEP.PDF"
            ],
            [
              "titulo"=> "John Deere",
              "archivo" => "JOHN_DEERE.PDF"
            ],
            [
              "titulo"=> "KIA",
              "archivo" => "KIA.PDF"
            ],
            [
              "titulo"=> "Lada",
              "archivo" => "LADA.PDF"
            ],
            [
              "titulo"=> "Lancia",
              "archivo" => "LANCIA.PDF"
            ],
            [
              "titulo"=> "Land Rover",
              "archivo" => "LAND_ROVER.PDF"
            ],
            [
              "titulo"=> "Lexus",
              "archivo" => "LEXUS.PDF"
            ],
            [
              "titulo"=> "Leyland",
              "archivo" => "LEYLAND.PDF"
            ],            
            [
              "titulo"=> "LOMBARDINI – AIXAM – LIGIER",
              "archivo" => "LOMBARDINI.PDF"
            ],
            [
              "titulo"=> "Mahindra",
              "archivo" => "MAHINDRA.PDF"
            ],
            [
              "titulo"=> "MAN",
              "archivo" => "MAN.PDF"
            ],
            [
              "titulo"=> "Maruti",
              "archivo" => "MARUTI.PDF"
            ],
            [
              "titulo"=> "MAXION-PERKINS",
              "archivo" => "MAXION-PERKINS.PDF"
            ],
            [
              "titulo"=> "Mazda",
              "archivo" => "MAZDA.PDF"
            ],
            [
              "titulo"=> "Mercedes",
              "archivo" => "MERCEDES.PDF"
            ],
            [
              "titulo"=> "Mercedes Industrial",
              "archivo" => "MERCEDES_INDUSTRIAL.PDF"
            ],
            [
              "titulo"=> "Mini",
              "archivo" => "MINI.PDF"
            ],
            [
              "titulo"=> "Mitsubishi",
              "archivo" => "MITSUBISHI.PDF"
            ],
            [
              "titulo"=> "Nissan",
              "archivo" => "NISSAN.PDF"
            ],
            [
              "titulo"=> "OPEL -VAUXHALL",
              "archivo" => "OPEL.PDF"
            ],            
            [
              "titulo"=> "Pegaso",
              "archivo" => "PEGASO.PDF"
            ],
            [
              "titulo"=> "Perkins",
              "archivo" => "PERKINS.PDF"
            ],
            [
              "titulo"=> "Peugeot",
              "archivo" => "PEUGEOT.PDF"
            ],
            [
              "titulo"=> "Piaggio",
              "archivo" => "PIAGGIO.PDF"
            ],
            [
              "titulo"=> "Proton",
              "archivo" => "PROTON.PDF"
            ],
            [
              "titulo"=> "Renault",
              "archivo" => "RENAULT.PDF"
            ],
            [
              "titulo"=> "Rover Group",
              "archivo" => "ROVER_GROUP.PDF"
            ],
            [
              "titulo"=> "RVI",
              "archivo" => "RVI.PDF"
            ],
            [
              "titulo"=> "Saab",
              "archivo" => "SAAB.PDF"
            ],
            [
              "titulo"=> "Same",
              "archivo" => "SAME.PDF"
            ],
            [
              "titulo"=> "Saturn",
              "archivo" => "SATURN.PDF"
            ],
            [
              "titulo"=> "Sava",
              "archivo" => "SAVA.PDF"
            ],
            [
              "titulo"=> "Saviem",
              "archivo" => "SAVIEM.PDF"
            ],
            [
              "titulo"=> "Scania",
              "archivo" => "SCANIA.PDF"
            ],
            [
              "titulo"=> "Scion",
              "archivo" => "SCION.PDF"
            ],
            [
              "titulo"=> "Seat",
              "archivo" => "SEAT.PDF"
            ],
            [
              "titulo"=> "Skoda",
              "archivo" => "SKODA.PDF"
            ],
            [
              "titulo"=> "Smart",
              "archivo" => "SMART.PDF"
            ],[
              "titulo"=> "Ssangyong",
              "archivo" => "SSANGYONG.PDF"
            ],
            [
              "titulo"=> "Subaru",
              "archivo" => "SUBARU.PDF"
            ],
            [
              "titulo"=> "Suzuki",
              "archivo" => "SUZUKI.PDF"
            ],
            [
              "titulo"=> "Talbot",
              "archivo" => "TALBOT.PDF"
            ],
            [
              "titulo"=> "Tata",
              "archivo" => "TATA.PDF"
            ],
            [
              "titulo"=> "Toyota",
              "archivo" => "TOYOTA.PDF"
            ],
            [
              "titulo"=> "Trabant",
              "archivo" => "TRABANT.PDF"
            ],
            [
              "titulo"=> "Triumph",
              "archivo" => "TRIUMPH.PDF"
            ],
            [
              "titulo"=> "Volkswagen",
              "archivo" => "VOLKSWAGEN.PDF"
            ],
            [
              "titulo"=> "Volvo",
              "archivo" => "VOLVO.PDF"
            ],
            [
              "titulo"=> "Volvo Industrial",
              "archivo" => "VOLVO_INDUSTRIAL.PDF"
            ],
            [
              "titulo"=> "WARTBURG",
              "archivo" => "WARTBURG.PDF"
            ],
            [
              "titulo"=> "ZASTAVA",
              "archivo" => "ZASTAVA.PDF"
            ]
            ]; 
          return new JsonResponse(array("pdfs"=>$json,"code"=>200));
    }

    /**
     * @Route("/pdfs-retenes")
     */
    public function pdfsRetenes(Request $request)
    {
        $json = [
            [
              "titulo" => "ACURA – STERLING",
              "archivo" => "ACURA_CONTENIDO.PDF"
            ],           
            [
              "titulo"=> "Alfa Romeo",
              "archivo" => "ALFAROMEO_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Asia Motor S",
              "archivo" => "ASIAMOTORS_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Audi",
              "archivo" => "AUDI_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "AUTOBIANCHI",
              "archivo" => "AUTOBIANCHI_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Bedford",
              "archivo" => "BEDFORD_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Bmw",
              "archivo" => "BMW_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Chery",
              "archivo" => "CHERY_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Chevrolet – OLDSMOBILE ",
              "archivo" => "CHEVROLET_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Chrysler",
              "archivo" => "CHRYSLER_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Citroen",
              "archivo" => "CITROEN_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Dacia",
              "archivo" => "DACIA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Daewoo",
              "archivo" => "DAEWOO_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Daihatsu",
              "archivo" => "DAIHATSU_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Dodge",
              "archivo" => "DODGE_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Eagle",
              "archivo" => "EAGLE_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Fiat",
              "archivo" => "FIAT_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Ford",
              "archivo" => "FORD_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Ford Industrial",
              "archivo" => "FORD_(INDUSTRIAL)_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Galloper",
              "archivo" => "GALLOPER_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "GEO",
              "archivo" => "GEO_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "GM-Chevrolet",
              "archivo" => "GMCHEVROLET_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Holden",
              "archivo" => "HOLDEN_CONTENIDO.PDF"
            ],          
            [
              "titulo"=> "Honda",
              "archivo" => "HONDA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Hyundai",
              "archivo" => "HYUNDAI_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Innocenti",
              "archivo" => "INNOCENTI_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Isuzu",
              "archivo" => "ISUZU_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Iveco-Fiat Industrial",
              "archivo" => "IVECO_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Jeep",
              "archivo" => "JEEP_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "KIA",
              "archivo" => "KIA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Lada",
              "archivo" => "LADA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Lancia",
              "archivo" => "LANCIA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Land Rover",
              "archivo" => "LAND_ROVER_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Leyland",
              "archivo" => "LEYLAND_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Mahindra",
              "archivo" => "MAHINDRA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Maruti",
              "archivo" => "MARUTI_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Mazda",
              "archivo" => "MAZDA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Mini",
              "archivo" => "MINI_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Mitsubishi",
              "archivo" => "MITSUBISHI_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Nissan",
              "archivo" => "NISSAN_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "OPEL -VAUXHALL",
              "archivo" => "OPEL_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Pegaso",
              "archivo" => "PEGASO_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Peugeot",
              "archivo" => "PEUGEOT_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Proton",
              "archivo" => "PROTON_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Renault",
              "archivo" => "RENAULT_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Rover Group",
              "archivo" => "ROVER_GROUP_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Saab",
              "archivo" => "SAAB_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Saturn",
              "archivo" => "SATURN_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Saviem",
              "archivo" => "SAVIEM_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Seat",
              "archivo" => "SEAT_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Skoda",
              "archivo" => "SKODA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Subaru",
              "archivo" => "SUBARU_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Suzuki",
              "archivo" => "SUZUKI_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Talbot",
              "archivo" => "TALBOT_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Tata",
              "archivo" => "TATA_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Toyota",
              "archivo" => "TOYOTA_CONTENIDO.PDF"
            ],            
            [
              "titulo"=> "Volkswagen",
              "archivo" => "VOLKSWAGEN_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "Volvo",
              "archivo" => "VOLVO_CONTENIDO.PDF"
            ],
            [
              "titulo"=> "ZASTAVA",
              "archivo" => "ZASTAVA_CONTENIDO.PDF"
            ]
            ]; 
          return new JsonResponse(array("pdfs"=>$json,"code"=>200));
    }

    /**
     * @Route("/tweets")
     */
    public function tweets(Request $request)
    {
        $connection = new TwitterOAuth('jK2s2Kow0oNCZ0CAXYf2IyvXK', 'EuGvFjqf2Kb0ThqCijNB93Nf29iAoJfIqEJIds6O0FyHQ6acen', '1010836765803012099-CrKfWRDGubqBF1eu06gVwmwpkmISbY', '8ZXuKi1bxKZ0iZTojhzHV2f5dkS9ZAszvBhYNHj0Vd4Z5');
        $content = $connection->get("account/verify_credentials");
        
        $tweets_result = $connection->get("statuses/user_timeline", ["user_id" => "788925601", "count" => 7, "exclude_replies" => true]);
        return new JsonResponse(array("tweets"=>$tweets_result,"code"=>200));
       
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

     /**
     * @Route("/addUser")
     * @Method("POST")
     */
    public function addUser(Request $request) 
    {
        //
        $lang = $request->get('lang');
        $email = $request->get('email');
        if (!empty($email))
        {
            $this->createContact($email, "", $lang);
        }

        return new JsonResponse(array("code"=>200, "email" => $email, "lang" => $lang));
    }


    private function createContact($Cemail, $nombre = "", $lang = "es")
    {
        $logger = $this->get("logger");
        $logger->info($lang);
        if (empty($nombre)) {
            $nombre = $Cemail;
        }
        if ("es" == $lang) {            
            $listId = "1537891";
        } else {            
            $listId = "1537892";
        }
        if (empty($nombre)) {
            $nombre = $Cemail;
        }
        $logger->info($Cemail . " " . $nombre);
        $contact = new Contact($Cemail, $nombre, []);
    
        $this->container->get('event_dispatcher')->dispatch(
            ContactEvent::EVENT_SUBSCRIBE,
            new ContactEvent($listId, $contact)
        );
    }

   /**
     * @Route("/addUserAldonza")
     * @Method({"POST", "OPTIONS"})
     */
    public function addUserAldonza(Request $request) 
    {
        //
        $lang = $request->get('lang');
        $email = $request->get('email');
        if (!empty($email))
        {
            $this->createContactAldonza($email, "", $lang);
        }

        return new JsonResponse(array("code"=>200, "email" => $email, "lang" => $lang));
    }


    private function createContactAldonza($Cemail, $nombre = "", $lang = "es")
    {
        if (empty($nombre)) {
            $nombre = $Cemail;
        }
        if ("es" == $lang) {            
            $listId = "2332388";
        } else {            
            $listId = "2332389";
        }
        if (empty($nombre)) {
            $nombre = $Cemail;
        }
        
        $contact = new Contact($Cemail, $nombre, []);
    
        $this->container->get('event_dispatcher')->dispatch(
            ContactEvent::EVENT_SUBSCRIBE,
            new ContactEvent($listId, $contact)
        );
    }
}
