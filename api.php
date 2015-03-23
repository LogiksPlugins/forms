<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_SESSION["SESS_USER_ID"])) $_SESSION["SESS_USER_ID"]='guest';
if(!isset($_SESSION["SESS_USER_NAME"])) $_SESSION["SESS_USER_ID"]='Guest';
if(!isset($_SESSION["SESS_PRIVILEGE_ID"])) $_SESSION["SESS_PRIVILEGE_ID"]='-99';
if(!isset($_SESSION["SESS_PRIVILEGE_NAME"])) $_SESSION["SESS_PRIVILEGE_NAME"]='*';

$webPath=getWebPath(__FILE__);
$rootPath=getRootPath(__FILE__);

loadHelpers("countries");
loadHelpers("uicomponents");

include_once "buttons.php";
include_once "functions.php";

function loadFormFromDB($frmID, $frmTable, $showHelpInfo=true, $layout=null,$noFormToolBar=false,$animation=true,$plugins=null) {
	$frmData=array();

	$sql="SELECT * FROM $frmTable where id='$frmID'";

	$dbLink=getAppsDBLink();
	$result=$dbLink->executeQuery($sql);
	if($dbLink->recordCount($result)>0) {
		$frmData=$dbLink->fetchData($result);
		if($frmData['blocked']=='true') {
			trigger_ForbiddenError("Sorry, Required Form Is Forbidden.");
			return;
		}
		if($_SESSION["SESS_PRIVILEGE_ID"]>2 && $frmData["privilege"]!="*") {
			if(strlen($frmData["privilege"])>0) {
				$priArr=explode(",",$frmData["privilege"]);
				if(!in_array($_SESSION["SESS_PRIVILEGE_NAME"],$priArr)) {
					trigger_ForbiddenError("Sorry, Required Form Is Forbidden.");
					return;
				}
			} else {
				trigger_ForbiddenError("Sorry, Required Form Is Forbidden.");
				return;
			}
		}
		$q=array();
		foreach($_GET as $a=>$b) {
			if($a=="site" || $a=="toolbar" || $a=="page" || $a=="mod" || $a=="rid") continue;
			else $q[]="$a=".urlencode($b);
		}
		$q=implode("&",$q);
		$frmData['dataSource']="services/?scmd=datagrid&site=".SITENAME."&action=load&sqlsrc=dbtable&sqltbl=$frmTable&sqlid=".$frmData["id"]."&{$q}";

		printForm($frmData, $showHelpInfo, $layout,$noFormToolBar,$animation,$plugins);
	} else {
		trigger_NotFound("Sorry, Required Form Not Found.");
	}
}
function printForm($frmData, $showHelpInfo=true, $layout=null,$noFormToolBar=false,$animation=true,$plugins=null) {
	$webPath=getWebPath(__FILE__);
	$rootPath=getRootPath(__FILE__);

	if($frmData==null) return;
	if(strlen($frmData['frmdata'])==0) {
		echo "<link href='".$webPath."css/formsui.css' rel='stylesheet' type='text/css' media='all' /> ";
		printFormNotFound();
		return;
	}

	if(!isset($frmData['datatable_hiddenCols'])) {
		$frmData['datatable_hiddenCols']="";
	}
	if(!isset($frmData['datatable_colnames'])) {
		$frmData['datatable_colnames']="";
	}

	$frmData["toolbtns"]="";
	if($_SESSION["SESS_PRIVILEGE_ID"]>3) {
		if(isset($frmData["privilege_model"])) {
			if(strlen($frmData["privilege_model"])<=0) {
				$frmData["toolbtns"]="";
			} else {
				$pModel=(array)json_decode($frmData["privilege_model"]);
				if(isset($pModel[$_SESSION["SESS_PRIVILEGE_NAME"]])) {
					$frmData["toolbtns"]=$pModel[$_SESSION["SESS_PRIVILEGE_NAME"]];
				} else {
					$frmData["toolbtns"]="";
				}
			}
		}
	} else {
		$frmData["toolbtns"]="*";
	}
	$frmData['noFormToolBar']=$noFormToolBar;

	$divId="form_".$frmData['id']."_".time();
	$frmData["divid"]=$divId;
	$GLOBALS['FRMDATA']=$frmData;

	if($layout==null) $layout=$frmData["layout"];

	if(strlen($frmData["datatable_cols"])<=0) {
		$layout="plain";
	}

	if(isset($_REQUEST['frmRelink'])) {
		$frmData["submit_action"]="goto#"._link($_REQUEST['frmRelink']);
	} elseif(isset($_REQUEST['frmMsg'])) {
		$frmData["submit_action"]="msg#{$_REQUEST['frmMsg']}";
	}

	$fL=dirname(__FILE__)."/layouts/".$layout.".php";
	$fpath="";
	if(file_exists($fL)) {
		$cache=CacheManager::singleton();
		$cacheID="form_".$frmData['id'];
		$fpath=$cache->getCacheLink($fL,$cacheID,true);
	} else {
		echo "<link href='".$webPath."css/formsui.css' rel='stylesheet' type='text/css' media='all' /> ";
		printFormNotFound("Sorry, Required Layout Is Not <u style='color:darkgreen;'>".strtoupper($layout)."</u> Installed OR Supported On This System Yet.");
		return;
	}
	_js(array("jquery.ui","lgksplugin","jquery.ui-timepicker","jquery.splitter","jquery.mailform","validator"));
	_css(array("colors","styletags","formtable","formfields"));

	echo "<link href='".$webPath."css/formsui.css' rel='stylesheet' type='text/css' media='all' />\n";
	echo "<script src='".$webPath."js/forms.js' type='text/javascript' language='javascript'></script>\n";
	if($layout=="plain") {

	} else {
		echo "<link href='".$webPath."css/split.css' rel='stylesheet' type='text/css' media='all' />\n";

		echo "<script src='".$webPath."js/grid.js' type='text/javascript' language='javascript'></script>\n";
		echo "<script src='".$webPath."js/gridprint.js' type='text/javascript' language='javascript'></script>\n";
	}

	loadLoadableFormPlugins($plugins);
?>
<style>
#<?=$divId?> .formTable,#<?=$divId?> .formDataTable {
	display:none;
}
.helpinfo {
	display:<?=$showHelpInfo?"block":"none";?>;
}
.ui-jqgrid-bdiv>div {overflow:hidden;}
</style>
<div id='<?=$divId?>_frmloader' style='height:100%;'><div id=formLoader class='ajaxloading'>Loading Form ...</div></div>
<div id='<?=$divId?>' class='LGKSFORMTABLE' style='overflow:auto;height:100%;width:100%;'>
	<form id="dataform_<?=$divId?>">
	<?php
		if(strlen($fpath)>0 && file_exists($fpath)) {
			include_once $fpath;
		} else {
			dispErrMessage("Requsted Form Could Not Be Found, Please contact your admin.",
					"404:Form Not Found",404,"media/images/warning.png");
		}
	?>
	</form>
