<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_REQUEST["action"])) {
	$_REQUEST["action"]="";
}
if(!isset($_REQUEST['formid'])) {
	trigger_error("Form Not Found");
}
include_once __DIR__."/api.php";

switch($_REQUEST["action"]) {
	
	default:
		trigger_error("Action Not Defined or Not Supported");
}
?>
