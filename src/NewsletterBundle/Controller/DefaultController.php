<?php

namespace NewsletterBundle\Controller;

use AppBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use NewsletterBundle\Form\NewsletterForm;

use NewsletterBundle\Entity\Newsletter;

class DefaultController extends Controller
{
    /**
     * @Route("",name="ficheros_newsletter")
     */
    public function indexAction()
    {
        $em=$this->getDoctrine()->getManager();

    	$results= $em->createQuery(
    		"SELECT n.nombre FROM NewsletterBundle:Newsletter n"
    		)->execute();    	

        $params["newsletters"] = $results;
        
        return $this->render('NewsletterBundle:Default:index.html.twig',$params);
    }

    /**
     * @Route("anadir",name="anadir_fichero")
     
     */
    public function anadir(Request $request)
    {
        $newsletter = new Newsletter();
        $logger = $this->container->get("logger");        
        $logger->info("la virgen, es que cada epoca es igual");
        $form   = $this->createForm(NewsletterForm::class,$newsletter);
        $params["form"] =   $form->createView();
        return $this->render('NewsletterBundle:Default:newsletter.html.twig',$params);
    }


    private function gestion_newsletter($request, $id=null)
    {
        $newsletter = new Noticia();
        $accion = "nuevo";
        
        $params["form"] =   $form->createView();
        return $this->render('NoticiasBundle:Form:noticia.html.twig',$params);
    }
}
