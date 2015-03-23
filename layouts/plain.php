<?php
$dataSource=$GLOBALS['FRMDATA'];
if(!isset($width)) {
	if(isset($_REQUEST['width'])) $width=$_REQUEST['width'];
	if(isset($dataSource['width'])) $width=$dataSource['width'];
	else $width="800px";
}

if(file_exists(dirname(dirname(__FILE__))."/dengines/".$dataSource['engine'].".php")) {
	include dirname(dirname(__FILE__))."/dengines/".$dataSource['engine'].".php";
} else {
	echo "<h3>The Form Engine Type Is Not Yet Supported. <br/>Please ask Admin to add this module :: <b style='color:orange'>"."form_dengine_".$dataSource['frmdata_type']."</b></h3>";
}
?>
