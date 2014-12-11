<?php

class Controller {

	protected $layout = 'default';

	public function __construct() {
		$f3=Base::instance();
		$this->f3 = $f3;

		// Connect to the database
		$this->db = new Database();
		$this->Model = new Model($this);

		//Load helpers
		$helpers = array('Auth');
		foreach($helpers as $helper) {
			$helperclass = $helper . "Helper";
			$this->$helper = new $helperclass($this);
		}
	}

	public function beforeRoute($f3) {
		$this->request = new Request();

		//Check user
		$this->Auth->resume();

		//Load settings
		$settings = $this->Model->Settings->fetchList(array('key','value'));
		$settings['base'] = $f3->get('BASE');
		$settings['path'] = $f3->get('PATH');
		$this->Settings = $settings;
		$f3->set('site',$settings);

		//Extract request data
		extract($this->request->data);

		//Process before route code
		if(isset($beforeCode)) {
			$f3->process($beforeCode);
		}
		//if post data is sent

		if ($this->request->is('post')){

			//xss scrub
			function scrub($i){ //uses f3 scrub to remove any harmfull tags
				$f3 = \Base::instance(); //will not accept external variable have to overide scrubbed data for content and message
			    return $f3->scrub($i);
			}
			// //for forms using request data
			$message = (isset($this->request->data['message']) ? $this->request->data['message'] : '');
			$content = (isset($this->request->data['content']) ? $this->request->data['content'] : '');
			$this->request->data = array_map("scrub", $this->request->data);
			$this->request->data['message'] = $message;
			$this->request->data['content'] = $content;

			//For forms using copy fromn
			$post = $f3->get('POST');
			$message = (isset($post['message']) ? $post['message'] : '');
			$content = (isset($post['content']) ? $post['content'] : '');
			$post = array_map("scrub", $f3->get('POST'));
			$f3->set('POST', $post);
			$f3->set('POST.message', $message);
			$f3->set('POST.content', $content);		

			//csrf check
			if ($f3->get('SESSION.token') && isset($this->request->data['token']) && $f3->get('SESSION.token') == $this->request->data['token']){
				$f3->clear('SESSION.token');
			}
			else{
				\StatusMessage::add("you're not a unicorn!! <br/><img src='http://27.media.tumblr.com/tumblr_lb9ftsiCnz1qanb21o1_500.jpg' width='15%'>",'danger');
				$f3->error(405);				
			}
		}
	}

	public function afterRoute($f3) {	
		//Set page options
		$f3->set('title',isset($this->title) ? $this->title : get_class($this));

		//Prepare default menu	
		$f3->set('menu',$this->defaultMenu());

		//Setup user
		$f3->set('user',$this->Auth->user());

		//Check for admin
		$admin = false;
		if(stripos($f3->get('PARAMS.0'),'admin') !== false) { $admin = true; }

		//Identify action
		$controller = get_class($this);
		if($f3->exists('PARAMS.action')) {
			$action = $f3->get('PARAMS.action');	
		} else {
			$action = 'index';
		}

		//Handle admin actions
		if ($admin) {
			$controller = str_ireplace("Admin\\","",$controller);
			$action = "admin_$action";
		}

		//Handle errors
		if ($controller == 'Error') {
			$action = $f3->get('ERROR.code');
		}
		//Handle custom view
		if(isset($this->action)) {
			$action = $this->action;
		}

		//Extract request data
		extract($this->request->data);

		//Generate content		
		$content = View::instance()->render("$controller/$action.htm");
		$f3->set('content',$content);

		//Process before route code
		if(isset($afterCode)) {
			$f3->process($afterCode);
		}

		//Render template
		echo View::instance()->render($this->layout . '.htm');
	}

	public function defaultMenu() {
		$menu = array(
			array('label' => 'Search', 'link' => 'blog/search'),
			array('label' => 'Contact', 'link' => 'contact'),
		);

		//Load pages
		$pages = $this->Model->Pages->fetchAll();
		foreach($pages as $pagetitle=>$page) {
			$pagename = str_ireplace(".html","",$page);
			$menu[] = array('label' => $pagetitle, 'link' => 'page/display/' . $pagename);
		}

		//Add admin menu items
		if ($this->Auth->user('level') > 1) {
			$menu[] = array('label' => 'Admin', 'link' => 'admin');
		}

		return $menu;
	}

}

?>
