<?php 

namespace NewsletterBundle\Form;

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


class NewsletterForm extends AbstractType
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
        return 'newsletter';        
    }

    private function form()
	{	
		
		$form = array(				

				array(
					"nombre",
					FileType::class,
					array(
						"label"		=>"Pdf",
						"required"	=>true,
					)),
									
				array("submit",SubmitType::class, array("label"=>"Guardar")));

		return $form;

	}
}

?>
