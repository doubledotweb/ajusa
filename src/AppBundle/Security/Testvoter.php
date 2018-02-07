<?php
namespace AppBundle\Security;



use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class Testvoter extends Voter
{
    protected function supports($attribute, $subject)
    {   
        
        return in_array($attribute, array(
            "usuarios","cerca-de-ti","categorias","notas-de-prensa","infografias","sellos-y-reconocimientos","desayunos"));        
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        
        $user = $token->getUser();        
            
        if(is_a($user,"Symfony\Component\Security\Core\User\User") and in_array("Super", $user->getRoles()))
            return true;

        

        switch($user->getRole()) 
        {
            case "Administrador":
                return true;
            break;

            case "Gestor":
                $permisos=$user->getPermisos();

                $permisos=array_flip($permisos);        

                return isset($permisos[$attribute]);
            break;

            case "Gestor Medio Ambiente":
                return $attribute==="cerca-de-ti";
            break;
        }
        
        
        
        

        throw new \LogicException('This code should not be reached!');
    }
}
