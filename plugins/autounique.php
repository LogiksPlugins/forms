<script language='javascript'>
$(function() {
	$(frmID+" .required").parents("td").find("div.field_unique").detach();
	
	$(frmID+" .unique").parents("td").append("<div style='float:right;' class='infield field_unique' onclick='checkUnique(this)' title='Must Be Unique'></div>");
	initAutoUniqueCheck(frmID);
});
function initAutoUniqueCheck(frmid) {
	$(frmid+" input.unique").blur(function() {
			btn=$(this).parents("tr").find(".field_unique");
			tbl=$(this).parents("table.formTable").find("input[name=submit_table]").val();
			term=$(this).val();
			name=$(this).attr("name");
			
			$(btn).removeClass("field_check");
		    $(btn).removeClass("field_error");
		    $(btn).removeClass("field_warn");
			
			s="services/?scmd=formaction&action=unique";
			q="&tbl="+tbl+"&col="+name+"&term="+term;
			if(term.length>0) {
				$.ajax({
					  type: 'POST',
					  url: s,
					  data: q,
					  success: function(txt) {
						  if(txt.trim()=="unique") {
							  $(btn).addClass("field_check");
							  $(btn).attr("title","Selected Value Is Acceptable");
						  } else {
							  $(btn).addClass("field_error");
							  $(btn).attr("title","An Unique value is required");
						  }
					  },
					});				
			}
		});
}
function checkUnique(btn) {
	r=$(btn).parents("tr").find(".unique");
	if(r.length==1) {
		tbl=$(btn).parents("table.formTable").find("input[name=submit_table]").val();
		term=r.val();
		name=r.attr("name");
		s="services/?scmd=formaction&action=unique";
		q="&tbl="+tbl+"&col="+name+"&term="+term;
		$.ajax({
			  type: 'POST',
			  url: s,
			  data: q,
			  success: function(txt) {
				  $(btn).removeClass("field_check");
				  $(btn).removeClass("field_error");
				  $(btn).removeClass("field_warn");
				  if(frmMode=="insert") {
					  if(txt.trim()=="unique") {
						  $(btn).addClass("field_check");
					  } else {
						  $(btn).addClass("field_error");
					  }
				  } else {
					  if(txt.trim()=="unique") {
						  $(btn).addClass("field_error");
					  } else {
						  $(btn).addClass("field_check");
					  }
				  }
			  },
			});
	}
}
</script>
