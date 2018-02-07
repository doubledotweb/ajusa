<?php

namespace AdminBundle\Controller;

use AppBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AdminBundle\Entity\Admin;

use AdminBundle\Forms\UserForm;
use AdminBundle\Forms\RenovarForm;  



class DefaultController extends BaseController
{
    /**
    * @Route("/manager",name="home_admin")    
    */
    public function home()
    {
            
        $params=array();

        return $this->render('AdminBundle:Default:dashboard.html.twig',$params);

    }

    /**
    * @Route("/manager/administradores",name="lista_admin")
    * @Security("is_granted('usuarios')")
    */
    public function indexAction()
    {

    	$params["administradores"]=$this->findByField("AdminBundle:Admin",array());

        return $this->render('AdminBundle:Default:index.html.twig',$params);
    }

    private function get_usuarios()
    {

    	$em=$this->getDoctrine()->getManager();

    	$results= $em->createQuery(
    		"SELECT u.id,u.nombre,u.apellido,u.username as email,u.role as tipo,u.isActive,u.last_login
    		FROM AdminBundle:Admin u"
    		)->execute();    	

    	return $results;
    }

    /**
     * @Route("/manager/administrador/anadir",name="anadir_admin"))
     * @Security("is_granted('usuarios')")
     */
    public function anadirAction(Request $request)
    {
    	
        $user= new Admin();         

    	$form 	= $this->createForm(UserForm::class,$user);

        $form	->  handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) 
        {
            $form->getData()->setPassword($form->get("password")->getData());
        	try
        	{
        	    $this->insertar_usuario($form->getData());
                $this->addFlash("success","Guardado Correctamente");  
                return $this->redirect($this->generateUrl("lista_admin"));            		
        	}
        	catch(\Exception $e)
            {                
                $this->addFlash("error",$this->mensaje_error($e));
            }    	 	
        }
        
