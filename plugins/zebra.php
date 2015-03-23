<?php
$dateEngine=getSiteSettings("Form Date Field Type","default","Forms","list","default,zebra");
if($dateEngine=="zebra") {
	echo "<link href='".$webPath."css/zebra_datepicker.css' rel='stylesheet' type='text/css' media='all' /> ";
	echo "<script src='".$webPath."js/zebra_datepicker.js' type='text/javascript' language='javascript'></script>";
?>
<script language='javascript'>
$(function() {
	registerPluginLoader("initZebraDatePicker");
});
function initZebraDatePicker(id) {
	if(id==null) id=frmID;
	sltr=id+" .datefield";
	
	if(typeof window.$.Zebra_DatePicker != "function") return;
	if($(sltr).length<=0) return;
	
	zFormat=dateFormat.replace("yy","Y");
	try {
		$(sltr).datepicker("destroy");
	} catch(e) {		
	}
	
	$(sltr).each(function() {
			$(this).Zebra_DatePicker({
						offset:[-$(this).width()-10,250],
						format:zFormat,
						view:'days',
				});
			$(this).css("background-image","none");
		});
	
}
</script>
<?php
}
?>
