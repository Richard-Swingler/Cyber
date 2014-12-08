<?php
class User extends Controller {
	
	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($userid);

		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {
		if (!empty($_SESSION['user'])){
			$f3->reroute('/');
		}
		if($this->request->is('post')) {
			extract($this->request->data);
			$check = $this->Model->Users->fetch(array('username' => $username));
			if (!empty($check)) {
				StatusMessage::add('User already exists','danger');
			} else if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
			} else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				StatusMessage::add('Invalid email');
			} else if($captcha == $_SESSION['captcha_code']) {
				$user = $this->Model->Users;
				$user->copyfrom('POST');
				$user->password = sha1($user->password);
				$user->created = mydate();
				$user->bio = '';
				$user->level = 1;
				if(empty($displayname)) {
					$user->displayname = $user->username;
				}
				$user->save();	
				StatusMessage::add('Registration complete','success');
				return $f3->reroute('/user/login');
			}else{
				StatusMessage::add('Captcha is incorrect','danger');
			}		
		}
	}

	public function login($f3) {
		if (!empty($_SESSION['user'])){
			$f3->reroute('/');
		}
		if ($this->request->is('post')) {
			list($username,$password) = array($this->request->data['username'],$this->request->data['password']);
			if ($this->Auth->login($username,$password)) {
				StatusMessage::add('Logged in succesfully','success');
				//resets attemps variable if successful
				$_SESSION['attempts'] = 0;
				if(isset($_GET['from'])) {
					$f3->reroute($_GET['from']);
				} else {
					$f3->reroute('/');	
				}
			} else {
				//increases attemps variable for each failed attempts
				$_SESSION['attempts']++;
				StatusMessage::add('Invalid username or password','danger');
			}
		}		
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');	
	}


	public function profile($f3) {	
		$id = $this->Auth->user('id');
		extract($this->request->data);
		$u = $this->Model->Users->fetch($id);
		$oldpass = $u->password;
		if($this->request->is('post')) {
			$u->copyfrom('POST');
			if(empty($u->password)) { $u->password = $oldpass; }

			//Handle avatar upload
			$valid_mime_types = array(
			    "image/gif",
			    "image/png",
			    "image/jpeg",
			    "image/pjpeg",
			);
			$extention = \Web::instance()->mime($_FILES['avatar']['name']);
			$mime = $_FILES['avatar']['type'];

			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name']) && in_array($extention, $valid_mime_types) && in_array($mime, $valid_mime_types)) {
				$url = File::Upload($_FILES['avatar']);
				$u->avatar = $url;
				$target_path='/var/webhomes/rs14g12/linuxproj_html/blog/'.$url; //hardcoded url for now as __DIR__ returns /controller
				chmod($target_path, 0644); //set permission to -rwx-r--r-- aloows for read only no execute except for owner
								
			} else if(isset($reset)) {
				$u->avatar = '';			}
			}
			$u->save();
			\StatusMessage::add('Profile updated succesfully','success');
			return $f3->reroute('/user/profile');
		}			
		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function promote($f3) {
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetch($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}

}
?>
