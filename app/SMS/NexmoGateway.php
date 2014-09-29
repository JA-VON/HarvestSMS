<?php 

namespace SMS;

class NexmoGateway implements SMSGateway {
	
	protected $messageService;

	public function __construct()
	{
		$this->messageService = new NexmoMessage(Config::get('Nexmo.NexmoID'), Config::get('Nexmo.NexmoSecret'));
	}

	public function hasNewText($data)
	{
		return $this->messageService->inboundText($data);
	}

	public function getTextMessage()
	{
		return $this->messageService->text; 
	}

	public function sendText($data,$name,$message)
	{
		$this->messageService->sendText($data,$name,$message); 
	}

	public function displayOverView($data)
	{
		$this->messageService->displayOverView($data); 
	}

	public function reply($message)
	{
		$this->messageService->reply($message);
	}
}