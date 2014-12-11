<?php

class Page extends Controller {

	function display($f3) {

		$pagename = urldecode($f3->get('PARAMS.3'));
		$page = $this->Model->Pages->fetch($pagename); //returns false if page is not found or string if it is
		if(is_string($page)){ //tried to check bool would not work instead checks if it is string, this prevents pages such as page/display/<marquee>text</marquee> from outputting
			$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",$pagename)));
			$f3->set('pagetitle',$pagetitle);
			$f3->set('page',$page);
		} else{
			$f3->error(404); //redirects to 404
		}
		
	}

}

?>
