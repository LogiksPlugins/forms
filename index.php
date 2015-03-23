<?php
include_once "api.php";
?>
<div class='formholder' style='width:100%;height:100%;'>
<?php
if(isset($_REQUEST['fid'])) {
	if(isset($_REQUEST['ui'])) {
		loadFormFromDB($_REQUEST['fid'],_dbtable("forms"),false,$_REQUEST['ui']);
	}else {
		loadFormFromDB($_REQUEST['fid'],_dbtable("forms"),false);
	}
} else {
	echo "<h3>Form Not Found</h3>";
}
?>
</div>
<style>
.ui-pager-control .ui-paging-info {
	overflow:hidden;
}
.ui-tabs-panel.ui-widget-content {
	padding:0px !important;
	margin:0px !important;
}
.formTable {
	font-size:12px !important;
}
</style>
<script>
$(function() {
	$(".formholder").height($(window).height()-$(".formholder").parents(".tabs").find(".ui-tabs-nav").height()-10);
});
</script>
