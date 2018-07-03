<?php

namespace TipsBundle\Controller;
use AppBundle\Controller\BaseController;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use TipsBundle\Entity\Keyword;
use TipsBundle\Form\KeywordForm;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Psr\Log\LoggerInterface;

class KeywordsController extends BaseController
{
    /**
     * @Route("",name="listado_keywords")
     */
    public function listado()
    {
        $params["keywords"]=$this->findByField("TipsBundle:Keyword",array(),array("titulo"=>"DESC"));
        
        return $this->render('TipsBundle:keywords:listado.html.twig',$params);
    }
    /**
     * @Route("añadir",name="añadir_keyword")
     */
    public function añadir(Request $request)
    {
        return $this->gestionar_keyword($request);
    }

    /**
     * @Route("editar/{id}",name="editar_keyword")
     */
    public function editar(Request $request,$id)
    {
        return $this->gestionar_keyword($request,$id);
    }

    /**
     * @Route("eliminar",name="eliminar_keyword")
     */
    public function eliminar_keyword(Request $request)
    {
        $id=$request->request->get("id");

        $keyword=$this->findById("TipsBundle:Keyword",$id);
		/* return $this->json(array("status_code"=>200,"message"=>"que te peten"));
		$logger = $this->get('logger');
		$logger->info("********************************");
		$logger->info($id);
		 */
        if($keyword)
        {
            try 
            {
               $this->borrar_entity($keyword);
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
            $mensaje="Este keyword no existe";
            $status_code=404;
        }
        return $this->json(array("status_code"=>$status_code,"message"=>$mensaje));
        
    }    

    private function gestionar_keyword(Request $request,$id=null)
    {
		$accion=$id==null?"nuevo":"editar";

    	if($id==null)
    		$keyword= new Keyword();
    	else    	
    		$keyword= $this->findById("TipsBundle:Keyword",$id);
    		
    	

    	if($keyword==null)
    	{
		    $this->addFlash("success","La keyword no existe");
        	return $this->redirect($this->generateUrl("listado_keywords"));
    	}

    	$form 	= $this->createForm(KeywordForm::class,$keyword);

    	if($id!=null)
            $form->add("delete",SubmitType::class, array("label"=>"Eliminar keyword"));

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
        		return $this->redirect($this->generateUrl("listado_keywords"));
        	}
        }
        
     	$params["form"] =   $form->createView();

    	return $this->render('TipsBundle:keywords:form.html.twig',$params);

    }
}
