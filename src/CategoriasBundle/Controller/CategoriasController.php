<?php

namespace CategoriasBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Controller\BaseController;

use CategoriasBundle\Entity\Categoria;
use CategoriasBundle\Form\CategoriaForm;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Doctrine\ORM\Query\ResultSetMapping;

class CategoriasController extends BaseController
{
    /**
     * @Route("",name="listado_categorias")
     
     */
    public function indexAction()
    {   
        $params["categorias"] = $this->findByField("CategoriasBundle:Categoria");

        return $this->render('CategoriasBundle:Default:categorias.html.twig',$params);
    }

    /**
     * @Route("anadir",name="anadir_categoria")
     
     */
    public function anadir(Request $request)
    {
        $translator=$this->get("translator");
        $categoria= new Categoria();         

    	$form 	= $this->createForm(CategoriaForm::class,$categoria);

        $form	->  handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) 
        {
            $redirect=true;

        	try
        	{
                $categoria=$this->generar_slug($form->getData());
                $this->insertar_entity($categoria);
                $this->addFlash("success",$translator->trans("Creado Correctamente"));
        	}
        	catch(\Exception $e)
            {             
                $redirect=false;      
                $this->addFlash("error",$this->mensaje_error($e));
            }    	 	
           	if($redirect)
                return $this->redirect($this->generateUrl("listado_categorias"));

        }
        
     	$params["form"] =   $form->createView();
        return $this->render('CategoriasBundle:Form:categoria.html.twig',$params);
    }

    /**
     * @Route("editar/{id}",name="editar_categoria")
     
     */
    public function editar(Request $request,$id)
    {
        $translator=$this->get("translator");

        $categoria = $this->findById("CategoriasBundle:Categoria",
            $id);

        $form   = $this->createForm(CategoriaForm::class,$categoria);
        
        $form   ->  add("delete",SubmitType::class,array("label"=>"Eliminar"));

        $form   ->  handleRequest($request);

        $params["imagen"]=$categoria->getImagen();
        
        if($form->isSubmitted() && $form->isValid()) 
        {
            $redirect=true;
            
            switch ($form->getClickedButton()->getName()) 
            {
                case 'submit':
                   try
                    {
                        $categoria=$this->generar_slug($form->getData());
                        $categoria->actualizar_imagen(); 
                        $this->insertar_entity($categoria);
                        $this->addFlash("success",$translator->trans("Guardado Correctamente"));
                    }
                    catch(\Exception $e)
                    {             
                        $redirect=false;                        
                        $this->addFlash("error",$this->mensaje_error($e));
                        
                    }
                break;
               
                case "delete":
                    
                       if($this->noticias_categoria($categoria)==0)
                        {

                            try 
                            {
                                $this->borrar_entity($categoria);
                                $this->addFlash("success",$translator->trans("Eliminado Correctamente"));
                            } catch (Exception $e) {
                                $this->addFlash("error",$translator->trans("Algo ha ido mal"));
                                $redirect=false;
                            }

                        }
                        else
                        {
                            $this->addFlash("error",$translator->trans("No se puede borrar esta categoria, aun contiene noticias"));
                            $redirect=false;
                        }                    
                break;
            }           
            if($redirect)
                return $this->redirect($this->generateUrl("listado_categorias"));

        }
        
        $params["form"] =   $form->createView();
        return $this->render('CategoriasBundle:Form:categoria.html.twig',$params);
    }

    /**
     * @Route("borrar/{id}",name="eliminar_categoria")
     
     */
    public function borrar(Request $request,$id)
    {
        $translator=$this->get("translator");

        $categoria = $this->findById("CategoriasBundle:Categoria",$id);
        if($categoria!=null)
        {

            if($this->noticias_categoria($categoria)==0)
            {

                try 
                {
                    $this->borrar_entity($categoria);
                } catch (Exception $e) {
                    return new JsonResponse(array("status"=>500,"message"=>$translator->trans("Algo ha ido mal")));            
                }

                return new JsonResponse(array("status"=>200,"message"=>$translator->trans("Eliminado Correctamente")));
            }
            else
                return new JsonResponse(array("status"=>500,"message"=>$translator->trans("No se puede borrar esta categoria, aun contiene noticias")));

        }
        else
            return new JsonResponse(array("status"=>404,"message"=>$translator->trans("Esta categoría no existe")));
        
    }


    private function generar_slug($categoria)
    {
    	$aux=array();
    	$em=$this->getDoctrine()->getManager();

    	foreach ($categoria->getNombre() as $lang => $value) 
    	{
    		$aux[$lang]=$this->container->get("normalizator")->calculate_slug($value);

            
    		$result = $em->createQuery("SELECT c FROM CategoriasBundle:Categoria c where c.slug like :nombre")->setParameter("nombre","%".$aux[$lang]."%")->getResult();
            
    		if(count($result)>0)
            {
               $aux[$lang].="-".count($result); 
            }
    	}
        
        $categoria->setSlug($aux);

        return $categoria;
    }    
}
