<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\File;



class DefaultController extends Controller
{
    


	/**
     * @Route("/resize/{width}/{height}/{slug}", name="",requirements={"slug":".+"})
     */
	public function resize(Request $request,$slug,$width=null,$height=null)
	{		
		$aux=getimagesize($slug);


		$filename=substr($slug, strrpos($slug, "/")+1);

		$file=new File($slug);

		

		$params["anch_ori"] 	=	$aux[0];
		$params["alt_ori"]	=	$aux[1];

		$params["ratio"] 	=$params["anch_ori"]/$params["alt_ori"];

		$params["ancho_nuevo"]	=	is_null($width)?$params["anch_ori"]:(integer)$width;
		$params["alto_nuevo"]		=	is_null($height)?$params["alt_ori"]:(integer)$height;


		if($params["alt_ori"]<=$params["anch_ori"])		
			$params["porcentaje"]=$params["alt_ori"]/$params["alto_nuevo"];
		else		
			$params["porcentaje"]=$params["anch_ori"]/$params["ancho_nuevo"];






		$params["ratio_nuevo"] 	= 	$params["ancho_nuevo"]/$params["alto_nuevo"];
		$params["slug"]=$slug;
		$content=$this->filter($params);
		
		

		$headers=array("Content-Type" => $file->getMimeType(),'Content-Disposition' => 'inline; filename="'.$filename.'"');
		
		return new Response($content,200,$headers);


		
	}


