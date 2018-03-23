<?php

namespace NoticiasBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Controller\BaseController;

use NoticiasBundle\Entity\Noticia;

use NoticiasBundle\Form\NoticiaForm;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class NoticiasController extends BaseController
{
    /**
     * @Route("",name="listado_noticias")
     
     */
    public function indexAction()
    {
        $params["noticias"] = $this->findByField("NoticiasBundle:Noticia",array(),array("created"=>"DESC"));

        return $this->render('NoticiasBundle:Default:noticias.html.twig',$params);
    }

    /**
     * @Route("anadir",name="anadir_noticia")
     
     */
    public function anadir(Request $request)
    {

        return $this->gestion_noticia($request);
    }

    /**
     * @Route("editar/{id}",name="editar_noticia")
     
     */
    public function editar(Request $request,$id)
    {
        return $this->gestion_noticia($request,$id);
    }

    /**
     * @Route("borrar/{id}",name="eliminar_noticia")
     
     */
    public function borrar(Request $request,$id)
    {
        $translator=$this->get("translator");

        $noticia = $this->findById("NoticiasBundle:Noticia",$id);

        if($noticia!=null)
        {
            try 
            {
                $this->borrar_entity($noticia);

            } catch (Exception $e)
            {
                return new JsonResponse(array("status"=>500,"message"=>$this->mensaje_error($e)));            
            }

            return new JsonResponse(array("status"=>200,"message"=>$translator->trans("Eliminado Correctamente")));
        }
        else
            return new JsonResponse(array("status"=>404,"message"=>$translator->trans("Esta noticia no existe")));   
    }

    
    private function gestion_noticia($request, $id=null)
    {
        $translator=$this->get("translator");

        if($request->request->get("noticia_form")!=null && array_key_exists ("id",$request->request->get("noticia_form")))
        {
            $noticia = $this->findById("NoticiasBundle:Noticia",$request->request->get("noticia_form")["id"]);
            $accion = $request->request->get("boton")=="submit"?"editar":"borrar";
            
        }
        elseif ($id!=null) 
        {
            $noticia = $this->findById("NoticiasBundle:Noticia",$id);
            $accion = $request->request->get("boton")=="submit"?"editar":"borrar";
            
        }
        else
        {
            $noticia = new Noticia();
            $accion = "nuevo";
        }
        
        if($noticia->getId()!=null)
        {
            $aux=array("es"=>null,"en"=>null);

            $noticia->imagen_aux=$aux;
            $noticia->descargable_aux=$aux;

            foreach ($noticia->getIdioma() as $lang => $flag) 
            {
                if($flag)
                {
                    $params["imagen"][$lang]=$noticia->getImagen()[$lang];
                    $params["cuerpo"]=$noticia->getCuerpo();
                }
            }
        }
        
        $form   = $this->createForm(NoticiaForm::class,$noticia);

        if($accion!="nuevo")
        {
            $form->add("delete",SubmitType::class,array("label"=>"Eliminar"));
        }
        

        if($request->request->count()) 
        {        
            
            $response=array();
            
            switch ($accion) 
            {
                case 'nuevo':
                    try
                    {
                        $noticia=$this->parsear_noticia($request,$noticia);
                        
                        $noticia=$this->insertar_entity($noticia);

                        $response["status"]=200;
                        $response["message"]=$translator->trans("Creado Correctamente");
                        $response["id"]=$noticia->getId();

                    }
                    catch(\Exception $e) 
                    {                        
                        
                        $response["status"]=500;
                        $response["message"]=$this->mensaje_error($e);
                    }
                break;

                case "editar":
                    try
                    {
                        $noticia=$this->parsear_noticia($request,$noticia);
                        $noticia=$this->insertar_entity($noticia);                        
                        $response["status"]=200;
                        $response["message"]=$translator->trans("Guardado Correctamente");                        

                    }
                    catch(\Exception $e) 
                    {                        
                        
                        $response["status"]=500;
                        $response["message"]=$this->mensaje_error($e);
                    }
                break;

                case "borrar":
                    try
                    {
                        $this->borrar_entity($noticia);
                        $this->addFlash("success",$translator->trans("Eliminado Correctamente"));                       

                        $response["status"]=200;
                        $response["redirect"]=$this->generateUrl("listado_noticias");
                    }
                    catch(\Exception $e) 
                    {                        
                        
                        $response["status"]=500;
                        $response["message"]=$this->mensaje_error($e);
                    }
                break;
                
                
            }                       
            
            return new JsonResponse($response);

        }
        
        $params["form"] =   $form->createView();
        return $this->render('NoticiasBundle:Form:noticia.html.twig',$params);
    }
    private function generar_slug($noticia,$lang_code)
    {
    	$slugs=$noticia->getSlug();
        $old_slug=$slugs[$lang_code];

    	$em=$this->getDoctrine()->getManager();
		
        $new_slug=$this->container->get("normalizator")->calculate_slug($noticia->getTitulo()[$lang_code]);
        

        if($noticia->getId()!=null)
        {
            $result = $em->createQuery("SELECT n FROM NoticiasBundle:Noticia n where n.slug like :slug  and n.id != :id")->setParameter("slug","%".$new_slug."%")->setParameter("id",$noticia->getId());
        }
        else
        {
            $result = $em->createQuery("SELECT n FROM NoticiasBundle:Noticia n where n.slug like :slug")->setParameter("slug","%".$new_slug."%");
        }

        $result=$result->getResult();

		if(count($result)>0)        
           $new_slug=$new_slug."-".count($result);
                
        
        $slugs[$lang_code]=$new_slug;     
    	
        
        $noticia->setSlug($slugs);
        
        if($old_slug!=null && $old_slug!=$new_slug)
        {
            $noticia->rename["old"]=$old_slug;
            $noticia->rename["new"]=$new_slug;
            $noticia->rename["lang"]=$lang_code;
        }

        return $noticia;
    }


    private function parsear_noticia($request,$noticia)
    {
        

        $noticia_aux = $request->request->all();
        $noticia_aux = $noticia_aux["noticia_form"];
        $files_aux   = $request->files->all();

        if(isset($files_aux["noticia_form"]))
            $files_aux   = $files_aux["noticia_form"];
        else
            $files["noticia_form"]=array();        

        $fecha=$noticia_aux["fecha_publicacion"];
        $fecha=explode("/", $fecha);

        $aux=$fecha[2];

        $fecha[2]=$fecha[0];

        $fecha[0]=$aux;

        $fecha=implode("-", $fecha);


        $fecha = new \DateTime($fecha." 00:00:00");

        $noticia->setVisible($noticia_aux["visible"]);
        $noticia->setDestacado($noticia_aux["destacado"]);

        $lang_code = key($noticia_aux["idioma"]);

        $langs = $noticia->getIdioma();

        $langs[$lang_code] = $noticia_aux["idioma"][$lang_code]==1?true:false;
        
        $titulos = $noticia->getTitulo();        
        
        $titulos[$lang_code] = $noticia_aux["titulo"][$lang_code];


        $entradillas = $noticia->getEntradilla();

        $entradillas[$lang_code] = $noticia_aux["entradilla"][$lang_code];


        $noticia->setFechaPublicacion($fecha);
        $noticia->setIdioma($langs);
        $noticia->setTitulo($titulos);
        $noticia->setEntradilla($entradillas);
        
        $noticia=$this->generar_slug($noticia,$lang_code);

        $slugs=$noticia->getSlug();
        $imagen=$noticia->getImagen();

        if(isset($files_aux["imagen_aux"]))
                    
            $imagen[$lang_code]["valor"]=$noticia->subir($files_aux["imagen_aux"][$lang_code],$slugs[$lang_code]);
        

        $imagen[$lang_code]["pie"]=$noticia_aux["imagen_aux"][$lang_code]["pie"];

        $noticia->setImagen($imagen);

        $cuerpo=$noticia->getCuerpo();

        $cuerpo[$lang_code]=null;

        if(isset($noticia_aux["cuerpo"][$lang_code]))
            $total=count($noticia_aux["cuerpo"][$lang_code]);
        else
            $total=0;
        for($i=0;$i<$total;$i++)
        {
            if(isset($noticia_aux["cuerpo"][$lang_code][$i]))
            {
                $tipo=key($noticia_aux["cuerpo"][$lang_code][$i]);
                switch ($tipo) 
                {
                    case 'image':
                        $cuerpo[$lang_code][$i]["tipo"]="imagen";
                        if ($noticia_aux["cuerpo"][$lang_code][$i]["image"]["accion"]=="anadir")              
                                
                            $cuerpo[$lang_code][$i]["valor"]=$noticia->subir($files_aux["cuerpo"][$lang_code][$i]["image"],$slugs[$lang_code]);

                        else

                            $cuerpo[$lang_code][$i]["valor"]=$noticia_aux["cuerpo"][$lang_code][$i]["image"]["accion"];

                        $cuerpo[$lang_code][$i]["pie"]=$noticia_aux["cuerpo"][$lang_code][$i]["image"]["pie"];
                                                        
                    break;
                    
                    case "texto":
                    case "destacado":
                        
                        $cuerpo[$lang_code][$i]["tipo"]=$tipo;
                        $cuerpo[$lang_code][$i]["valor"]=$noticia_aux["cuerpo"][$lang_code][$i][$tipo];
                        
                    break;
                    case "video":
                        $cuerpo[$lang_code][$i]["tipo"]=$tipo;
                        $cuerpo[$lang_code][$i]["valor"]=$noticia_aux["cuerpo"][$lang_code][$i][$tipo]["valor"];
                        $cuerpo[$lang_code][$i]["pie"]=$noticia_aux["cuerpo"][$lang_code][$i][$tipo]["pie"];

                    break;
                    case "despiece":
                        $cuerpo[$lang_code][$i]["tipo"]=$tipo;
                        $cuerpo[$lang_code][$i]["titulo"]=$noticia_aux["cuerpo"][$lang_code][$i][$tipo]["titulo"];
                        $cuerpo[$lang_code][$i]["enlace"]=$noticia_aux["cuerpo"][$lang_code][$i][$tipo]["enlace"];

                    break;
                }
            }                    
                
        }
        $noticia->setCuerpo($cuerpo);

        
        if(isset($noticia_aux["categoria"]))
                    
            $categoria=$this->findById("CategoriasBundle:Categoria",$noticia_aux["categoria"]);
        
        else
        
            $categoria= null;            
        

        $noticia->setCategoria($categoria);

        return $noticia;
    }
}
