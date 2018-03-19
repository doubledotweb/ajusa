<?php 

namespace AdminBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $form=$this->form();
        foreach ($form as $field) 
        {
        	$builder->add($field[0],$field[1],$field[2]);
        }
    }

    public function getName()
    {
        return 'Usuario';        
    }



    private function form()
    {

        
        $form = array(
            array("id",HiddenType::class, array("label"=>false,"mapped"=>false)),

            array("nombre",TextType::class,array("label"=>"Nombre","attr"=>array("required"=>true,"id" => "nombre",))),

            array("departamento",TextType::class,array("label"=>"Departamento","attr"=>array("required"=>true,"id" => "nombre",))),
            
            array("apellido",TextType::class,array("label"=>"Apellido","attr"=>array("required"=>true,"id" => "apellido",))),

            array("username",TextType::class,array("label"=>"Email","attr"=>array("required"=>true,"id" => "email",))),                   

            array("password",TextType::class,array("label"=>"Contraseña","mapped"=>false,"attr"=>array("id" => "pass",))),

            array("isActive",CheckboxType::class,array("label"=>"Usuario Activo","required"=>false,"attr"=>array("id" => "active",))),

            
            array("submit",SubmitType::class, array("label"=>"Guardar")),
            );

        return $form;
    }
}


?>