<?php
include "data_common.php";
$canCreateNew=false;
if($dataSource["toolbtns"]=="*" || strpos("#".$dataSource["toolbtns"],"new")>1) {
	$canCreateNew=true;
}
?>
<div class='datatabs' style='width:100%;height:100%;border:0px;'>
	<ul>
		<li><a href='#dgrid11'>Datatable</a></li>
		<li style='display:none;'><a href='#dform1'>Form</a></li>
		<?php if($canCreateNew) { ?>
		<li class='createNew'><a href='#' onclick='createNewForm()'>New</a></li>
		<?php } ?>
	</ul>
	<div id=dgrid11 style='padding:5px;height:100%;padding-right:10px;overflow:hidden;'>
		<div id=hd1 class='ui-widget-header ui-corner-top' style='width:100%;height:25px;overflow:hidden;'>
			<h3 style='margin:0px;margin-top:4px;margin-left:15px;float:left;'>DataTable</h3>
			<div id=gridtoolbar style='float:right;margin-right:2px;'>
				<?=printGridButtons($dataSource["toolbtns"],$dataSource["divid"]);?>
			</div>
		</div>
		<div id=dgrid1 class=formDataTable style='width:100%;height:96%;overflow-y:hidden;overflow-x:scroll;'>
			<table id='<?=$dataSource["divid"]?>_grid_table'>				
			</table>
			<div id='<?=$dataSource["divid"]?>_grid_pager' class="pager"></div>
		</div>
	</div>
	<div id=dform1>
		<?php
			$width="800px";
			include "plain.php";
		?>
	</div>
</div>
<style>
.LGKSFORMTABLE {background:white;}
.LGKSFORMTABLE .ui-corner-bottom {border:0px !important;}
.LGKSFORMTABLE .datatabs .createNew.ui-state-disabled {opacity:1;cursor:pointer;}
.LGKSFORMTABLE .datatabs .createNew.ui-state-disabled a {opacity:1;cursor:pointer;}
</style>
<script type="text/javascript">
jqColumns={
		"colNames":<?=$cols?>,
		"colModel": <?=$model?>,
		};

<?=$dataSource["datatable_params"]?>
exportCSS="<?=$css?>";
grid_less_height=20;//75
$(function() {
	//$(frmID).css("height",$(frmID).height()-30);
	//$jqGrid.setGridHeight("500px");
	$(".datatabs").tabs();
	onEditFunc.push(function() {
		$(".datatabs").tabs("select",1);
	});
	onSubmitFunc.push(function() {
		$(".datatabs").tabs("select",0);
	});
});
<?php if($canCreateNew) { ?>
function createNewForm() {
	$(".datatabs").tabs("select",1);
	createBlankForm();
	formReset(null);
}
<?php } ?>
</script>