<br/>
</div>
<div class='notesLegend helpinfo ui-widget-content ui-corner-all'>
	<table width=100% border=0 cellpadding=3px>
		<tr>
			<td width=50px class='field_required' style="background-color:transparent;"></td>
			<td align=left>Required</td>
		</tr>
		<tr>
			<td width=50px class='field_check' style="background-color:transparent;"></td>
			<td align=left>Correct Entry</td>
		</tr>
		<tr>
			<td width=50px class='field_error' style="background-color:transparent;"></td>
			<td align=left>Validation Error</td>
		</tr>
		<tr>
			<td width=50px class='field_warns' style="background-color:transparent;"></td>
			<td align=left>Field Should Be Filled</td>
		</tr>
		<tr>
			<td width=50px class='field_searchable'></td>
			<td align=left>Searchable Field</td>
		</tr>
	</table>
</div>
<div style='display:none'>
	<div id=toMailForm_css>
		<?php
			echo file_get_contents($rootPath."css/formexport.css");
		?>
	</div>
	<div id="fileUploader_<?=$divId?>">
		<form class=fileform target='formsubmitframe' method='post' enctype='multipart/form-data' action='services/?scmd=attachments&site=<?=SITENAME?>&action=upload'>
		</form>
	</div>
	<iframe id=formsubmitframe name=formsubmitframe style='display:none'></iframe>
