var frmMode="insert";
var dateFormat="d/m/yy";
var timeFormat="h:m";
var showAMPM=false;
var yearRange='1950:2100';
var datefieldReadonly=true;

var onNewFunc=[];
var onEditFunc=[];
var onClearFunc=[];
var onSubmitFunc=[];
var onPreSubmitFunc=[];
var onPostSubmitFunc=[];

lastBtn=null;
lastId=-1;
function initForm(frmid) {
	$(frmID).find("tr").removeAttr("type");
	$(frmID).find("tr").removeAttr("title");
	$(frmID).find("tr").removeAttr("nullable");
	//$(frmID).find("tr").removeAttr("class");
	$(frmID).find("tr").removeAttr("btype");
	$(frmID).find("tr").removeAttr("style");
	    
	$(frmID+' .hidden').parents("tr").css("display","none");
	$(frmID+' input[type=hidden]').parents("tr").css("display","none");
	
	$(frmid+" .required").parents("td").find("div.field_required").detach();	
	$(frmid+" .required").parents("td").append("<div style='float:right;' class='field_required' title='Is A Required Field'></div>");
	
	$(frmid+" .readonly").attr("readonly","readonly");
	$(frmid+" .disabled").attr("disabled","disabled");	
	
	$(frmid+" .datetimefield").each(function() {
			$(this).attr("id",Math.ceil(Math.random()*100000000));
			$(this).datetimepicker({
					timeFormat:timeFormat,
					separator:' ',
					ampm:showAMPM,
					changeMonth:true,
					changeYear:true,
					showButtonPanel:true,
					yearRange:yearRange,
					dateFormat:dateFormat,
				});
		});
	$(frmid+" .datefield").each(function() {
			$(this).attr("id",Math.ceil(Math.random()*100000000));
			$(this).datepicker({
					changeMonth: true,
					changeYear: true,
					showButtonPanel: false,
					yearRange: yearRange,
					dateFormat:dateFormat,
				});
		});
	$(frmid+" .timefield").each(function() {
			$(this).attr("id",Math.ceil(Math.random()*1000000000));
			$(this).timepicker({
				timeFormat:timeFormat,
				ampm:showAMPM,
			});
		});
	if(datefieldReadonly) {
		$(frmid+" .datetimefield, "+frmid+" .datefield, "+frmid+" .timefield").attr("readonly","readonly");
	}
	
	$(frmid+" button:not(.nostyle)").button();	
	$(frmid+" select:not(.nostyle)").addClass("ui-state-default ui-corner-all");
	$(frmid+" select[multiple]").removeClass("ui-state-default");
	$(frmid+" select[size]").removeClass("ui-state-default");

	$(frmid+" .permanent").each(function() {
			if($(this).attr("value")!=null && $(this).attr("value").length>0) {
				$(this).attr("data-val",$(this).attr("value"));
			}
		});

	$(frmid).delegate("a[href]","click",function(e) {
		e.preventDefault();
		if($(this).hasClass("nopopup")) {
			openFormLink($(this).attr("href"),$(this).text(),false);
		} else {
			openFormLink($(this).attr("href"),$(this).text(),true);
		}
	});
}
function openFormLink(link,title,inNewPage) {
	if(inNewPage) {
		if(typeof parent.dpLink == "function") {
			parent.dpLink(title,link);//,search,btns,icon
		} else if(typeof dpLink == "function") {
			dpLink(title,link);//,search,btns,icon
		} else if(typeof parent.openInNewTab == "function") {
			parent.openInNewTab(title,link);
		} else {
			window.open(link,title);
		}
	} else {
		document.location=link;
	}
}
function loadData(frmid,divid) {
	loadForm(frmid,divid);
	loadDataTable(frmid,divid);
}

function initPanes(frmid) {
	$(frmid+" .accordion").accordion({
				fillSpace: true
			});
	$(frmid+" .tabs").tabs();
	$(frmid+" .pane").addClass("ui-widget-content");
}

function initDraggables(frmid) {
	$(frmid+" #draggable").draggable();
}

