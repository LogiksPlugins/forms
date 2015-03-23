<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//All Commonly Needed Funtions From Interface
if(!function_exists("getUserSelector")) {
	function getUserSelector($where="") {
		$arr=getUserList(null,$where);
		$users="";
		foreach($arr as $a=>$b) {
			$userid=$b["userid"];
			$name=$b["name"];
			$users.="<option value='$userid'>$name [$userid]</option>";
		}
		return $users;
	}
}

if(!function_exists("loadLoadableFormPlugins")) {
	function loadLoadableFormPlugins($plugins=null) {
		$webPath=getWebPath(__FILE__);
		$rootPath=getRootPath(__FILE__);
		
		if($plugins==null) {
			$plugins=getSiteSettings("Loadable Form Plugins","*","Forms","scandir",str_replace(ROOT,"",dirname(__FILE__))."/plugins/#php",'multiple');
		}
		if($plugins=="*") {
			$fs=scandir(dirname(__FILE__)."/plugins/");
			unset($fs[0]);unset($fs[1]);
			$out=array();
			foreach($fs as $a) {
				//$a=str_replace(".php","",$a);
				if(strpos($a,"~")!==0) array_push($out,$a);
			}
			$plugins=$out;
		} elseif(!is_array($plugins)) {
			$plugins=explode(",",$plugins);
		}
		
		foreach($plugins as $a) {
			$a=trim($a);
			$f=dirname(__FILE__)."/plugins/$a";
			if(file_exists($f)) include $f;
		}
	}
	function printFormNotFound($msg="Please contact webmaster for further information.") {
		echo "<div class='noFormFound ui-widget-content ui-corner-all'>
				<h1 class='ui-widget-header'>Form Was Not Found</h1>
				<div class='noFormIcon'></div>
				<h3>$msg</h3>
			  </div>";
	}
}
?>
