<?php

namespace PrensaBundle\Controller;
use AppBundle\Controller\BaseController;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use PrensaBundle\Entity\Descargable;
use PrensaBundle\Form\DescargableForm;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DefaultController extends BaseController
{
    /**
     * @Route("descargables",name="listado_descargables")
     */
    public function listado()
    {
        $params["descargables"]=$this->findByField("PrensaBundle:Descargable",array(),array("creado"=>"DESC"));
        $params["categorias"]=array(

                    "actualidad"=>"Actualidad",
                    "catalogo"=>"Catálogo",
                    "ferias"=>"Ferias",
                    "historia" => "Historia",
                    "energias-alternativas"=>"Energías alternativas",
                    "videos"=>"Vídeos",
                    "informes-tecnicos"=>"Informes técnicos",
                    "webinarios"=>"Webinarios",
                    "otros"=>"Otros",
                );
        return $this->render('PrensaBundle:Default:listado.html.twig',$params);
    }
    /**
     * @Route("descargable/añadir",name="añadir_descargable")
     */
    public function añadir(Request $request)
    {
        return $this->gestionar_destacado($request);
    }

    /**
     * @Route("descargable/editar/{id}",name="editar_descargable")
     */
    public function editar(Request $request,$id)
    {
        return $this->gestionar_destacado($request,$id);
    }

    /**
     * @Route("descargable/eliminar",name="eliminar_descargable")
     */
    public function eliminar(Request $request)
    {
        $id=$request->request->get("id");

        $descargable=$this->findById("PrensaBundle:Descargable",$id);        

        

        if($descargable)
        {
            try 
            {
               $this->borrar_entity($descargable);
               $mensaje="Eliminado Correctamente";
               $status_code=200;
                
            } catch (Exception $e) {
                $mensaje="Algo ha ido mal";
                $status_code=500;
                return $this->json(array("status"=>$status_code,"mensaje"=>$mensaje),500);
            }            
        }
        else
        {
            $mensaje="Este descargable no existe";
            $status_code=404;
        }
        return $this->json(array("status_code"=>$status_code,"message"=>$mensaje));
        
    }
    
    /**
     * @Route("descargable/estado",name="cambiar_estado_descargable")
     */
    public function cambiar_estado(Request $request)
    {
        $id=$request->request->get("id");

        $descargable=$this->findById("PrensaBundle:Descargable",$id);

        if($descargable==null)
            return new JsonResponse(array("mensaje"=>"Este descargable no existe"),500);


        $estado=!$descargable->getVisible();

        $descargable->setVisible($estado);

        try
        {
            $this->editar_entity($descargable);            
        }
        catch(\Exception $e)
        {
            return new JsonResponse(array("mensaje"=>$this->mensaje_error($e)),500);
        }

        return new JsonResponse(array("estado"=>$estado,"mensaje"=>"El descargable se ha ".($estado?"activado":"desactivado")." satisfactoriamentes"));

    }


    private function gestionar_destacado(Request $request,$id=null)
    {
		$accion=$id==null?"nuevo":"editar";

    	if($id==null)
    		$descargable= new Descargable();
    	else
    	
    		$descargable= $this->findById("PrensaBundle:Descargable",$id);    	

    	if($descargable==null)
    	{
		    $this->addFlash("success","El destacado no existe");
        	return $this->redirect($this->generateUrl("listado_destacados"));
    	}

        $params["archivo"]=$descargable->getArchivo();

        switch ($descargable->getCategoria()) 
        {        
            case "logotipo":
            case "imagen":
            
                if($descargable->getMiniatura()!="")
                    $params["imagen"]=$descargable->getMiniatura();
                
            break;
        }

    	$form 	= $this->createForm(DescargableForm::class,$descargable);

    	if($id!=null)
            $form->add("delete",SubmitType::class, array("label"=>"Eliminar descargable"));

        $form	->  handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) 
        {
            $redirect=true;

        	switch ($form->getClickedButton()->getName()) 
        	{
        		case 'submit':

        			switch ($accion) 
        			{
        				case 'nuevo':

        					try
				        	{
				        		//TODO: COMPROBAR DATOS
				        	    $this->insertar_entity($form->getData());
				                $this->addFlash("success","Guardado Correctamente");  				                
				        	}
				        	catch(\Exception $e)
				            {                
				                $this->addFlash("error",$this->mensaje_error($e));
				                $redirect=false;
				            }

    					break;
        				
        				case 'editar':
        					try
				        	{
                                                                
				        		
				        		//TODO: COMPROBAR DATOS
				        	    $this->insertar_entity($form->getData());
				                $this->addFlash("success","Guardado Correctamente");				                
				        	}
				        	catch(\Exception $e)
				            {                
                                
				                $this->addFlash("error",$this->mensaje_error($e));
				                $redirect=false;
				            }
    					break;
        			}
        			
    			break;
        		
        		case "delete":
        			try
		        	{
		        		//TODO: COMPROBAR que existe
		        	    $this->borrar_entity($form->getData());
		                $this->addFlash("success","Eliminado Correctamente");		                
		        	}
		        	catch(\Exception $e)
		            {                
		                $this->addFlash("error",$this->mensaje_error($e));
		                $redirect=false;
		            }
    			break;
        	}

        	if($redirect)
        	{
        		return $this->redirect($this->generateUrl("listado_descargables"));
        	}
        }

     	$params["form"] =   $form->createView();

    	return $this->render('PrensaBundle:Default:form.html.twig',$params);

    }
}
