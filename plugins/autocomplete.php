<script language='javascript'>
$(function() {
	$(frmID+" .autocomplete").parents("td").find("div.field_autocomplete").detach();

	$(frmID+" .autocomplete[src]").parents("td").append("<div style='float:right;' class='infield field_autocomplete' onclick='listAutoCompleteField(this)' title='Autocomplete Supported'></div>");
	initAutoComplete(frmID);
});
function initAutoComplete(frmid) {
	$(frmid+" input.autocomplete").each(function() {
			var minL=1;

			if($(this).attr("minlength")!=null) minL=parseInt($(this).attr("minlength"));

			if($(this).attr("src")!=null) {
				$(this).attr("src",$(this).attr("src")+"&site=<?=SITENAME?>");
				var href=$(this).attr("src")+"&format=json";
				$(this).autocomplete({
						minLength: minL,
						source:href,
						select: function( event, ui ) {
							fid="#"+$(event.target).parents(".LGKSFORMTABLE").attr("id");
							loadDataIntoForm(fid,ui.item);
							return true;
						}
					});
			} else if($(this).attr("callback")!=null) {
				var cb=$(this).attr("callback");
				$("input.autocomplete").autocomplete({
					minLength: minL,
					source:function(request, response) {
						response(window[cb](request));
					},
					select: function( event, ui ) {
						fid="#"+$(event.target).parents(".LGKSFORMTABLE").attr("id");
						loadDataIntoForm(fid,ui.item);
						return true;
					}
				});
			} else {
				if($(this).attr("name").length>0) {
					//var href="services/?scmd=autocomplete&id="+$(this).attr("name");
					var href=getServiceCMD("autocomplete")+"&id="+$(this).attr("name");
					$(this).autocomplete({
							minLength: minL,
							source:href,
							select: function( event, ui ) {
								fid="#"+$(event.target).parents(".LGKSFORMTABLE").attr("id");
								loadDataIntoForm(fid,ui.item);
								return true;
							}
						});
				}
			}
		});
}
function listAutoCompleteField(btn) {
	if($(btn).parents("tr").find(".autocomplete").attr("disabled")!=null) return;
	if($(btn).parents("tr").find(".autocomplete").length==1) {
		if($(btn).parents("tr").find(".autocomplete").attr("src").length>3) {
			field=$(btn).parents("tr").find(".autocomplete");
			s=field.attr("src")+"&format=selector";
			term=field.val();
			if(term==null) term="";
			else if(term.length>0) {
				s+="&term="+term;
			}
			processAJAXQuery(s,function(txt) {
				html="<input class='searchfield' type=text style='width:99%;height:23px;border:1px solid #aaa;padding:0px;' value='"+term+"' ";
				html+="onkeyup='updateAutocompleteListPoup(this,field);' />";
				html+="<select id=listAutoCompleter_123456 class='listAutoCompleter' style='width:99%;height:270px;background:white;border:1px solid #aaa;border-top:0px;' size=4 >";
				html+=txt;
				html+="</select>";
				jqPopupData(html,"Select",function(txt) {
						if(txt=="OK") {
							field.val($("#listAutoCompleter_123456").val());
							title=$("#listAutoCompleter_123456 option:selected").text();
							data=$("#listAutoCompleter_123456 option:selected").attr("data");
							if(data!=null) {
								item={};
								item=$.parseJSON(data);
								fid="#"+$(field).parents(".LGKSFORMTABLE").attr("id");
								loadDataIntoForm(fid,item);
							}
						}
					}).dialog({
							width:600,
							height:410,
							resizable:false,
						});
			});
		}
	}
}
function updateAutocompleteListPoup(fld,src) {
	s=src.attr("src")+"&format=selector";
	term=fld.value;
	if(term!=null && term.length>0) {
		s+="&term="+term;
	}
	$(fld).next("select").html("<option value='-1'>Loading ...</option>");
	//$(fld).next("select").load(s);
	processAJAXQuery(s,function(txt) {
		$(fld).next("select").html(txt);
	});
}
</script>
