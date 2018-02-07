<?php 

namespace AdminBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class RenovarForm extends AbstractType
{
    
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$form=$this->renovar_form();

		foreach ($form as $field) 
		{
			$builder->add($field[0],$field[1],$field[2]);
		}
	}


	public function getName()
    {
        return 'Contraseña Olvidada';        
    }

    private function renovar_form()
	{	
		
		$form = array(
			array("email",TextType::class,array("label"=>false,"attr"=>array("required"=>true,"id" => "email","class" => "datos","placeholder"=>"Escribe tu email"))),			
			
			array("submit",SubmitType::class, array("label"=>"Solicitar")),);

		return $form;

	}
}


?>