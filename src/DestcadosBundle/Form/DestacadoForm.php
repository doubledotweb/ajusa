<?php 

namespace DestcadosBundle\Form;

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
use Symfony\Component\Form\Extension\Core\Type\UrlType;


class DestacadoForm extends AbstractType
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
        return 'destacado';        
    }



    private function form()
    {

        
        $form = array(
            array("id",HiddenType::class, array("label"=>false,"mapped"=>false)),

            array("idioma",ChoiceType::class,array("label"=>"Idioma","required"=>true,"choices"=>array("Español"=>"es","Inglés"=>"en"),"multiple"=>false,"expanded"=>true)),

            array("titulo",TextType::class,array("label"=>"Título","required"=>true,"attr"=>array("maxlength"=>255))),

            array("visible",CheckboxType::class,array("label"=>"Visible","required"=>true,)),

            array("tipo",ChoiceType::class,array("label"=>"Tipo","required"=>true,
                "choices"=>array(
                    "Actualidad"=>"actualidad",
                    "Catálogo" => "catalogo",
                    "Ferias"=>"ferias",
                    "Historia" => "historia",
                    "Energías alternativas"=>"energias-alternativas",
                    "Vídeos"=>"videos",
                    "Informes técnicos"=>"informes-tecnicos",
                    "Webinarios"=>"webinarios",
                    "Otros"=>"otros",
                ))),

            
            array("imagen",ChoiceType::class,array("label"=>"Imagen","required"=>true,"choices"=>array(1,2),"multiple"=>false,"expanded"=>true)),


            array("resumen",TextType::class,array("label"=>"Resumen","required"=>false,"attr"=>array("maxlength"=>500))),                 


            array("texto_enlace",TextType::class,array("label"=>"Texto enlace","required"=>false,"attr"=>array("maxlength"=>100))),

            array("enlace",UrlType::class,array("label"=>"Enlace","required"=>false,"attr"=>array("maxlength"=>300))),

            
            array("submit",SubmitType::class, array("label"=>"Guardar")),
            );

        return $form;
    }
}


?>