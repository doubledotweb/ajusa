<?php 

namespace CategoriasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\URlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class CategoriaForm extends AbstractType
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
        return 'categoria';        
    }

    private function form()
	{	
		
		$form = array(				

				array(
					"nombre",
					CollectionType::class,
					array(
						"label"		=>"Nombre",
						"required"	=>true,						
						"entry_type"=>TextType::class,
						"entry_options"=>array("attr"=>array("maxlength"=>255))

					)),
				array(
					"imagen_aux",
					FileType::class,
					array(
						"label"		=>"Imagen",
						"required"	=>true,
					)),
									
				array("submit",SubmitType::class, array("label"=>"Guardar")));

		return $form;

	}
}

?>
