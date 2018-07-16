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
		try 
		{					
	        $message = \Swift_Message::newInstance();
	        $logo=$message->embed(\Swift_Image::fromPath(__DIR__."/../../../web/img/logo.png"));
	        
	        $message->setSubject($params["subject"])
	        ->setFrom("social@ajusa.es")
	        ->setTo($params["to"])
	        ->setBody(                  
	            $this->templating->render(                    
	                $params["template"],
	                array("email"=>$params["to"],"logo"=>$logo,"title"=>"CAMBIAR")
	            ),
	            'text/html'
	        );

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
		catch (Exception $e) 
		{
			return 0;
		}		
	}


	}