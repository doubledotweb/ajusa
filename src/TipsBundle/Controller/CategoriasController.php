<?php

namespace TipsBundle\Controller;
use AppBundle\Controller\BaseController;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use TipsBundle\Entity\Categoria;
use TipsBundle\Form\CategoriaForm;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategoriasController extends BaseController
{
    /**
     * @Route("",name="listado_categorias")
     */
    public function listado()
    {
        $params["categorias"]=$this->findByField("TipsBundle:Categoria",array(),array("creado"=>"DESC"));
        
        return $this->render('TipsBundle:categorias:listado.html.twig',$params);
    }
    /**
     * @Route("añadir",name="añadir_categoria")
     */
    public function añadir(Request $request)
    {
        return $this->gestionar_categoria($request);
    }

    /**
     * @Route("editar/{id}",name="editar_categoria")
     */
    public function editar(Request $request,$id)
    {
        return $this->gestionar_categoria($request,$id);
    }

    /**
     * @Route("eliminar",name="eliminar_categoria")
     */
    public function eliminar(Request $request)
    {
        $id=$request->request->get("id");

        $categoria=$this->findById("TipsBundle:Categoria",$id);

        

        if($categoria)
        {
            try 
            {
               $this->borrar_entity($categoria);
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
            $mensaje="Esta categoría no existe";
            $status_code=404;
        }
        return $this->json(array("status_code"=>$status_code,"message"=>$mensaje));
        
    }    

    private function gestionar_categoria(Request $request,$id=null)
    {
		$accion=$id==null?"nuevo":"editar";

    	if($id==null)
    		$categoria= new Categoria();
    	else    	
    		$categoria= $this->findById("TipsBundle:Categoria",$id);
    		
    	

    	if($categoria==null)
    	{
		    $this->addFlash("success","El archivo no existe");
        	return $this->redirect($this->generateUrl("listado_categorias"));
    	}

    	$form 	= $this->createForm(CategoriaForm::class,$categoria);

    	if($id!=null)
            $form->add("delete",SubmitType::class, array("label"=>"Eliminar categoría"));

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
        		return $this->redirect($this->generateUrl("listado_categorias"));
        	}
        }
        
     	$params["form"] =   $form->createView();

    	return $this->render('TipsBundle:categorias:form.html.twig',$params);

    }
}
