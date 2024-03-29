<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include_once __DIR__."/api.php";

$slug=_slug("?/src/mode/refid");

if(isset($slug['src']) && !isset($_REQUEST['src'])) {
	$_REQUEST['src']=$slug['src'];
}
if(isset($slug['mode'])) {
  $mode=$slug['mode'];
} elseif(isset($_REQUEST['mode'])) {
  $mode=$_REQUEST['mode'];
} else {
  $mode="new";
}
if(isset($_REQUEST['src']) && strlen($_REQUEST['src'])>0) {
	$formConfig=findForm($_REQUEST['src']);

	if($formConfig) {
		if(($mode=="edit" || $mode=="update") && isset($slug['refid'])) {
			$where=[];
			if(isset($formConfig['source']) && isset($formConfig['source']['refcol'])) {
				$refid=$formConfig['source']['refcol'];
			} else {
				// $refid="id";
				$refid="md5(id)";
			}
			$where=["{$refid}"=>$slug['refid']];//$where=["md5({$refid})"=>$slug['refid']];,['gotolink'=>$glink]
			
			if(isset($formConfig['source']['precreate']) && isset($formConfig['source']['precreate'])==true) {
				autoReferenceSystem($formConfig,$slug['refid']);
			}

			printForm($mode,$formConfig,$formConfig['dbkey'],$where);
		} else {
			printForm($mode,$formConfig,$formConfig['dbkey']);
		}
	} else {
		echo "<h1 class='errormsg'>Sorry, form '{$_REQUEST['src']}' not found.</h1>";
	}
} else {
	echo "<h1 class='errormsg'>Sorry, form not defined.</h1>";
}

?>