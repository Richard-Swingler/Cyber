<?php

class File {

	public static function Upload($array) {
		$f3 = Base::instance();
		extract($array);

		//array of valid types
		$valid_types = array(
		    "image/gif",
		    "image/png",
		    "image/jpeg",
		);
		$extention = \Web::instance()->mime($name); //gets extention from f3 function mime() that actually checks teh extention not the MIME type but returns matching mime string

		if (isset($array) && isset($tmp_name) && !empty($tmp_name) &&
			@getimagesize($tmp_name) !== false &&  //ensure contents is actually an image
			in_array($type, $valid_types) &&  //checks extention subimitted (f3 functions says it checks mime type but actually checks extention and returns mime type associated)
			in_array($extention, $valid_types)){//checks mime type submitted
			$directory = getcwd() . '/uploads';
			while (true) {
				 $name = uniqid(rand(), true).'.'.preg_replace('/^.+[\\\\\\/]/', '', $extention); //generates theoratically unique file name using the extension (already validated) + uniqid()
				 if (!file_exists($directory.$name)) break; //breaks loop if name is unique, this is in the unlikely event the randomly generated name is not unique
			}		
			$destination = $directory . '/' . $name;
			$webdest = '/uploads/' . $name;
			if (move_uploaded_file($tmp_name,$destination)) {
				chmod($destination, 0644);
				return $webdest;
			} else {
				return false;
			}
		}
		else{
			return false;
		}
	}
}

?>
