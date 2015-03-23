<?php
function printToolButtons($btns,$divid) {
	$uid=$_SESSION["SESS_USER_ID"];
	$_form_buttons=array();

	$_form_buttons['new']="<button id='frm_btn_new' type=reset onclick='createBlankForm(this); return true;'>New</button>";
	$_form_buttons['edit']="<button id='frm_btn_edit' type=button onclick='editRecord(this);'>Edit</button>";
	$_form_buttons['delete']="<button id='frm_btn_delete' type=button onclick='deleteRecord(this);'>Delete</button>";
	$_form_buttons['reset']="<button id='frm_btn_reset' type=reset onclick='checkClearMode(this); return false;'>Clear</button>";
	$_form_buttons['submit']="<button id='frm_btn_submit' type=button onclick='formSubmit(this)'>Submit</button>";
	$_form_buttons['print']="<button id='frm_btn_print' type=button onclick='formPrint(this)'>Print</button>";
	$_form_buttons['mail']="<button id='frm_btn_mail' type=button onclick='formMail(this)'>Mail</button>";
	$_form_buttons['download']="<button id='frm_btn_download' type=button onclick='formExport(this)'>Export</button>";

	$_form_buttons['clone']="<button id='frm_btn_clone' type=button onclick='formClone(this)'>Clone</button>";
	
	if(strtolower($btns)=="reset,submit") {
		//$btns="reset,clone,submit";
	}
	if($btns=="*") {
		if(isset($GLOBALS['FRMDATA']['layout'])) {
			if($GLOBALS['FRMDATA']['layout']=="plain") {
				$btns="print,mail,download";
			} else {
				$btns="new,edit,delete,print,mail,download";
			}
		} else {
			$btns="new,edit,delete,print,mail,download";
		}
	} elseif($_SESSION['SESS_PRIVILEGE_ID']<=3 && strlen($btns)<=0) {
		if(isset($GLOBALS['FRMDATA']['layout'])) {
			if($GLOBALS['FRMDATA']['layout']=="plain") {
				$btns="print,mail,download";
			} else {
				//$btns="new,edit,delete,print,mail,download";
			}
		} else {
			//$btns="new,edit,delete,print,mail,download";
		}
	}
	if(!is_array($btns)) $arr=explode(",",$btns);
	else $arr=$btns;
	foreach($arr as $a=>$b) {
		if(isset($_form_buttons[trim($b)])) echo $_form_buttons[trim($b)];
	}
}
function printGridButtons($btns,$divid) {
	$uid=$_SESSION["SESS_USER_ID"];
	$_form_buttons=array();

	$_form_buttons['search']="<div id='gridtbl_btn_search' class='gridbutton btn_search' style='width:25px;height:25px;float:right;' onclick='searchDataTable(this);' title='Search Data'></div>";
	$_form_buttons['edit']="<div id='gridtbl_btn_edit' class='gridbutton btn_edit' style='width:25px;height:25px;float:right;' onclick='editRecord(this);' title='Edit Record'></div>";
	$_form_buttons['printview']="<div id='gridtbl_btn_print' class='gridbutton btn_print' style='width:25px;height:25px;float:right;' onclick='printGrid(this);' title='Print Table'></div>";
	$_form_buttons['exportview']="<div id='gridtbl_btn_xls' class='gridbutton btn_xls' style='width:25px;height:25px;float:right;' onclick='exportToExcel(this);' title='Export In Excel'></div>";
	$_form_buttons['exportview'].="<div id='gridtbl_btn_html' class='gridbutton btn_html' style='width:25px;height:25px;float:right;' onclick='exportToHTML(this);' title='Export In HTML'></div>";
	$_form_buttons['mailview']="<div id='gridtbl_btn_mail' class='gridbutton btn_mail' style='width:25px;height:25px;float:right;' onclick='mailGrid(this);' title='Mail Table'></div>";

	if($btns=="*") {
		$btns="";
		$btns=implode(",",array_keys($_form_buttons));
	}
	if(!is_array($btns)) $arr=explode(",",$btns);
	else $arr=$btns;

	echo "<div class='gridbutton btn_info' style='width:25px;height:25px;float:right;' onclick='showHelpInfo(this);' title='Show Help Info'></div>";
	echo "<div class='gridbutton btn_column' style='width:25px;height:25px;float:right;' onclick='colChange(this);' title='Change Viewable Columns'></div>";
	foreach($arr as $a=>$b) {
		if(isset($_form_buttons[trim($b)])) echo $_form_buttons[trim($b)];
	}
	echo "<div id='gridtbl_btn_view' class='gridbutton btn_view' style='width:25px;height:25px;float:right;' onclick='viewRecord(this);' title='View Record'></div>";
	echo "<div id='gridtbl_btn_reload' class='gridbutton btn_reload' style='width:25px;height:25px;float:right;' onclick='reloadDataTable(this);' title='Reload Table'></div>";
	echo "<div class='gridbutton btn_new' style='width:25px;height:25px;float:right;' onclick='formReset(null);createBlankForm(null);' title='Create New Form'></div>";	
}
?>
