<?php
_js(array("jquery.tagit"));
_css(array("jquery.tagit"));
?>
<style>
.tagit.ui-widget.ui-widget-content {
	width:90%;
	margin-top:0px;
	margin-bottom:0px;
}
</style>
<script language='javascript'>
$(function() {
	registerPluginLoader(function() {
			$(frmID+' .tagfield').each(function() {
				sf=true;
				as=false;
				if($(this).attr("singleField")!=null) sf=$(this).attr("singleField");
				if($(this).attr("allowSpaces")!=null) as=$(this).attr("allowSpaces");
				//$(this).removeClass("tagfield");
				$(this).tagit({
					singleField:sf,
					allowSpaces:as,
				});
			});
			onEditFunc.push(updateTagFields);
			onClearFunc.push(clearTagFields);
		});
});
function clearTagFields(frmID) {
	$(frmID+' .tagfield').each(function() {
			$(this).tagit("removeAll");
		});
}
function updateTagFields(frmID) {
	$(frmID+' .tagfield').each(function() {
			v=$(this).val();
			$(this).val("");
			selector=$(this);
			selector.tagit("removeAll");
			v=v.split(",");
			$.each(v,function(k,v1) {
					selector.tagit("createTag",v1);
				});
		});
}
</script>