function emphasize(e) {
	if(typeof e=='object') {
		e.fadeOut('slow',function() {
			e.fadeIn('slow',function() {
				e.focus();
			});
		});		
	}	
}
function callOnDemandFuncs(vars,formID) {
	if(formID==null) {
		formID="#"+$(".LGKSFORMTABLE").attr("id");
	}
	$(vars).each(function(k,func) {
			try {
				callUserFunc(func,formID);
			} catch(e) {}
		});
}
function callUserFunc(func,txt) {
	if(func==null) return;
	if(typeof(func)=='function') func(txt);
	else window[func](txt);
}
//Form Tool Buttons
function checkClearMode() {
	if(frmMode!="insert") {
		msg="Do you want to just clear the form OR Create New Form?";
		lgksAlert(msg,"Clear Form").dialog("option",{
				buttons:{
					'Just Clear':function() {
						formReset(null);
						$(this).dialog("close");
					},
					'Create New':function() {
						formReset(null);
						createBlankForm();
						$(this).dialog("close");
					}
				}
			});
	} else {
		formReset(null);
	}
}
function formReset(btn) {
	$("div.field_unique").removeClass("field_check");
	$("div.field_unique").removeClass("field_error");
	$("div.field_unique").removeClass("field_warn");
	
	if(btn!=null) {
		$(".LGKSFORMTABLE").find("input.noautoreset,select.noautoreset").each(function() {
				v=$(this).val();
				$(this).attr("narval",v);
			});
	}
	$(".LGKSFORMTABLE").find("form").each(function() {
			this.reset();
		});
	if(btn!=null) {
		$(".LGKSFORMTABLE").find("input.noautoreset,select.noautoreset").each(function() {
				v=$(this).attr("narval");
				$(this).val(v);
				$(this).removeAttr("narval");
			});
	}
	$(".LGKSFORMTABLE").find("input.permanent[data-val]").each(function() {
			$(this).val($(this).attr("data-val"));
		});
	$(".LGKSFORMTABLE form select option:selected").removeAttr("selected");
	callOnDemandFuncs(onClearFunc,"#"+$(".LGKSFORMTABLE").attr("id"));
}
function formClone(btn) {
	if(frmMode=="insert") {
		lgksAlert("Clone works only in edit mode.");
		return;
	}
	callOnDemandFuncs(onPreSubmitFunc,"#"+$(".LGKSFORMTABLE").attr("id"));
	$("#"+$(".LGKSFORMTABLE").attr("id")).find("#data_id").val("0");
	frmMode="insert";
	formToDB(btn);
	callOnDemandFuncs(onSubmitFunc,"#"+$(".LGKSFORMTABLE").attr("id"));
}
function formSubmit(btn) {
	callOnDemandFuncs(onPreSubmitFunc,"#"+$(".LGKSFORMTABLE").attr("id"));
	if(formActionLink=="db") {
		formToDB(btn);
	} else if(formActionLink=="mail") {
		formMail(btn);
	} else if(formActionLink=="dbmail") {
		formToDB(btn);
	} else {
		formToDB(btn,formActionLink);
	}
	callOnDemandFuncs(onSubmitFunc,"#"+$(".LGKSFORMTABLE").attr("id"));
}
function formToDB(btn,lnk) {
	lastBtn=btn;
	formID=$(btn).parents(".LGKSFORMTABLE").attr("id");
	v=false;
	l=$("#"+formID+" form")
	.find("input[type=text], textarea, select")
	.filter(":enabled")
	.each(function() {
		if(this.value!=null && this.value.length>0 && this.name.length>0 && v!=true) {
			v=true;
		}
	});
	if(!v) {
		lgksAlert("No Fields Are Filled For Submiting.");
		return;
	}
	
	a=true;
	if(typeof window.validateForm == "function") {
		a=validateForm("#"+formID);
	}
	if(a) {
		if(lnk==null || lnk.length<=0) {
			if(formActionLink=="db") {
				lnk=toDBAddLink;
			} else if(formActionLink=="dbmail") {
				lnk=toDBMailLink;
			} else {
				lgksAlert("Sorry Form Adapter Is Not Supported. Contact Your Admin.");
				return;
			}
		}
		s=lnk+"&frmMode="+frmMode;
		
		AJAXSubmit("#"+formID,s,function(txt) {
				json=null;
				if(txt.trim().length>0) {
					if(txt.indexOf("Error::")>=0) {
						if(typeof lgksAlert=="function") lgksAlert(txt);
						else alert(txt);
						return;
					} else {
						try {
							json=$.parseJSON(txt);
						} catch(e) {
							if(typeof lgksAlert=="function") lgksAlert(txt);
							else alert(txt);
						}
					}
				}
				
				if(json!=null && txt.length>2) {
					if(json.msg.length>0) {
						msg1=json.msg;
					} else {
						msg1="";
					}
					if(msg1.length>0) {
						if(typeof lgksAlert=="function") lgksAlert(msg1);
						else alert(msg1);
					}
					if($("#"+formID+" form").find("input[type=file]").length>0 && json['idVal'].toString().length>0) {
						if(json['formTable'].length<=0 || json['idVal'].length<=0) {
							lgksAlert("Sorry, File Attachments Error.");
							formSubmitFinalization("Error::File Attachments Error");
						} else {
							uploadFiles(formID,json['formTable'],json['idVal'],json['idCol']);
							//Response Will Call The formSubmitFinalization Function
						}
					} else {
						formSubmitFinalization(txt);
					}
					callOnDemandFuncs(onPostSubmitFunc,"#"+$(".LGKSFORMTABLE").attr("id"));
				} else {
					if(txt.indexOf("Error::")<=0) {
						formSubmitFinalization("");
					}
				}
			});
	}
}
function formExport(btn) {
	//if(frmMode=="insert")
	formID=$(btn).parents(".LGKSFORMTABLE").attr("id");
	body=createExport("#"+formID,collectFormData("#"+formID,false));
	
	body="<head><title>Export Data</title></head>"+body;
	
	OpenWindow=window.open('','Export Data');
	OpenWindow.document.write(body);
}
function formPrint(btn) {
	formID=$(btn).parents(".LGKSFORMTABLE").attr("id");
	body=createExport("#"+formID,collectFormData("#"+formID,false));
	body="<div align=center class=noprint><button onclick='window.print();' style='width:100px;height:30px;'>Print</button></div>"+body;
	body+="<style media='print'>.noprint {display:none;}</style>";
	
	body="<head><title>Print Data</title></head>"+body;
	
	OpenWindow=window.open('','Print Data');
	OpenWindow.document.write(body);
}
function formMail(btn) {
	formID=$(btn).parents(".LGKSFORMTABLE").attr("id");
	a=true;
	if(typeof window.validateForm == "function") {
		a=validateForm("#"+formID);
	}
	if(a) {
		subject="Form Mail :: "+new Date();
		body=createMail("#"+formID,collectFormData("#"+formID,false));
		$.mailform("",subject,body,toMailLink);
	}
}
function deleteRecord(btn) {
	if($(frmID+" #formtoolbar #frm_btn_delete").length<=0) {
		lgksAlert("Deleting Is Not Allowed.");
		return false;
	}
	if(btn==null) return false;
	formID=$(btn).parents(".LGKSFORMTABLE").attr("id");
	AJAXSubmit("#"+formID,toDBDeleteLink,function(txt) {
			if(txt.indexOf("Error::")<0) {
				formReset(btn);
				createBlankForm(btn);
				reloadDataTable(btn);
			}
			if(txt.length>0) {
				lgksAlert(txt);
			}
		});
}
function createBlankForm(btn) {
	$("#data_id").val("0");
	$("#data_userid").val(data_userid); 
	setFormMode("new");
	callOnDemandFuncs(onNewFunc,"#"+$(".LGKSFORMTABLE").attr("id"));
}
function setFormMode(mode,recordNo) {
	msg="";
	if(mode=="new") {
		frmMode=defMode;
		msg="NEW";
	} else if(mode=="edit") {
		if(defMode=="insertonly") {
			frmMode="insert";
			msg="NEW";
			$($jqGrid).parents(".LGKSFORMTABLE").find("#data_id").val(lastId);
		} else {
			frmMode="update";
			msg="EDIT:"+recordNo;
		}
	} else {
		lgksAlert("Form Mode Error.Please Reload Page.");
	}
	$("#formModeDisp").html(msg);
}
//All Extra Functions
$dataGrid=null;
function loadForm(formID,dbID) {
	if(dbID.length>0 && formID.length>0 && $(formID).find("#data_id").val()>0) {
		lx=getServiceCMD("formaction")+"&action=load&format=json&frmID="+dbID;
		q=[];
		qx=[];
		
		l=$(formID).find("input[name], select[name], textarea[name]")
				.each(function() {
					k=$(this).attr("name");
					v=$(this).val();
					if(k=="userid" || k=="frmID") return;
					if(k=="submit_table" || k=="submit_wherecol") q.push(k+"="+encodeURIComponent(v));
					else qx.push(k);
					q.push(k+"="+encodeURIComponent(v));
				});
		
		q.push('frmCols'+"="+encodeURIComponent(qx.join(",")));
		processAJAXPostQuery(lx,q.join("&"),function(txt) {
			try {
				json=$.parseJSON(txt);
				loadDataIntoForm(formID,json);
			} catch(e) {
				if(txt!=null && txt.length>0) alert(txt);
				else alert(e);
			}
		});
	}	
}
function loadDataTable(formID,dbID) {
	if(formID.length>0) {
		if(typeof loadDataGrid == "function")
			loadDataGrid(formID,dbID);
	}
}
function loadDataIntoForm(formID,json) {
	if(json==null) return;
	if(json.data!=null) {
		$.each(json.data,function(key, val) {
			try {
				$("input[name="+key+"]",formID).val(val);
				$("select[name="+key+"]",formID).val(val);
				$("select[name="+key+"]",formID).attr("value",val);
				$("textarea[name="+key+"]",formID).val(val);
				$("input[name="+key+"]:not(select)",formID).html(val);
			} catch(e1) {
			}
		});
	} else if(json.Data!=null) {
		$.each(json.Data,function(key, val) {
			try {
				$("input[name="+key+"]",formID).val(val);
				$("select[name="+key+"]",formID).val(val);
				$("select[name="+key+"]",formID).attr("value",val);
				$("textarea[name="+key+"]",formID).val(val);
				$("input[name="+key+"]:not(select)",formID).html(val);
			} catch(e1) {
			}
		});
	}
	
	callOnDemandFuncs(onEditFunc,formID);
}
function collectFormData(id,hidden) {	
	var params = {};
	if(hidden) {
		l=$(id)
		.find("input[type=hidden]")
		.each(function() {
			params[ this.name || this.id || this.parentNode.name || this.parentNode.id ] = this.value;
		});
	}	
	l=$(id)
	.find("input[type!=radio][type!=file], textarea, select, input[type=radio]:checked")
	.filter(":enabled")
	.each(function() {
		name="";
		if($(this).parents("tr").find("td.columnName").length>0) {
			name=$(this).parents("tr").find("td.columnName").html();
		} else {
			name=this.name || this.id || this.parentNode.name || this.parentNode.id;
			name=name.toTitle();
		}
		if($(this).attr("type")=='checkbox') {
			if($(this).is(":checked")) params[name] = "true";
			else params[name] = "false";
		} else {
			params[name] = this.value;
		}
	});
	return params;
}
function createMail(id,params) {
	headText=$(id+" .formheader").html();
	body="";
	body+="<style>";
	body+=$("#toMailForm_css").html();
	body+="</style>";
	body+="Dear ,<br/><br/><br/><div class=mailcontainer>";
	body+="<table class=mailform align='center' width='100%' border='0' cellpadding='2' cellspacing='0'>";
	if(headText!=null) body+="<thead><tr><td colspan=100 align=center><strong>"+headText+"</strong></td></tr></thead><tbody>";	
	var i=0;
	$.each(params,function(key,val) {
			if(key.length>0) {
				if(i%2==0)
					body+= "<tr class='columnName even'><td width=200px align=right>";
				else
					body+= "<tr class='columnName odd'><td align=right>";
					
				body+="<b>"+key+"</b></td><td width=5px class=columnEqual align=center>:</td><td class='columnInput'>"+val+"</td></tr>";
				
				i++;
			}
		});
	body+="</tbody></table>";	
	body+="</div>";
	return body;
}
function createExport(id,params) {
	headText=$(id+" .formheader").html();
	body="";
	body+="<style>";
	body+=$("#toMailForm_css").html();
	body+="</style>";
	body+="<div class=mailcontainer>";
	body+="<table class=mailform align='center' width='100%' border='0' cellpadding='2' cellspacing='0'>";
	if(headText!=null) body+="<thead><tr><td colspan=100 align=center><strong>"+headText+"</strong></td></tr></thead><tbody>";	
	var i=0;
	$.each(params,function(key,val) {
			if(key.length>0) {
				if(i%2==0)
					body+= "<tr class='columnName even'><td width=200px align=right>";
				else
					body+= "<tr class='columnName odd'><td align=right>";
					
				body+="<b>"+key+"</b></td><td width=5px class=columnEqual align=center>:</td><td class='columnInput'>"+val+"</td></tr>";
				
				i++;
			}
		});
	body+="</tbody>";
	if($(id+" .formfooter").text()!=null && $(id+" .formfooter").text().length>0) {
		body+="<tfoot><tr><td colspan=10>"+$(id+" .formfooter").text()+"</td></tr></tfoot>";
	}
	body+="</table>";
	body+="</div>";
	return body;
}
function uploadFiles(id,forTable,forIDVal,forIDCol) {
	if($("#fileUploader_"+id).length>0) {
		frm=$("#fileUploader_"+id+" form");
		frm.html("");
		
		frm.append("<input name='forTable' type=hidden value='"+forTable+"' />");
		frm.append("<input name='forIDCol' type=hidden value='"+forIDCol+"' />");
		frm.append("<input name='forIDVal' type=hidden value='"+forIDVal+"' />");
		
		$("#"+id+" form").find("input[type=file]").filter(":enabled").each(function() {
				f=$(this).clone();
				td=$(this).parents("td");
				frm.append(this);
				td.html(f);
				f.css("opacity",1);
				if($(this).attr('src')==null)
					frm.append("<input name='"+$(this).attr('name')+"' type=hidden value='' />");
				else
					frm.append("<input name='"+$(this).attr('name')+"' type=hidden value='"+$(this).attr('src')+"' />");
			});
		frm.append("<input type=submit /><input type=reset />");
		frm.get(0).submit();
	}
}
function uploadComplete(msg1) {
	if(msg1.length>0) {
		if(typeof lgksAlert=="function") lgksAlert(msg1);
		else alert(msg1);
	}
	formSubmitFinalization(msg1);
}
function formSubmitFinalization(txt) {
	if(txt.indexOf("Error::")<0) {
		try {
			json=$.parseJSON(txt);
			txt="&"+json.idCol+"="+json.idVal;
		} catch(e) {
		}
		if(actionOnSubmit.length==0 || actionOnSubmit=="reload") {
			formReset(lastBtn);
			createBlankForm();
			if(typeof reloadDataTable=="function") reloadDataTable(lastBtn);
		} else if(actionOnSubmit=="reloadFrame") {
			document.location.reload();
		} else {
			if(actionOnSubmit.indexOf("msg#")==0) {
				msg=actionOnSubmit.substr(4);
				if(msg.length<=0) msg="<h3>"+txt+"</h3>";
				s="<div class='formaftermsg ui-widget-content'>"+msg+"</div>";
				$(".formholder").html(s);
			} else if(actionOnSubmit.indexOf("goto#")==0) {
				lnk=actionOnSubmit.substr(5);
				if(lnk.length<=0) {
					formReset(lastBtn);
					createBlankForm();
					if(typeof reloadDataTable=="function") reloadDataTable(lastBtn);
					return;
				}
				if(!(lnk.substr(0,7)=="http://" || lnk.substr(0,8)=="https://")) {
					lnk=SiteLocation+lnk;
				}
				if(lnk.indexOf("site")>0) lnk=lnk+txt;
				else {
					lnk=lnk+"&site="+SITENAME+txt;
				}
				window.location=lnk;
			} else if(actionOnSubmit.indexOf("js#")==0) {
				func=actionOnSubmit.substr(3);
				if(func.length<=0) return;
				if(typeof(func)=='function') func(txt);
				else window[func](txt);
				
				formReset(lastBtn);
				createBlankForm();
				if(typeof reloadDataTable=="function") reloadDataTable(lastBtn);
			}
		}
	} else {
		if(typeof lgksAlert=="function") lgksAlert(txt);
		else alert(txt);
	}
}
function previewFile(u,nm) {
	//lgksAlert(u+" "+nm);
	if(typeof lgksOverlayFrame=="function") lgksOverlayFrame(u,nm);
	else window.open(u,nm);
}
function showHelpInfo() {
	$(".helpinfo").toggle("slide");
}
