<?php

namespace ComentariosBundle\Controller;
use AppBundle\Controller\BaseController;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use ComentariosBundle\Entity\Comentario;
use ComentariosBundle\Form\ComentarioForm;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DefaultController extends BaseController
{
    /**
     * @Route("",name="listado_comentarios")
     */
    public function listado()
    {
        $params["comentarios"]=$this->findByField("ComentariosBundle:Comentario",array(),array("creado"=>"DESC"));
        
        return $this->render('ComentariosBundle:comentarios:listado.html.twig',$params);
    }

    /**
     * @Route("ver/{id}",name="ver_comentario")
     */
    public function ver($id)
    {
        $params["comentario"]=$this->findById("ComentariosBundle:Comentario",$id);
        
        return $this->render('ComentariosBundle:comentarios:ficha.html.twig',$params);
    }

    /**
     * @Route("validar",name="validar_comentario")
     */
    public function validar(Request $request)
    {
        $id=$request->request->get("id");
        $comentario=$this->findById("ComentariosBundle:Comentario",$id);

        if($comentario!=null)
        {
            $comentario->setPermitido(!$comentario->getPermitido());

            $this->editar_entity($comentario);

            return new JsonResponse(array("status_code"=>200,"estado"=>$comentario->getPermitido()?"cancel":"done"));
        }
        else
            return new JsonResponse(array("status_code"=>404,"message"=>"No se puede encontrar"));
            

        
        
    }

    /**
     * @Route("eliminar",name="eliminar_comentario")
     */
    public function eliminar(Request $request)
    {
        $id=$request->request->get("id");

        $keyword=$this->findById("ComentariosBundle:Comentario",$id);        

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
            $mensaje="Este comentario no existe";
            $status_code=404;
        }
        return $this->json(array("status_code"=>$status_code,"message"=>$mensaje));
        
    }

}
