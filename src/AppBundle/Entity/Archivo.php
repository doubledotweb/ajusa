<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;



/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class Archivo extends Entity
{

	

    protected function borrar($path)
    {
        if(is_file(".".$path))        
          return unlink(".".$path);
        
        return false;
    }

    protected function subir($file,$carpeta)
    {      
          if($file!=null)
          {
               $fecha=date("Y/m/d");

               $ruta=$carpeta;

               $format=$file->getClientOriginalExtension();

               $name=str_replace(".".$format, "",$file->getClientOriginalName());

               $name=str_replace(" ","_", $name);

               $files=glob(".".$ruta.$name."*.".$format);


               $total=count($files);
               if($total)
                $name=$name."_".$total.".".$format;
               else
                $name=$name.".".$format;

               $file->move(".".$ruta,$name);

               return $ruta.$name;
          }
       
    }


}
