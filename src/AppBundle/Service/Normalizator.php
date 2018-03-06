<?php 

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;

class Normalizator
{

	


	public function calculate_slug($text)
	{
		$slug=strtolower(str_replace(" ", "-", $text));

        $rules=array(
            "á"=>"a",
            "é"=>"e",
            "í"=>"i",
            "ó"=>"o",
            "ú"=>"u",
            "Á"=>"a",
            "É"=>"e",
            "Í"=>"i",
            "Ó"=>"o",
            "Ú"=>"u",
            "´"=>"",
            "ñ"=>"n",
            "ä"=>"a",
            "ë"=>"e",
            "ï"=>"i",
            "ö"=>"o",
            "ü"=>"u",
            "Ä"=>"a",
            "Ë"=>"e",
            "Ï"=>"i",
            "Ö"=>"o",
            "Ü"=>"u",
            "."=>"",
            ","=>"",
            ":"=>"",
            "¿"=>"",
            "?"=>"",
            "¡"=>"",
            "!"=>"",
            "'"=>"",
            '"'=>"",
            "’"=>"",
            "‘"=>"",
            "%"=>"",
        );

        foreach ($rules as $key => $value) 
        {
            $slug=str_replace($key, $value, $slug);
        }
		return $slug;
	}


}