<?php

	class AuthHelper {

		/** Construct a new Auth helper */
		public function __construct($controller) {
			$this->controller = $controller;
		}

		/** Attempt to resume a previously logged in session if one exists */
		public function resume() {
			$f3=Base::instance();				

			//Ignore if already running session	
			if($f3->exists('SESSION.user.id')) return;

			//Log user back in from cookie
			if($f3->exists('COOKIE.RobPress_User')) {
				$cookie = $f3->get('COOKIE.RobPress_User');
				$user = $results = $this->controller->Model->Users->fetch(array('resume' => $cookie));
				if($user){
					$user = $user->cast();
					$this->forceLogin($user);
				} else{
					$f3->clear('COOKIE.RobPress_User');
					StatusMessage::add('<b>Meddled Cookie</b></br>Please delete your cookies and try to login again','danger');
					$f3->error(403);
				}
			}
		}		

		/** Look up user by username and password and log them in */
		public function login($username,$password) {
			$f3=Base::instance();		
			$crypt=\Bcrypt::instance();				
			$db = $this->controller->db;
			$results = $this->controller->Model->Users->fetch(array('username' => $username));
			$result = (!empty($results) ? $results->cast() : ''); //cast only if not empty
			if (!empty($results) && $crypt->verify($password, $results['password'])) {	//check against empty once more and verify hashed password against db has using the very method from bcrypt 
				$user = $results;	
				$this->setupSession($user);
				return $this->forceLogin($user);
			} 
			return false;
		}

		/** Log user out of system */
		public function logout() {
			$f3=Base::instance();							

			//Kill the session
			session_destroy();

			//Kill the cookie
			setcookie('RobPress_User','',time()-3600,'/');
		}

		/** Set up the session for the current user */
		public function setupSession($user) {
			//Remove previous session
			session_destroy();

			$crypt=\Bcrypt::instance();
			$hash = $crypt->hash(uniqid(rand(),true));
			//Setup new session
			session_id($hash); //more secure hash of session instead of md5

			//Setup cookie for storing user details and for relogging in
			$user->resume = $hash;
			$user->save();	
			setcookie('RobPress_User', $hash ,time()+3600*24*30,'/');


			//And begin!
			new Session();
		}

		/** Not used anywhere in the code, for debugging only */
		public function specialLogin($username) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3 = Base::instance();
			$user = $this->controller->Model->Users->fetch(array('username' => $username));
			$array = $user->cast();
			return $this->forceLogin($array);
		}

		/** Force a user to log in and set up their details */
		public function forceLogin($user) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3=Base::instance();						
			$f3->set('SESSION.user',$user);
			return $user;
		}

		/** Get information about the current user */
		public function user($element=null) {
			$f3=Base::instance();
			if(!$f3->exists('SESSION.user')) { return false; }
			if(empty($element)) { return $f3->get('SESSION.user'); }
			else { return $f3->get('SESSION.user.'.$element); }
		}

	}

?>
