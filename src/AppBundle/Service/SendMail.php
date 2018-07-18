<?php 

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;

class SendMail
{

	private $templating;
	private $mailer;

	public function __construct($mailer,$templating)
	{
		$this->templating=$templating;
		$this->mailer=$mailer;
	}


	public function send($params)
	{
		/* try 
		{ */					
	        $message = \Swift_Message::newInstance();
	       // $logo=$message->embed(\Swift_Image::fromPath(__DIR__."/../../../web/img/logo.png"));
	        
	        $message->setSubject($params["subject"])
	        ->setFrom("web@corporacionhms.com")
	        ->setTo($params["to"])
	        ->setBody( $params["datos"]                 
	            /* $this->templating->render(                    
	                $params["template"],
	                array("consulta"=>$params["datos"],"title"=>"CAMBIAR")
	            ),
				'text/html' */
				
			)
			->setContentType("text/html");

	        if (isset($params["files"]) && count($params["files"])) 
	        {
	        	foreach ($params["files"] as $key => $file) 
	        	{
	        	
	        		$attach=new \Swift_Attachment();

	        		$attach->setFilename($file->getClientOriginalName());
	        		$attach->setContentType($file->getMimeType());
	        		$attach->setBody(file_get_contents($file));

	        		$message->attach($attach);        		
	        		

	        	}
	        }
	            
			return $this->mailer->send($message);

	}


}