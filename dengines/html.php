<?php
if(!$dataSource['noFormToolBar']) {
	echo "<div id=formtoolbar class='formtoolbar_top ui-widget-header' style='width:$width;height:35px;margin:auto;overflow:hidden;' align=left>";
	if(isset($dataSource["toolbtns"]) && strlen($dataSource["toolbtns"])>0) {
		printToolButtons($dataSource["toolbtns"],$dataSource["divid"]);
	}
	echo "<div id=formModeDisp class='clr_darkblue' title='Form Mode'>new</div>";
	echo "</div>";
}	
	$idCol="id";
	$dtCols=explode(",",$dataSource['datatable_cols']);
	foreach($dtCols as $a=>$b) {
		$xx=explode(".",$b);
		$dtCols[$a]=$xx[count($xx)-1];
	}
	if(is_array($dtCols) && !in_array($idCol,$dtCols)) {
		if(strlen($dataSource['submit_wherecol'])>0) {
			$sArr=explode(",",$dataSource['submit_wherecol']);
			if(strlen($sArr[count($sArr)-1])===0) unset($sArr[count($sArr)-1]);
			if(count($sArr)===1) {
				$idCol=$sArr[0];
			}
		}
	}
?>
<table id='<?=$dataSource["divid"]?>_form' class='formTable' align=center width=<?=$width?> >
	<?php if(strlen($dataSource['header'])>0) echo "<tr class='formheader'><td colspan='100'><strong>".$dataSource['header']."</strong></td></tr>"; ?>
	
	<input type="hidden" id='data_userid' name="userid" value="<?=$_SESSION["SESS_USER_ID"]?>" />
	<input type="hidden" name="frmID" value="<?php if(isset($dataSource['id'])) echo $dataSource['id']; ?>" />
	
	<input type="hidden" id='data_id' name="<?=$idCol?>" value="0" />
	
	<input type="hidden" name="submit_table" value="<?=$dataSource['submit_table']?>" />
	<input type="hidden" name="submit_wherecol" value="<?=$dataSource['submit_wherecol']?>"/>
	
	<?php
		echo _replace($dataSource['frmdata']);
		
		if(strlen($dataSource['toolbtns'])>0) {
			$rBtns=explode(",",$dataSource['toolbtns']);
			if($dataSource['toolbtns']=="*" || in_array("edit",$rBtns) || in_array("new",$rBtns)) {
				echo "<tr class='divider'><td class='divider' colspan=100></td>";
				echo "</tr><tr class=formtoolbar><td colspan=100>";
				printToolButtons("reset,submit",$dataSource["divid"]);
				echo "</td></tr>";
			}
		}
		//echo "<tr><td><br/></td></tr>";
		echo "<tr><td><br/></td></tr>";
		if(strlen($dataSource['footer'])>0) {
			echo "<tr class=formfooter><td colspan=100>";
			echo $dataSource['footer'];
			echo "</td></tr>";
		}
	?>
</table>
<script>
formHeader="<?=$dataSource['header']?>";
</script>
