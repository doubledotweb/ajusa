<?php 


namespace AppBundle\Service;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Acceso implements ContainerAwareInterface,AuthenticationSuccessHandlerInterface
{
	use ContainerAwareTrait;
	
    function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        
        if($token->getUser()->getUsername()!="doubledot")
        {
        	$token->getUser()->updateLastLogin();

        	$this->container->get("doctrine")->getManager()->flush();
        }

        return new RedirectResponse("/manager");
    }
}