<?php

?>
<script language='javascript'>
$(function() {
	$(frmID+" .slider").each(function() {
			min1=0;
			max1=100;
			val=0;
			forWhom="";
			
			if($(this).attr("min")!=null) min1=parseInt($(this).attr("min"));
			if($(this).attr("max")!=null) max1=parseInt($(this).attr("max"));
			if($(this).attr("value")!=null) val=parseInt($(this).attr("value"));
			
			$(this).slider({
					min:min1,
					max:max1,
					value:val,
					orientation:"horizontal",
					range: "min",
					animate: true,
					slide: function( event, ui ) {
						if($(this).attr("for")!=null) {
							$($(this).attr("for")).val(ui.value);
							$($(this).attr("for")).html(ui.value);
						}
					}
				});
		});
});
</script>
