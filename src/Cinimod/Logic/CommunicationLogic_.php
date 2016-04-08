<?php

namespace Mkny\Cinimod\Logic;


/**
* Classe de comunicação
* 
* sms/email
*/
class CommunicationLogic
{
	private $type;
	private $subject;
	private $arrTo = [];

	public function init($type='mail')
	{
		if(!in_array($type, array('mail', 'sms'))){
			throw new \Exception("Tipo invalido '{$type}'");
		}

		$this->type = $type;
	}

	public function addDest($destAddress, $destName=false)
	{
		if(!$destName){
			$destName = $destAddress;
		}
		// $to_types = array(
		// 	'to_unique',
		// 	'to',
		// 	'bcc',
		// 	'cc'
		// 	);

		$arrTo[$destAddress] = array(
			'addr' => $destAddress,
			'name' => $destName,
			'type' => 'to_unique'
			);
	}

	public function setSubject($value='')
	{
		$this->subject = $value;
	}

	public function send()
	{
		
	}
}



// title
// message
// [] dest
// from
// cc/bcc
// 
//  Mail::send('emails.reminder', ['user' => $user], function ($m) use ($user) {
        //     $m->from('hello@app.com', 'Your Application');

        //     $m->to($user->email, $user->name)->subject('Your Reminder!');
        // });