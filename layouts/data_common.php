<?php
if(!defined('ROOT')) exit('No direct script access allowed');
_js(array("jquery.jqGrid-locale-en","jquery.jqGrid"));
_css(array("jquery.ui.jqgrid","jquery.ui.multiselect"));

$dataSource=$GLOBALS['FRMDATA'];

ob_start();
include dirname(dirname(__FILE__)). "/css/htmlexport.css";
$css=ob_get_contents();
ob_clean();
$css=str_replace("\n","",$css);
$css=str_replace("	"," ",$css);
$css=str_replace("  "," ",$css);

$dn=$dataSource["datatable_colnames"];
$d=$dataSource["datatable_cols"];
if(strlen(trim($dn))<=0) $dn=$d;

$d=explode(",",$d);
$dn=explode(",",$dn);
$cols="[";
foreach($dn as $x) {
	$x=_ling($x);
	$x=str_replace("_"," ",trim($x));
	if(strpos($x,".")>0) {
		$x=explode(".",$x);
		$x=$x[sizeOf($x)-1];
	}
	if(strlen($x)<=3) $x=strtoupper($x);
	else $x=ucwords($x);
	$cols.="'$x',";
}
$cols.="]";

$n=0;$vCols=0;

$maxCols=getSiteSettings("Visible Column Count",8,"DataTable","int");
$idField=getSiteSettings("ID Column Width",50,"DataTable","int");

$hiddenCols=array();
$searchCols=$d;
$sortCols=$d;
$classes=array();
$alinks=array();

if(isset($dataSource['datatable_hiddenCols'])) {
	$hiddenCols=explode(",",$dataSource['datatable_hiddenCols']);
}

if(isset($dataSource['datatable_model']) && strlen($dataSource['datatable_model'])>1) {
	$modelArr=json_decode($dataSource["datatable_model"],true);
	if($modelArr!=null) {
		$modelEngine=$modelArr["modelEngine"];
		if($modelEngine=="DataControls1") {
			if(isset($modelArr["modelData"]["hiddenCols"])) $hiddenCols=explode(",",$modelArr["modelData"]["hiddenCols"]);
			if(isset($modelArr["modelData"]["searchCols"])) $searchCols=explode(",",$modelArr["modelData"]["searchCols"]);
			if(isset($modelArr["modelData"]["sortCols"])) $sortCols=explode(",",$modelArr["modelData"]["sortCols"]);
			if(isset($modelArr["modelData"]["classes"])) $classes=explode(",",$modelArr["modelData"]["classes"]);
			if(isset($modelArr["modelData"]["alinks"])) $alinks=$modelArr["modelData"]["alinks"];
		} else {
			dispErrMessage("DataModel Not Found Requested Form.","Form Model Error",
					404,"media/images/notfound/database.png");
			exit();
		}
	}
}

$model="[";
foreach($d as $x) {
	if($x=="act1") {
		$model.="{";
		$model.="name:'action1'";
		$model.=",index:'$x'";
		$model.=",search:false";
		$model.=",sortable:false";
		$model.=",hidden:false";
		$model.=",formatter:'actions'";
		$model.=",width:110";
		$model.="},";
	} elseif($x=="act2") {
		$model.="{";
		$model.="name:'action2'";
		$model.=",index:'$x'";
		$model.=",search:false";
		$model.=",sortable:false";
		$model.=",hidden:false";
		$model.=",formatter:function() {return '<select onchange=\"gridAction(this,this.value);\">".getActionsSelector($alinks)."</select>';}";
		$model.=",width:150";
		$model.="},";
	} else {
		$y=explode(".",$x);
		$y=$y[count($y)-1];

		$model.="{";
		$model.="name:'$y'";
		$model.=",index:'$x'";

		if(in_array($x,$searchCols)) {
			$model.=",search:true";
		} else {
			$model.=",search:false";
		}
		if(in_array($x,$sortCols)) {
			$model.=",sortable:true";
		} else {
			$model.=",sortable:false";
		}
		if(isset($classes[$n]) && strlen($classes[$n])>0) {
			$model.=",classes:'{$classes[$n]}'";
		}

		if($n==0) {
			$model.=",key:true,width:$idField";
		}
		if(in_array($x,$hiddenCols) || $n>$maxCols) {
			$model.=",hidden:true";
		}
		$model.="},";
	}

	$n++;
}
$model.="]";
if(!function_exists("getActionsSelector")) {
	function getActionsSelector($alinks) {
		$s="<option value=\"*\">Select Action</option>";

		if(is_array($alinks)) {
			foreach($alinks as $a=>$b) {
				$s.="<option value=\"$b\">$a</option>";
			}
		}
	
		return $s;
	}
}
?>
