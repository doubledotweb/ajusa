<?php 

namespace TipsBundle\Form;

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


class TipForm extends AbstractType
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
        return 'tip';        
    }



    private function form()
    {

        
        $form = array(
            array("id",HiddenType::class, array("label"=>false,"mapped"=>false)),

            array("idioma",ChoiceType::class,array("label"=>"Idioma","required"=>true,"choices"=>array("Español"=>"es","Inglés"=>"en"),"multiple"=>false,"expanded"=>true)),

            array("titulo",TextType::class,array("label"=>"Título","required"=>true,"attr"=>array("maxlength"=>255))),

            array("visible",CheckboxType::class,array("label"=>"Visible","required"=>true,)),

            array("destacado",CheckboxType::class,array("label"=>"Destacado","required"=>true,)),

            array("categoria",ChoiceType::class,array("label"=>"Tipo","required"=>true,
                "choices"=>array(
                    "Actualidad"=>"actualidad",
                    "Catálogo" => "catalogo",
                    "Ferias"=>"ferias",
                    "Energías alternativas"=>"energias-alternativas",
                    "Vídeos"=>"videos",
                    "Informes técnicos"=>"informes-tecnicos",
                    "Webinarios"=>"webinarios",
                    "Otros"=>"otros",
                ))),

            
            array("archivo_aux",FileType::class,array("label"=>"Archivo","required"=>true,)),            
            
            array("submit",SubmitType::class, array("label"=>"Guardar")),
            );

        return $form;
    }
}


?>