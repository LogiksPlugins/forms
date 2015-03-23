<?php
_js(array("jquery.ghosttext"));
?>
<script language='javascript'>
$(function() {
	registerPluginLoader(function() {
			$('input.ghosttext,textarea.ghosttext',frmID).each(function() {
				$(this).ghosttext();
			});
		});
});
</script>
