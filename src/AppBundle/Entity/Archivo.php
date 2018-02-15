<?php 

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;


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
               $nombre=$this->sanear_nombre($file->getClientOriginalName());
               
               $nombre=$this->calcular_nombre($nombre,$carpeta);

               $file->move(".".$carpeta,$nombre);

               return $carpeta.$nombre;
          }
       
    }

    protected function subir_base64($carpeta,$base64_string)
    {
      $temp_file = tempnam(sys_get_temp_dir(), 'aj');
      

      $base64_string=explode("+-+", $base64_string);
      
      $nombre= $this->sanear_nombre($base64_string[0]);
      

      $nombre= $this->calcular_nombre($nombre,$carpeta);
      
      $base64_string=explode(",", $base64_string[1]);

      

      $base64_string=base64_decode($base64_string[1]);

      file_put_contents($temp_file,$base64_string);

      $file=new File($temp_file);

      $file->move(".".$carpeta,$nombre);

      return $carpeta.$nombre;

    }

    private function calcular_nombre($nombre,$carpeta)
    {
      $format=substr($nombre,strrpos($nombre, ".")+1);
      $nombre=substr($nombre,0,strrpos($nombre, "."));
      
      $files=glob(".".$carpeta.$nombre."*.".$format);


      $total=count($files);
      if($total)
        $nombre=$nombre."_".$total.".".$format;
      else
        $nombre=$nombre.".".$format;

      return $nombre;
    }

    private function sanear_nombre($nombre)
    {
      return str_replace(" ","_", $nombre);
    }


}
