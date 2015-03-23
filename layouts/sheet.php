<?php
include "data_common.php";

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

<div style='width:100%;height:100%;padding:0px;margin:0px;overflow:hidden;margin-bottom:-10px;'> 
	<div id=hd1 class='ui-widget-header' style='width:100%;height:25px;overflow:hidden;'>
		<h3 style='margin:0px;margin-top:4px;margin-left:15px;float:left;'>DataTable</h3>
		<div id=gridtoolbar style='float:right;margin-right:2px;'>
			<?=printGridButtons($dataSource["toolbtns"],$dataSource["divid"]);?>
		</div>
	</div>
	<form>
		<div id=dgrid1 class=formDataTable style='width:101%;height:100%;overflow-y:hidden;overflow-x:auto;'>
			<table id='<?=$dataSource["divid"]?>_grid_table'>				
			</table>
			<div id='<?=$dataSource["divid"]?>_grid_pager' class="pager"></div>
		</div>
	</form>
</div>

<script type="text/javascript">
jqColumns={
		"colNames":<?=$cols?>,
		"colModel": <?=$model?>,
		};
colModel=jqColumns.colModel;
rptOptions.extraToolbar=true;
<?=$dataSource["datatable_params"]?>
exportCSS="<?=$css?>";
$(function() {
	$(".LGKSFORMTABLE").css("overflow","hidden");
	$(".LGKSFORMTABLE").delegate("input","keypress",function(key) {
			if(key.charCode==13) {
				th=$(this).parents("th");
				focusNext(th);
			} else if(key.charCode==10) {
				formSubmit($(frmID+" .ui-jqgrid-view .sheetFormBtn").get(0));
			}
		});
});
function focusNext(th) {
	if(th==null) return;
	a=th.next();
	if(a.length>0) {
		nm=a.find("input").attr("name");
		col="th#"+frmID.substr(1)+"_grid_table_"+nm;
		if($(col).is(":visible")) {
			a.find("input").focus();
		} else {
			focusNext($(col));
		}
	}
}
function updateGridDataForm() {
	$(frmID+" .ui-jqgrid-view .ui-jqgrid-hbox table.ui-jqgrid-htable thead .ui-sheet-form").detach();
	
	n=$(frmID+" .ui-jqgrid-view .ui-jqgrid-hbox table.ui-jqgrid-htable thead th").length;
	html="<tr class='ui-sheet-form' role='rowheader'>";
	
	html+="<input type='hidden' id='data_userid' name='userid' value='<?=$_SESSION['SESS_USER_ID']?>' />";
	html+="<input type='hidden' name='frmID' value='<?php if(isset($dataSource['id'])) echo $dataSource['id']; ?>' />";
	html+="<input type='hidden' name='submit_table' value='<?=$dataSource['submit_table']?>' />";
	html+="<input type='hidden' name='submit_wherecol' value='<?=$dataSource['submit_wherecol']?>'/>";
	html+="<input type='hidden' id='data_id' name='<?=$idCol?>' value='0' />";
	
	for(i=0;i<n;i++) {
		html+="<th role='columnheader' class='ui-state-default ui-th-column ui-th-ltr'>";
		html+="<div style='width:100%;height:100%;position:relative;padding-right:0.3em;'>";
		if(colModel[i]!=null) {
			if(colModel[i].index=="<?=$idCol?>") {
				html+="<input type=hidden style='width:95%;padding:0px;' name='"+colModel[i].index+"' id='sf-"+colModel[i].index+"' value='0' readonly=true />";
				html+="<button type=button style='display:none' class='sheetFormBtn'></button>";
			} else {
				html+="<input type=text style='width:95%;padding:0px;' name='"+colModel[i].index+"' id='sf-"+colModel[i].index+"' />";
			}
		}
		html+="</div></th>";
	}
	html+="</tr>";
	$(frmID+" .ui-jqgrid-view .ui-jqgrid-hbox table.ui-jqgrid-htable thead").append(html);
}
</script>
