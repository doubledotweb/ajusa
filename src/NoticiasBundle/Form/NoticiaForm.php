<?php 

namespace NoticiasBundle\Form;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\URlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use CategoriasBundle\Entity\Categoria;

use Doctrine\ORM\EntityRepository;


class NoticiaForm extends AbstractType
{
    
    private $em;

    private $security;

    public function __construct(EntityManagerInterface $em,$security)
    {
        $this->em = $em;
        $this->security=$security;
    }
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
        return 'noticia';        
    }

    private function form()
	{	

		$form = array(

				array("fecha_publicacion",DateType::class,array("label"=>"Fecha 	publicación","required"=>true,"widget"=>"single_text")),

				array("visible",CheckboxType::class,array("label"=>"Visible")),
				array("destacado",CheckboxType::class,array("label"=>"Destacado")),

				array(
					"categoria",EntityType::class,
					array(
						"label"		=>"Categorias",						
						"class"		=>Categoria::class,
						"query_builder"=> function (EntityRepository $er) {
					        return $er->createQueryBuilder('c')
					            ->orderBy('c.nombre', 'ASC');
					    },
    
						"choice_label"=>function($categorias)
						{
							return $categorias->getNombre()["es"];
						}
					)),

				array(
					"idioma",
					CollectionType::class,
					array(
						"label"		=>"Nombre",
						"required"	=>true,
						"entry_type"=>CheckboxType::class,
						

					)),


				array(
					"titulo",
					CollectionType::class,
					array(
						"label"		=>"Título",
						"required"	=>true,						
						"entry_type"=>TextType::class,
						"entry_options"=>array("attr"=>array("maxlength"=>255))

					)),

				array(
					"entradilla",
					CollectionType::class,
					array(
						"label"		=>"Entradilla",
						"required"	=>true,						
						"entry_type"=>TextareaType::class,						

					)),

				array(
					"imagen_aux",
					CollectionType::class,
					array(
						"label"		=>"Imagen principal",
						"required"	=>false,						
						"entry_type"=>FileType::class,						

					)),
				array(
					"descargable_aux",
					CollectionType::class,
					array(
						"label"		=>"Descargable",
						"required"	=>false,						
						"entry_type"=>FileType::class,
					)),


									
				array("submit",SubmitType::class, array("label"=>"Guardar")));

		return $form;

	}
}

?>
