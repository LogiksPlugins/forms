<?php

?>
<style>
.formTable .ui-progressbar .ui-progressbar-value {
	border:0px !important;
}
</style>
<script language='javascript'>
$(function() {
	$(frmID+" .progressbar").each(function() {
			x=0;
			td=$(this).parent("td");
			if($(this).attr("value")!=null && $(this).attr("value").length>0) {
				x=parseInt($(this).attr("value"));
			} else if($(this).attr("src")!=null && $(this).attr("src").length>0) {
				x=parseInt($(this).attr("src"));
			}
			$(this).detach();
			td.addClass("ui-state-default");
			
			td.progressbar({
				value:x,
			});
		});
});
</script>