	private function filter($params)
	{
		//$thumb = imagecreatetruecolor($params["ancho_nuevo"], $params["alto_nuevo"]);

		switch (image_type_to_extension(exif_imagetype($params["slug"]))) 
		{
			case ".png":
				$create_from = "imagecreatefrompng";
				$render="imagepng";
				
				
			break;
			case ".jpeg":
				$create_from = "imagecreatefromjpeg";
				$render 	 =	"imagejpeg";
			break;
			
		}
		$origen=$create_from($params["slug"]);

		if($params["ratio"]==1) //imagenes cuadradas
		{
			if($params["ratio_nuevo"]>=1)
			{

				$aux = imagecreatetruecolor($params["ancho_nuevo"],floor($params["ancho_nuevo"]/$params["ratio"]));

				imagecopyresampled($aux, $origen, 0, 0, 0, 0,$params["ancho_nuevo"],floor($params["ancho_nuevo"]/$params["ratio"]), $params["anch_ori"], $params["alt_ori"]);
				$thumb=$aux;
				$thumb=imagecrop($aux,array("x"=>0,"y"=>floor(($params["ancho_nuevo"]/$params["ratio"]-$params["alto_nuevo"])/2),"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));	
			}
			else
			{
				$aux = imagecreatetruecolor($params["alto_nuevo"]*$params["ratio"], floor($params["alto_nuevo"]));

				imagecopyresampled($aux, $origen, 0, 0, 0, 0, floor($params["alto_nuevo"]*$params["ratio"]), $params["alto_nuevo"], $params["anch_ori"], $params["alt_ori"]);
				
				$thumb=imagecrop($aux,array("x"=>floor(($params["alto_nuevo"]*$params["ratio"]-$params["ancho_nuevo"])/2),"y"=>0,"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));
			}
			
		}
		elseif($params["ratio"]>1)	//imagenes horizontales
		{	
			if($params["ratio_nuevo"]==1)
			{

				$aux = imagecreatetruecolor(floor($params["alto_nuevo"]*$params["ratio"]), $params["alto_nuevo"]);

				imagecopyresampled($aux, $origen, 0, 0, 0, 0, floor($params["alto_nuevo"]*$params["ratio"]), $params["alto_nuevo"], $params["anch_ori"], $params["alt_ori"]);
				
				$thumb=imagecrop($aux,array("x"=>floor(($params["alto_nuevo"]*$params["ratio"]-$params["ancho_nuevo"])/2),"y"=>0,"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));	
			}
			elseif($params["ratio_nuevo"]<1)
			{

				$aux = imagecreatetruecolor(floor($params["alto_nuevo"]*$params["ratio"]), $params["alto_nuevo"]);

				imagecopyresampled($aux, $origen, 0, 0, 0, 0, floor($params["alto_nuevo"]*$params["ratio"]), $params["alto_nuevo"], $params["anch_ori"], $params["alt_ori"]);
				
				$thumb=imagecrop($aux,array("x"=>floor(($params["alto_nuevo"]*$params["ratio"]-$params["ancho_nuevo"])/2),"y"=>0,"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));
			}
			else
			{
				$x1=$params["ancho_nuevo"];
				$y1=floor($params["ancho_nuevo"]/$params["ratio"]);

				$x2=floor($params["alto_nuevo"]*$params["ratio"]);

				$y2=$params["alto_nuevo"];

				if($params["ancho_nuevo"]<=$x1 && $params["alto_nuevo"]<=$y1)
				{
					$x=$x1;
					$y=$y1;

					$cor_x=0;

					$cor_y=floor(($y1-$params["alto_nuevo"])/2);


				}
				else
				{
					$x=$x2;
					$y=$y2;

					$cor_x=floor(($x2-$params["ancho_nuevo"])/2);

					$cor_y=0;
				}



				$aux = imagecreatetruecolor($x,$y);

				imagecopyresampled($aux, $origen, 0, 0, 0, 0, $x, $y, $params["anch_ori"], $params["alt_ori"]);
				$thumb=$aux;

				$thumb=imagecrop($aux,array("x"=>$cor_x	,"y"=>$cor_y,"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));

			}



		}
		else //imagenes verticales
		{			

			if($params["ratio_nuevo"]==1)
			{

				$aux = imagecreatetruecolor($params["ancho_nuevo"], floor($params["ancho_nuevo"]/$params["ratio"]));

				imagecopyresampled($aux, $origen, 0, 0, 0, 0, $params["ancho_nuevo"], floor($params["ancho_nuevo"]/$params["ratio"]), $params["anch_ori"], $params["alt_ori"]);
				
				$thumb=imagecrop($aux,array("x"=>0,"y"=>floor($params["alto_nuevo"]*0.25),"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));
			}
			elseif($params["ratio_nuevo"]<1)
			{

				$aux = imagecreatetruecolor(floor($params["alto_nuevo"]*$params["ratio"]), $params["alto_nuevo"]);

				imagecopyresampled($aux, $origen, 0, 0, 0, 0, floor($params["alto_nuevo"]*$params["ratio"]), $params["alto_nuevo"], $params["anch_ori"], $params["alt_ori"]);
				$thumb=$aux;
				$thumb=imagecrop($aux,array("x"=>floor(($params["alto_nuevo"]*$params["ratio"]-$params["ancho_nuevo"])/2),"y"=>0,"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));
			}
			else
			{	

				$aux = imagecreatetruecolor($params["ancho_nuevo"], floor($params["ancho_nuevo"]/$params["ratio"]));

				imagecopyresampled($aux, $origen, 0, 0, 0, 0, $params["ancho_nuevo"], floor($params["ancho_nuevo"]/$params["ratio"]), $params["anch_ori"], $params["alt_ori"]);
				
				$thumb=imagecrop($aux,array("x"=>0,"y"=>floor(($params["ancho_nuevo"]/$params["ratio"]-$params["alto_nuevo"])/2),"width"=>$params["ancho_nuevo"],"height"=>$params["alto_nuevo"]));
			}
		}	

		ob_start();
			$render($thumb);
		$aux=ob_get_clean();

		return $aux;
	}

	private function escalar($origen,$thumb,$params)
	{

		if($params["ratio"]==$params["ratio_nuevo"])
		{
			imagecopyresampled ($thumb, $origen, 0, 0,0,0, $params["ancho_nuevo"]/$params["porcentaje"] , $params["alto_nuevo"]/$params["porcentaje"] , $params["anch_ori"],$params["alt_ori"]);
		}
		else
		{
			imagecopyresampled ($thumb, $origen, 0, 0,0,0, $params["ancho_nuevo"]/$params["porcentaje"] ,$params["alto_nuevo"]/$params["porcentaje"], $params["anch_ori"],$params["alt_ori"]);
		}

		return $thumb;
	}

}