<?php

namespace TipsBundle\Controller;
use AppBundle\Controller\BaseController;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use TipsBundle\Entity\Tip;
use TipsBundle\Form\TipForm;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Psr\Log\LoggerInterface;

class DefaultController extends BaseController
{
    /**
     * @Route("",name="listado_tips")
     */
    public function listado()
    {
        $params["tips"]=$this->findByField("TipsBundle:Tip",array(),array("creado"=>"DESC"));
        
        return $this->render('TipsBundle:Default:listado.html.twig',$params);
    }
    /**
     * @Route("añadir",name="añadir_tip")
     */
    public function añadir(Request $request)
    {
        return $this->gestionar_tip($request);
    }

    /**
     * @Route("editar/{id}",name="editar_tip")
     */
    public function editar(Request $request,$id)
    {
        return $this->gestionar_tip($request,$id);
    }

    /**
     * @Route("eliminar",name="eliminar_tip")
     */
    public function eliminar(Request $request)
    {
        $id=$request->request->get("id");

        $tip=$this->findById("TipsBundle:Tip",$id);        

        

        if($tip)
        {
            try 
            {
               $this->borrar_entity($tip);
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
            $mensaje="Este archivo no existe";
            $status_code=404;
        }
        return $this->json(array("status_code"=>$status_code,"message"=>$mensaje));
        
    }
    
    /**
     * @Route("estado",name="cambiar_estado_tip")
     */
    public function cambiar_estado(Request $request)
    {
        $id=$request->request->get("id");

        $tip=$this->findById("TipsBundle:Tip",$id);

        if($tip==null)
            return new JsonResponse(array("mensaje"=>"Este archivo no existe"),500);


        $estado=!$tip->getVisible();

        $tip->setVisible($estado);

        try
        {
            $this->editar_entity($tip);            
        }
        catch(\Exception $e)
        {
            return new JsonResponse(array("mensaje"=>$this->mensaje_error($e)),500);
        }

        return new JsonResponse(array("estado"=>$estado,"mensaje"=>"El archivo se ha ".($estado?"activado":"desactivado")." satisfactoriamentes"));

    }

    /**
     * @Route("destacado",name="cambiar_destacado_tip")
     */
    public function cambiar_destacado(Request $request)
    {
        $id=$request->request->get("id");

        $tip=$this->findById("TipsBundle:Tip",$id);

        if($tip==null)
            return new JsonResponse(array("mensaje"=>"Este archivo no existe"),500);


        $estado=!$tip->getDestacado();

        $tip->setDestacado($estado);

        try
        {
            $this->editar_entity($tip);            
        }
        catch(\Exception $e)
        {
            return new JsonResponse(array("mensaje"=>$this->mensaje_error($e)),500);
        }

        return new JsonResponse(array("estado"=>$estado,"mensaje"=>$estado?"El archivo ya está guardado como destacado":"El archivo ya no tiene la consideración de destacado"));

    }


    private function gestionar_tip(Request $request,$id=null)
    {
		$accion=$id==null?"nuevo":"editar";

    	if($id==null)
    		$tip= new Tip();
    	else
    	{
    		$tip= $this->findById("TipsBundle:Tip",$id);
    		$params["archivo"]=$tip->getArchivo();
    	}

    	if($tip==null)
    	{
		    $this->addFlash("success","El archivo no existe");
        	return $this->redirect($this->generateUrl("listado_tips"));
    	}

    	$form 	= $this->createForm(TipForm::class,$tip);

    	if($id!=null)
            $form->add("delete",SubmitType::class, array("label"=>"Eliminar archivo"));

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
                                /**
                                 * if (in_array($form["anio"]->getData(), $anios)) {
                                 *        throw new PreconditionFailedHttpException("Ya hay una configuracion para ese año");
                                 *   }
                                 */
                                
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
                                
                                /* $logger = $this->get('logger');
                                $logger->info($form["keywords"]->getData());
                                foreach ($form["keywords"]->getData() as $keyword)
                                {
                                    $logger->info($keyword->getTitulo());
                                } */
                                    
                                
				        		$form->getData()->actualizar_archivo();
				        		//TODO: COMPROBAR DATOS
				        	    $this->editar_entity($form->getData());
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
        		return $this->redirect($this->generateUrl("listado_tips"));
        	}
        }
        
     	$params["form"] =   $form->createView();

    	return $this->render('TipsBundle:Default:form.html.twig',$params);

    }
}
