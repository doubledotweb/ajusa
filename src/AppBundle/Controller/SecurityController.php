<?php


namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController extends Controller
{
    
    /**
     * @Route("/manager/login", name="login_admin")     
     */
    public function loginUserAction(Request $request)
    {
        
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return $this->redirect("/manager");
        else
        {
            
            if($_SERVER["REQUEST_URI"]=="/manager/login")
                return $this->render('layout/login.html.twig', array('last_username' => $lastUsername,'error'=> $error,));
            else        
                return $this->render('front/profesional/login.html.php', array('last_username' => $lastUsername,'error'=> $error,));
            

        }
    }

    /**
     * @Route("/manager/login_check", name="login_check_admin")
     * @Route("/login_check", name="login_check")
     */
    public function LoginChecking(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', array(
            'last_username' => "df",
            'error'         => $error,
        ));
    }

    /**
     * @Route("/manager/logout", name="salir_admin")     
     * @Route("/logout", name="salir")     
     */
    public function logoutUserAction(Request $request)
    {                    
        $session = $request->getSession();

        // get the login error if there is one
        $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);        
    }
    

}