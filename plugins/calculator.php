<?php
$calculator=true;
if($calculator) {
	echo "<link href='".$webPath."css/calculator.css' rel='stylesheet' type='text/css' media='all' /> ";
	echo "<script src='".$webPath."js/calculator.js' type='text/javascript' language='javascript'></script>";
}
?>
<script language='javascript'>
$(function() {
	registerPluginLoader(function() {
			initCalculator(frmID);
		});
});
function initCalculator(id) {
	sltr=id+" .calculatorfield";
	
	if((typeof $.fn.calculator!="function")) return;	
	if($(sltr).length<=0) return;
	
	$(sltr).each(function() {
			if($(this).parents("td").find(".calc_button").length>0) return;
			$(this).parents("td").append("<button type=button class='calc_button nostyle'></button>");
			$(this).parents("td").find("button.calc_button").click(function() {
					chld=$(this).parents("tr").find("td.columnInput").children();
					if(chld.length<=0) return;
					field=$(chld[0]);
					$("#form_calculator").dialog({
							width:210,
							height:240,
							resizable:'none',
							draggable:true,
							modal:false,
							stack:true,
							dialogClass:'alert',
							closeOnEscape:true,
							create:function() {
								txt=field.val();
								$('#form_calculator').find("label.calc-text").text(txt);
							}, 
							close:function() {
								txt=$('#form_calculator').find("label.calc-text").text();
								field.val(txt);
							}
						});
				});
		});
	$("body").prepend("<div id='form_calculator' title='Calculator' style='display:none;'></div>");
	$('#form_calculator').calculator();
}
</script>