</div>
<script language='javascript'>
data_userid="<?=$_SESSION["SESS_USER_ID"]?>";
dateFormat="<?=getConfig("DATE_FORMAT")?>";
timeFormat="<?=strtolower(str_replace("i","m",getConfig("TIME_FORMAT")))?>";
defMode="<?=$frmData["def_mode"]?>";
frmMode="<?=$frmData["def_mode"]?>";
yearRange="<?=date("Y")-getConfig("DATE_YEAR_RANGE")?>:<?=date("Y")+getConfig("DATE_YEAR_RANGE")?>";
actionOnSubmit="<?=$frmData["submit_action"]?>";
formActionLink="<?=$frmData["adapter"]?>";

var dataSource="<?=SiteLocation.$frmData["dataSource"]."&datatype=json"?>";
var toMailLink="<?=SiteLocation?>services/?scmd=formaction&site=<?=SITENAME?>&action=mail";//"services/?scmd=mail";
var toDBAddLink="<?=SiteLocation?>services/?scmd=formaction&site=<?=SITENAME?>&action=submit&response=json";
var toDBDeleteLink="<?=SiteLocation?>services/?scmd=formaction&site=<?=SITENAME?>&action=delete";
var toDBMailLink="<?=SiteLocation?>services/?scmd=formaction&site=<?=SITENAME?>&action=dbmail";

frmID="#<?=$divId?>";

$(function() {
	loadUI(frmID);
	initForm(frmID);
	loadPlugins(frmID);
	showUI(frmID);
	
	<?php
		if(isset($_REQUEST['autoload'])) {
			$pid=explode(",",$_REQUEST['autoload']);
			foreach($pid as $a) {
				if(isset($_REQUEST[$a])) {
					echo "$(frmID).find('input[name=$a],select[name=$a],txtarea[name=$a]').val('{$_REQUEST[$a]}');";
				}
			}
	?>
			loadData(frmID,"<?=$frmData['id']?>");
			setFormMode("edit","<?=$frmData['id']?>");
	<?php
		} elseif(isset($_REQUEST['pushload'])) {
			echo 'setFormMode("new");';
			echo "setTimeout(function() {";
			$pid=explode(",",$_REQUEST['pushload']);
			foreach($pid as $a) {
				if(isset($_REQUEST[$a])) {
					echo "$(frmID).find('input[name=$a],select[name=$a],txtarea[name=$a]').val('{$_REQUEST[$a]}');";
				}
			}
			echo "},100);";
		} else {
	?>
	setFormMode("new");
	loadData(frmID,"<?=$frmData['id']?>");
	<?php
		}
	?>
});
function loadUI(frmid) {
	$(frmid+" .formTable .formheader").addClass("ui-widget-header");//ui-widget-header,ui-state-default,ui-state-active
	$(frmid+" .formTable .formsubheader").addClass("ui-state-active");//ui-widget-header,ui-state-default,ui-state-active
	$(frmid+" .formTable .formfooter").addClass("clr_pink");//ui-widget-header,ui-state-default,ui-state-active
	$(frmid+" .formTable").addClass("ui-widget-content");
	$(frmid+" .formTable").css("margin","auto");
	//$(".formholder").addClass("ui-widget-content");
}
function showUI(frmid) {
	<?php
	if($animation) {
		if($animation===true) {
			$animation="classic";
		}

		if($animation=="classic") {
		?>
			$(frmid+"_frmloader").slideUp();//.delay(100).detach();
			$(frmid+" .formTable").fadeIn().delay(100).slideDown();
			$(frmid+" .formDataTable").fadeIn().delay(100).slideDown();
		<?php
		} else {
			$animation=explode(",",$animation);

			echo '$(frmid+"_frmloader").slideUp();';
			$x=array();
			foreach($animation as $a) {
				if(strlen($a)>0) array_push($x,".show('$a')");
			}
			$x=implode(".delay(100)",$x);
			echo "$('.formTable,.formDataTable',frmid){$x}";
		}
	} else  { ?>
		$(frmid+"_frmloader").css("display","none");
		$(frmid+" .formTable").css("display","block");
		$(frmid+" .formDataTable").css("display","block");
	<?php } ?>
}
<?php
if(isset($GLOBALS['FRMDATA']["script"])) {
	echo $GLOBALS['FRMDATA']["script"];
}
?>
</script>
<?php
if(isset($GLOBALS['FRMDATA']["style"])) {
	echo "<style>";
	echo $GLOBALS['FRMDATA']["style"];
	echo "</style>";
}
?>
<?php
}
unset($GLOBALS['FRMDATA']);
?>
