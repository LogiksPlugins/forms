<?php
function getFormEngines() {return array("html"=>"HTML","auto"=>"Auto");}
function getFormLayouts() {
	return array(
	"dataview"=>"Horizontal Data-Form",
	"datapage"=>"Vertical Form Below Data",
	"datatabs"=>"Tabbed Data-Form",
	"plain"=>"Plain Form",
	"sheet"=>"Sheet Form");
}
function getToolButtons() {
	$arr=array(
			"search"=>"Search Data",
			"printview"=>"Print DataTable",
			"exportview"=>"Export DataTable",
			"mailview"=>"EMail DataTable",			
			"new"=>"New Form",
			"delete"=>"Delete Record",
			"print"=>"Print Form",
			"mail"=>"EMail Form Content",
			"download"=>"Download Filled Form",
			"edit"=>"Edit Record In Form",
		);
	return $arr;
}
function getFormModes() {
	$arr=array("insert"=>"Insert And Update","update"=>"Update Only","insertonly"=>"Insert Only","updateinsert"=>"Update If Exists Else Insert");
	return $arr;
}
function getFormAdapters() {
	$arr=array("db"=>"Submit To Database","mail"=>"Email The Form");
	return $arr;
}
function getSubmitAction() {
	$arr=array("reload"=>"Reload Form","msg#"=>"Display A Message","goto#"=>"Goto A Link","js#"=>"JS Function");
	return $arr;
}
function getFormPluginsList() {
	$fs=scandir("plugins");
	unset($fs[0]);unset($fs[1]);
	$out=array();
	foreach($fs as $a) {
		$a=str_replace(".php","",$a);
		$t=str_replace("_"," ",$a);
		$out[$a]=$t;
	}
	return $out;
}
?>