     	$params["form"] =   $form->createView();
        return $this->render('AdminBundle:Default:form.html.twig',$params);

    }
    	

    /**
     * @Route("/manager/administrador/editar/{id}",name="editar_admin")
     * @Route("/manager/mi-perfil",name="mi_perfil")
     
     */
    public function editarAction(Request $request,$id=null)
    {
    	if($id!=null)
        {
            $this->denyAccessUnlessGranted("usuarios");
            $user= $this->findById("AdminBundle:Admin",$id);
            $params["perfil"]=false;
        }
        else
        {
           $user=$this->getCurrentUser();

           $params["perfil"]=true;
        }
        
        $form=$this->createForm(UserForm::class,$user);

        if($id==null)
        {
           $form->remove("role");
           $form->remove("permisos");
           $form->remove("isActive");
        }



        


        if($id!=null)
            $form->add("delete",SubmitType::class, array("label"=>"Eliminar Usuario"));        

    	
        $form	->  handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) 
        {
            $redirect=false;	
            
        	switch ($form->getClickedButton()->getName()) 
        	{
        		case 'submit':
    				try
		        	{
                        if($params["perfil"] and $form->get("password")->getData()!=null and $form->get("password")->getData()!="" )
                            $form->getData()->setPassword($form->get("password")->getData());
                        
                        
		        		$this->insertar_usuario($form->getData());     
		        		$this->addFlash("success","Guardado Correctamente");  
		        	}
		        	catch(\Exception $e)
		            {
                        $redirect=false;
		                $this->addFlash("error",$this->mensaje_error($e));
		            }
    			break;
        		
        		case 'delete':
                    try
                    {
                        $this->borrar_entity($form->getData());     
                        $this->addFlash("success","Eliminado Correctamente");  
                    }
                    catch(\Exception $e)
                    {
                        $redirect=false;
                        $this->addFlash("error",$this->mensaje_error($e));
                    }
    				
    			break;

                default:
                    return $this->redirect($this->generateUrl("lista_admin"));
                break;
        	}

            if($redirect)
                return $this->redirect($this->generateUrl("lista_admin"));

    	 
        }
        
    	
     	$params["form"]   =   $form->createView();
        return $this->render('AdminBundle:Default:form.html.twig',$params);

    }

    /**
     * @Route("/manager/administrador/eliminar/{id}",name="eliminar_admin")
     * @Security("is_granted('usuarios')")
     */
    public function eliminarAction(Request $request,$id)
    {
        $admin=$this->findById("AdminBundle:Admin",$id);

        if($admin)
        {
            try 
            {
        	   $this->borrar_entity($admin);
               $mensaje="Eliminado Correctamente";
               $status_code=200;
                
            } catch (Exception $e) {
                $mensaje="Algo ha ido mal";
                $status_code=500;
            }            
        }
        else
        {
            $mensaje="Este administrador no existe";
            $status_code=404;
        }
    	return $this->json(array("status_code"=>$status_code,"message"=>$mensaje));
    }
	


    /**
     * @Route("/manager/administrador/editar/{id}/renovar/pass",name="renovar_pass")
     * @Security("is_granted('usuarios')")
     */
    public function renovarAction(Request $request, $id)
    {
        
    	$user = $this->findById("AdminBundle:Admin",$id);

    	$new_pass=$user->generarPass();

        $factory=$this->container->get("security.encoder_factory");

        $enconder=$factory->getEncoder($user);

        $password=$enconder->encodePassword($new_pass,$user->getUsername());       

    	$user->setPassword($password);    	

    	try
    	{
    		$this->editar_entity($user);
    		$this->preparar_email($new_pass,$user->getUsername());
    	}
    	catch(\Exception $e)
    	{    		
    		
            $this->addFlash("error",$this->mensaje_error($e));
    		return $this->json(array("mensaje"=>$e->getMessage()));
    	}
    	return $this->json(array("mensaje" => "ok"));
    }

    private function insertar_usuario($datos)
    {
        
        $factory=$this->container->get("security.encoder_factory");
        
        $enconder=$factory->getEncoder($datos);

        $password=$enconder->encodePassword($datos->getPassword(),$datos->getUsername());
        
        $datos->setPassword($password);
        
        $this->insertar_entity($datos);
        
    }

    


    
	private function preparar_email($pass,$to)
    {
        
        $sendmail=$this->container->get("app.sendmail");

        $params["subject"]   = "[Pascual]: Se ha solicitado restablecido la contraseña";
        $params["to"]     = $to;
        $params["from"]     = "no-reply@doubledot.es";
        $params["template"] = "AdminBundle:Emails:contraseña.html.twig";
        $params["perfil"]   = "http://".$_SERVER["HTTP_HOST"]."/perfil";
        $params["datos"]["pass"]     = $pass;
        
        return $sendmail->send($params);
    }
    
    

    /**
     * @Route("/manager/renovar/pass",name="contraseña_olvidada")
     * 
     */
    public function passOlvidadaAction(Request $request)
    {

        $form = $this->createForm(RenovarForm::class,array());

        $form   ->  handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) 
        {
            

            $user=$this->findByField("AdminBundle:Admin",array("username"=>$form->getData()["email"]));
             
            if(count($user))
            { 
                $user=$user[0];

                $new_pass=$user->generarPass();


                $factory=$this->container->get("security.encoder_factory");

                $enconder=$factory->getEncoder($user);

                $password=$enconder->encodePassword($new_pass,$user->getUsername());       

                $user->setPassword($password);

                try
                {
                    $this->editar_entity($user);
                    $this->preparar_email($new_pass,$user->getUsername());
                    $this->addFlash("success","Pronto recibirá un email con su nueva contraseña");
                    return $this->redirect($this->generateUrl("login_admin"));       
                }
                catch(\Exception $e)
                {
                    $this->addFlash("error","Algo ha ido mal");                    
                }    
            }
            else
            {
                $this->addFlash("error","No existe el usuario");
            }
            


         
        } 
        
        $params["form"]   =   $form->createView();
        return $this->render('AdminBundle:Default:renovar.html.twig',$params);
    }



}   
