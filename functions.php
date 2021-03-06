<?php

/** Prepare timestamp for MySQL insertion */
function mydate($timestamp=0) {
	if(empty($timestamp)) { $timestamp = time(); }
	if(!is_numeric($timestamp)) { $timestamp = strtotime($timestamp); }
	return date("Y-m-d H:i:s",$timestamp);
}

/** Prepare timestamp for nice display */
function nicedate($timestamp=0) {
	if(empty($timestamp)) { $timestamp = time(); }
	if(!is_numeric($timestamp)) { $timestamp = strtotime($timestamp); }
	return date("l jS \of F Y H:i:s",$timestamp);
}

/** HTML escape content */
function h($text) {
	return htmlspecialchars($text);
}

function clearpecialchar($string) {
	$invalid_characters = array("$", "%", "#", "<", ">", "|");
	$string = str_replace($invalid_characters, "", $string);
   	return $string; // Removes special chars.
}
?>
