<?php
_js(array("jquery.multiselect","jquery.multiselect.filter"));
_css(array("jquery.multiselect","jquery.multiselect.filter"));
?>
<style>
button.ui-multiselect {
	width:430px !important;
	height:25px;
	padding:3px;padding-top:5px;
	margin-left:0px;
	margin-top:-10px;
	text-align:left;
}
button.ui-multiselect .ui-button-text {
	padding:0px;margin:0px;	
}
.ui-multiselect-header {
	height:22px;
}
.ui-multiselect-header.ui-helper-clearfix {
	overflow:hidden;
	padding-top:4px;
}
.ui-multiselect-header .ui-multiselect-filter {
	width:200px !important;
	font-size:12px;
}
.ui-multiselect-header .ui-multiselect-filter input {
	width:150px !important;
	margin-left:2px;
}
.ui-helper-reset a {
	font-size:12px;
}
.ui-multiselect-menu li>label.ui-state-hover {
	font-weight:plain;
	cursor:pointer;	
	border-radius:0px;	
}
button.ui-multiselect {
	font-weight: normal !important;
	font-size: 12px !important;
}
</style>
<script language='javascript'>
$(function() {
	registerPluginLoader(function() {
			w=430;
			minItem=10;
			header=true;
			sl=6;
			
			$(frmID+" select[multiple]").attr("class","");
			$(frmID+" select[multiple]").each(function() {
					if($(this).children().length<=3) header=false;
					m=$(this).multiselect({
						minWidth:w,
						header:header,
						selectedList:sl,
						position: {
							my: 'left top',
							at: 'left bottom'
						},
					});
					if($(this).children().length>minItem) {
						m.multiselectfilter();
					}
				});
			onEditFunc.push(updateMultiselectors);
			onClearFunc.push(clearMultiselectors);
		});
});
function updateMultiselectors() {
	try {
		var gsr = $jqGrid.getGridParam("selrow");
		var d=$jqGrid.getRowData(gsr);
		$(frmID+" select[multiple] option:selected").removeAttr("selected");
		$(frmID+" select[multiple]").each(function() {
				selector=$(this);
				nm=$(this).attr('name');
				nm=nm.replace("[]","");
				if(d[nm].length>0) {
					arr=d[nm].split(",");
					$.each(arr,function(k,v) {
							if(v.length>0)
								selector.find("option[value='"+v+"'],option:contains("+v+")").attr("selected",'selected');
						});
				}
			});
		$(frmID+" select[multiple]").multiselect('refresh');
	} catch(e) {
		$(frmID+" select[multiple]").each(function() {
			selector=$(this);
			if($(this).attr("value")!=null) {
				arr=$(this).attr("value").split(",");
				$.each(arr,function(k,v) {
					if(v.length>0)
						selector.find("option[value='"+v+"'],option:contains("+v+")").attr("selected",'selected');
				});
			}
		});
		$(frmID+" select[multiple]").multiselect('refresh');
	}
}
function clearMultiselectors() {
	$(frmID+" select[multiple] option:selected").removeAttr("selected");
	$(frmID+" select[multiple]").multiselect('refresh');
}
</script>
