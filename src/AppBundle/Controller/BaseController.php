<?php


namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


abstract class BaseController extends Controller
{


	protected function insertar_entity($entity)
    {
        $em =   $this->getDoctrine()->getManager();
        $em ->  persist($entity);
        $em ->  flush();
        $em ->  close();

        return $entity;
    }

    protected function editar_entity($entity)
    {
        $em   =   $this->getDoctrine()->getManager();
       
        $em ->persist($entity);        
        $em ->flush();
        $em ->close();

        return $entity;
    }

    protected function borrar_entity($entity)
    {
        $em =   $this->getDoctrine()->getManager();
        $em ->  remove($entity);
        $em ->  flush();
        $em ->  close();
    }

    protected function findById($entity,$id)
    {
        return $this->getDoctrine()->getManager()->getRepository($entity)->find($id);
    }

    protected function findByField($entity,$condition=array(),$order=array())
    {
        return $this->getDoctrine()->getManager()->getRepository($entity)->findBy($condition,$order);
    }

    protected function getCurrentUser()
    {
        return $this->container->get('security.token_storage')->getToken()->getUser();         
    }

    protected function query($query,$conditions=array())
    {
        $con=$this->getDoctrine()->getConnection();

        $stmt=$con->prepare($query);

        if(count($conditions)>0)
        {
            foreach ($conditions as $key => $condition) 
            {
                $stmt->bindValue($key,$condition);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    protected function mensaje_error($exception)
    {
        
        $logger=$this->container->get("logger");
        $logger->info($exception->getMessage());                    
        $logger->err($exception->getMessage());

        if(method_exists($exception, "getPrevious") && $exception->getPrevious() instanceof \PDOException)
        {

            switch ($exception->getPrevious()->getErrorCode()) 
            {
                case '1062':
                    return "Este email ya existe";
                break;
                
                default:
                    return "Ha ocurrido un error";
                break;
            }
        }

        //dump($exception,$exception->getCode(),get_class($exception), is_subclass_of($exception,"Exception"),$exception INSTANCEOF FileException);

        if($exception instanceof FileException)
        {
            switch ($exception->getCode()) 
            {
                case '0':
                    return "Permisos insuficientes";
                break;
                
                default:
                    return "Ha ocurrido un error";
                break;
            }
        }


        return "Ha ocurrido un error";
    }
}

