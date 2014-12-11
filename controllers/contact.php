<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			StatusMessage::add(filter_var($from, FILTER_VALIDATE_EMAIL));
			if(filter_var($from, FILTER_VALIDATE_EMAIL)){
				$from = "From: $from";
				mail('rob_the_ubicorn@totallyawesomewebsdevelpment.com',$subject,$message,$from); //manually input to field to avoid  mail form spam bot exploit
				StatusMessage::add('Thank you for contacting us');
				return $f3->reroute('/');
			} else{
				StatusMessage::add('Invalid email please try again', 'danger');
				return $f3->reroute('/contact');
			}			
		}	
	}

}

?>
