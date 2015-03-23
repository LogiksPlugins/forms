<script language='javascript'>
//It is possible to upload files directly as attachments to
//FS default userdata/attachments/
//DB default do_files
$(function() {
	//$(frmID+" input:file").uniform();
	registerPluginLoader(function() {
			refreshAllFileFields(frmID+" input:file");
			
			onEditFunc.push(updateAttachmentFields);
			onClearFunc.push(clearAttachmentFields);
		});
});
function updateAttachmentFields() {
	clearAttachmentFields();
	
	var gsr = $jqGrid.getGridParam("selrow");
	var d=$jqGrid.getRowData(gsr);
	$(frmID+" input[type=file]").each(function() {
			nm=$(this).attr("name");
			src=$(this).attr("src");
			if(nm.indexOf("[]")==nm.length-2) {
				nm=nm.substr(0,nm.length-2);
			}
			if(src==null) src="";
			
			if(d[nm]!=null && d[nm].length>0 && d[nm]!=",") {
				td=$(this).parent("td");
				$(this).attr("enabled",false);
				$(this).css("display","none");
				fld=td.find("input[type=file]").get(0).outerHTML;
				mns="<div class='infield field_minus frmbtn' onclick='removeAttachment(this)' title='Remove File' style='float:right;'></div>";
				if($(this).hasClass("multiple")) {
					arr=d[nm].split(",");
					td.html("");
					$.each(arr,function(k,v) {
							if(v.length>0 && v!="0") {
								t=createFileLink(nm,v,src);
								lnk="<div class='uploadedfilelink' nm='"+nm+"' src='"+src+"' rel='"+v+"'>"+t+mns+"</div>";
								td.append(lnk);
							}
						});
					td.append(fld);
					//td.find("input[type=file]").attr("enabled",true);
					//td.find("input[type=file]").css("display","inline-block");
				} else {
					v=d[nm];
					if(v.length>0 && v!="0") {
						t=createFileLink(nm,v,src);
						lnk="<div class='uploadedfilelink' nm='"+nm+"' src='"+src+"' rel='"+v+"'>"+t+mns+"</div>";
						td.html(lnk+fld);
					} else {
						td.html(fld);
						td.find("input[type=file]").attr("enabled",true);
						td.find("input[type=file]").css("display","block");
					}
				}
			}
		});
}
function createFileLink(nm,lnk,src) {
	t=lnk;
	
	if(t.indexOf("-")>1) {
		t=t.substr(t.indexOf("-")+1);
		t=t.replace("_"," ");
	} else if(!isNaN(t)) {
		t="File::"+t;
	}
	
	if(src==null || src.length<=0) src="fs#attachments/";
	if(src=="db#") src="db#<?=_dbtable("files")?>";
	
	if(src.indexOf("fs#")==0) {
		u="services/?scmd=viewfile&type=view&loc=local&file="+lnk;
		a="<a class='filepreviewlink' ondblclick=\"previewFile('"+u+"','"+nm+"')\">"+t+"</a>";
	} else if(src.indexOf("db#")==0) {
		tbl=src.substr(3);
		u="services/?scmd=viewfile&type=view&loc=dbfile&dbtbl="+tbl+"&file="+lnk;
		a="<a class='filepreviewlink' ondblclick=\"previewFile('"+u+"','"+nm+"')\">"+t+"</a>";
	} else {
		a="<a class='filepreviewlink' ondblclick=\"lgksAlert('Sorry, Source Not Supported')\">"+t+"</a>";
	}
	return a;
}
function clearAttachmentFields() {
	$(frmID+" input[type=file]").each(function() {
			a=this;
			td=$(this).parent("td");
			td.html("");
			td.append(a);
			
			$(this).removeAttr();
			$(this).css("display","");
		});
	refreshAllFileFields(frmID+" input:file");
}
function addFieldClone(btn) {
	td=$(btn).parents("td");
	fld=td.find("input,select,textarea");
	if(fld.length>0) {
		mns="<div class='field_minus frmbtn clone' onclick='removeFieldClone(this)' title='Remove Field'></div>";
		$(td).append(fld[0].outerHTML+mns);
	}
}
function removeFieldClone(btn) {
	$(btn).prev().detach();
	$(btn).detach();
}
function removeAttachment(btn) {
	td=$(btn).parents("td");
	nm=$(btn).parents('div.uploadedfilelink').attr("nm");
	pth=$(btn).parents('div.uploadedfilelink').attr("rel");
	src=$(btn).parents('div.uploadedfilelink').attr("src");
	
	tbl=$(frmID).find("input[name=submit_table]").val();
	idCol=$(frmID).find("input[name=submit_wherecol]").val();
	idVal=$(frmID).find("input[name="+idCol+"]").val();
	
	q="src="+src+"&name="+nm+"&path="+pth+"&forTable="+tbl+"&forIDCol="+idCol+"&forIDVal="+idVal;
	processAJAXPostQuery(
			"services/?scmd=attachments&action=delete",
			q,function(txt) {
				if(txt.length>0) {
					if(typeof lgksAlert=="function") lgksAlert(txt);
					else alert(txt);
				}
				if(txt.indexOf("Error:")<0) {
					$(btn).parents('div.uploadedfilelink').detach();
					reloadDataTable();
					if(td.find(".uploadedfilelink").length<=0) {
						fld=td.find("input[type=file]");
						fld.removeAttr();
						fld.css("display","");
						refreshAllFileFields(fld);
					}
				}
			}
		);
}
function refreshAllFileFields(flid) {
	$(flid).each(function() {
			if($(this).hasClass("multiple")) {
				nm=$(this).attr("name");
				if(nm.indexOf("[]")!=nm.length-2) {
					$(this).attr("name",nm+"[]");
				}
				$(this).parents("td").append("<div class='field_add frmbtn' onclick='addFieldClone(this)' title='Add More File Fields'></div>");
			}
		});
}
</script>
