<?php

namespace DestcadosBundle\Controller;
use AppBundle\Controller\BaseController;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use DestcadosBundle\Entity\Destacado;
use DestcadosBundle\Form\DestacadoForm;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DefaultController extends BaseController
{
    /**
     * @Route("",name="listado_destacados")
     */
    public function listado()
    {
        $params["destacados"]=$this->findByField("DestcadosBundle:Destacado",array(),array("creado"=>"DESC"));
        $params["categorias"]=array(

                    "actualidad"=>"Actualidad",
                     "catalogo"=>"Catálogo",
                    "ferias"=>"Ferias",
                    "energias-alternativas"=>"Energías alternativas",
                    "videos"=>"Vídeos",
                    "informes-tecnicos"=>"Informes técnicos",
                    "webinarios"=>"Webinarios",
                    "otros"=>"Otros",
                );
        return $this->render('DestcadosBundle:Default:listado.html.twig',$params);
    }
    /**
     * @Route("/añadir",name="añadir_destacado")
     */
    public function añadir(Request $request)
    {
        return $this->gestionar_destacado($request);
    }

    /**
     * @Route("editar/{id}",name="editar_destacado")
     */
    public function editar(Request $request,$id)
    {
        return $this->gestionar_destacado($request,$id);
    }

    /**
     * @Route("eliminar",name="eliminar_destacado")
     */
    public function eliminar(Request $request)
    {
        $id=$request->request->get("id");

        $destacado=$this->findById("DestcadosBundle:Destacado",$id);        

        

        if($destacado)
        {
            try 
            {
               $this->borrar_entity($destacado);
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
            $mensaje="Este destacado no existe";
            $status_code=404;
        }
        return $this->json(array("status_code"=>$status_code,"message"=>$mensaje));
        
    }
    
    /**
     * @Route("estado",name="cambiar_estado_destacado")
     */
    public function cambiar_estado(Request $request)
    {
        $id=$request->request->get("id");

        $destacado=$this->findById("DestcadosBundle:Destacado",$id);

        if($destacado==null)
            return new JsonResponse(array("mensaje"=>"Este destacado no existe"),500);


        $estado=!$destacado->getVisible();

        $destacado->setVisible($estado);

        try
        {
            $this->editar_entity($destacado);            
        }
        catch(\Exception $e)
        {
            return new JsonResponse(array("mensaje"=>$this->mensaje_error($e)),500);
        }
        if($estado=="activado")
        {
            $mensaje="El destacado ya es visible. Se mostrará o no en función de su fecha de creación";
        }
        else
        {
            $mensaje="El destacado ya no tiene visibilidad, por lo que no se mostrará.";
        }
        return new JsonResponse(array("estado"=>$estado,"mensaje"=>$mensaje));

    }


    private function gestionar_destacado(Request $request,$id=null)
    {
		$accion=$id==null?"nuevo":"editar";

    	if($id==null)
    		$destacado= new Destacado();
    	else
    		$destacado= $this->findById("DestcadosBundle:Destacado",$id);

    	if($destacado==null)
    	{
		    $this->addFlash("success","El destacado no existe");
        	return $this->redirect($this->generateUrl("listado_destacados"));
    	}

    	$form 	= $this->createForm(DestacadoForm::class,$destacado);

    	if($id!=null)
            $form->add("delete",SubmitType::class, array("label"=>"Eliminar destacado"));

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
        		return $this->redirect($this->generateUrl("listado_destacados"));
        	}
        }
        
     	$params["form"] =   $form->createView();

    	return $this->render('DestcadosBundle:Default:form.html.twig',$params);

    }
}
