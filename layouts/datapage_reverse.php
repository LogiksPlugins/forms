<?php
include "data_common.php";
?>
<div class="splitdiv" style='width:100%;height:100%;padding:0px;margin:0px;'> 
	<div class='leftdiv'style='overflow-x:hidden;' >
		<div style='width:50%;margin:auto;'>
		<?php
			$width="100%";
			include "plain.php";
		?></div>
	</div> 
	<div class='rightdiv' style='overflow-x:hidden;'>
		<div id=hd1 class='ui-widget-header' style='width:100%;height:25px;overflow:hidden;'>
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
</div>

<script type="text/javascript">
jqColumns={
		"colNames":<?=$cols?>,
		"colModel": <?=$model?>,
		};

<?=$dataSource["datatable_params"]?>
exportCSS="<?=$css?>";
splitterConfig={
			type:'h', 
			minTop:'100', 
			minBottom:'100',
			sizeTop:$(window).height()/2-50,
			onResize:resizeHSplits,
		};
$(function() {
	$("#<?=$dataSource["divid"]?>").css("overflow","hidden");
	
	setTimeout(function() {
		//$("#<?=$dataSource["divid"]?> .splitdiv").css("height",($("#<?=$dataSource["divid"]?>").height())+"px");
		$("#<?=$dataSource["divid"]?> .splitdiv").css("height",($(window).height()-60)+"px");
		//$("#<?=$dataSource["divid"]?> .splitdiv").css("height",($("#<?=$dataSource["divid"]?>").height())+"px");
		$("#<?=$dataSource["divid"]?> .splitdiv").css("width","101%");
		$("#<?=$dataSource["divid"]?> .splitdiv").splitter(splitterConfig);
		$(".splitdiv .vsplitbar, .splitdiv hsplitbar").addClass("ui-widget-header");
	},100);
});
</script>