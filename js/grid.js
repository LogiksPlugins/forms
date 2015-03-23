//$jqGrid=null;
rptSearchOpts={
		multipleSearch:true,
		multipleGroup:false,
		showQuery:false,
		modal:true,
		caption:"Search Data",
		sopt:['eq','ne','lt','le','gt','ge','bw','bn','ew','en','cn','nc','nn','nu','in','ni'],
	};
jqColumns={
	"colModel": [
				{name:'id',index:'id', width:60},
			],
	};
grid_less_height=20;
splitterConfig={};
rptOptions={
		loadonce:false,
		rownumbers:false,
		rownumWidth:40,
		scroll:false,
		multiselect:false,

		altRows:true,
		altclass:'ui-priority-secondary',

		grouping:false,
		groupField:[],

		groupDataSorted:false,
		groupCollapse:true,
		groupColumnShow:[true],
		groupText:["<b style='text-transform:capitalize;color:#2E70FF;'>{0} - {1} Item(s)</b>"],
		groupSummary:[false],
		showSummaryOnHide:true,

		cellEdit:false,

		footerSummary:false,
		filterToolbar:false,
		filterSearchOnEnter:true,

		autowidth:true,
		forceFit:true,
		shrinkToFit:true,

		gridview:false,
		viewrecords:true,

		sortname:'',
		sortorder:"asc",

		rowNum:30,
		rowList:[10,30,60,100,250,500,1000,2500,5000,10000,25000],

		//readsrc:'services/?scmd=datagrid&action=load&datatype=json&sqlsrc=dbtable&sqltbl=do_forms',
		//editsrc:'services/?scmd=datagrid&action=edit&datatype=json&frm=forms',

		extraToolbar:false,

		actOnDblclick:true,
	};
function loadDataGrid(formID,dbID) {
	if($(formID+"_grid_table").length<=0) {
		return;
	}
	if(splitterConfig.type=='h') {
		h=275;
	} else {
		//h=$(formID).parent().height();
		h=$(window).height()-$(".ui-tabs-nav").height()
		//$(window).children().each(function() {
		//	h=$(this).height();
		//});
		hx=65;

		if(rptOptions.filterToolbar) {
			hx+=23;
		}
		if(rptOptions.footerSummary) {
			hx+=23;
		}
		if(rptOptions.extraToolbar) {
			hx+=23;
		}
		h=h-hx-grid_less_height;
	}
	if(h<0) h="100%";
	if(rptOptions.rowList==null || rptOptions.rowList.length<=0) {
		rptOptions.rowList=[10,30,60,100,250,500,1000,2500,5000,10000,25000];
	}
	if(rptSearchOpts.sopt==null || rptSearchOpts.sopt.length<=0) {
		rptSearchOpts.sopt=['eq','ne','lt','le','gt','ge','bw','bn','ew','en','cn','nc','nn','nu','in','ni'];
	}

	//alert(rptOptions.editsrc+'&id='+dbID);
	$(formID+"_grid_table").addClass("datagrid");
	$jqGrid=$(formID+"_grid_table").jqGrid({
			url:dataSource,//rptOptions.readsrc+'&sqlid='+dbID,
			editurl:rptOptions.editsrc+'&id='+dbID,
			datatype:"json",
			mType:"POST",
			colNames:jqColumns["colNames"],
			colModel:jqColumns["colModel"],
			loadonce:rptOptions.loadonce,
			rowNum:rptOptions.rowNum,
			rowList:rptOptions.rowList,
			rownumbers:rptOptions.rownumbers,
			rownumWidth:rptOptions.rownumWidth,
			autowidth:rptOptions.autowidth,
			scroll:rptOptions.scroll,
			forceFit:rptOptions.forceFit,
			shrinkToFit:rptOptions.shrinkToFit,
			height:h,

			gridview:rptOptions.gridview,
			viewrecords:rptOptions.viewrecords,
			pager: formID+"_grid_pager",
			sortname:rptOptions.sortname,
			sortorder:rptOptions.sortorder,
			multiselect:rptOptions.multiselect,
			beforeRequest:function() {
				//console.log($(this));
			},
			loadError:function(xhr,st,err) {
				//alert("Type: "+st+"; Response: "+ xhr.status + " "+xhr.statusText);
				//console.log("Ajax Error :: "+xhr.responseText);
				gridInfoDialog("Ajax Error","Type: "+st+"; Response: "+ xhr.status + " "+xhr.statusText);
			},
			loadComplete:function(txt) {
				if(txt.MSG.length>0)  {
					if(typeof lgksAlert == "function") lgksAlert(txt.MSG);
					else alert(txt.MSG);
				}
			},
			gridComplete: function(){
				var ids = $jqGrid.getDataIDs();
				for(var i=0;i < ids.length;i++){
					var cl = ids[i];
					be = "<input style='height:22px;width:20px;' type='button' value='E' onclick=\"$jqGrid.editRow('"+cl+"');\"  />";
					se = "<input style='height:22px;width:20px;' type='button' value='S' onclick=\"$jqGrid.saveRow('"+cl+"');\"  />";
					ce = "<input style='height:22px;width:20px;' type='button' value='C' onclick=\"$jqGrid.restoreRow('"+cl+"');\" />";
					$jqGrid.setRowData(ids[i],{act1:be+se+ce});
				}
				if(typeof updateGridDataForm=="function") {
					updateGridDataForm();
				}
			},
			cellsubmit:'remote',
			cellEdit:rptOptions.cellEdit,

			altRows:rptOptions.altRows,
			altclass:rptOptions.altclass,

			//caption:"JSON Example",
			footerrow:rptOptions.footerSummary,
			userDataOnFooter:rptOptions.footerSummary,

			grouping:rptOptions.grouping,
			groupingView : {
				groupField:rptOptions.groupField,
				groupDataSorted:rptOptions.groupDataSorted,
				groupCollapse:rptOptions.groupCollapse,
				groupColumnShow:rptOptions.groupColumnShow,
				groupText:rptOptions.groupText,
				groupSummary:rptOptions.groupSummary,
				showSummaryOnHide:rptOptions.showSummaryOnHide,
			},

			ondblClickRow: function(rowid) {
					if(rptOptions.actOnDblclick) {
						editRecord();
					}
				},
		});
	$jqGrid.navGrid(formID+"_grid_pager",
			{view:false,edit:false,add:false,del:false},
			{},//add
			{},//edit
			{},//delete
			rptSearchOpts);

	if(rptOptions.filterToolbar) {
		$jqGrid.filterToolbar({stringResult: true,searchOnEnter : rptOptions.filterSearchOnEnter});
	}
}
function gridAction(eleRecord,cmd) {
	if(cmd=="-" || cmd=="*") return;
	
	id=$(eleRecord).parents("tr").attr("id");

	if(cmd=="edit") editRecord($(frmID+" #formtoolbar #frm_btn_edit").get(0));
	else if(cmd=="delete") deleteRecord($(frmID+" #formtoolbar #frm_btn_delete").get(0));
	else {
		u=cmd;
		nm="DataView";
		if($(eleRecord).find("option:selected").length>0) {
			nm=$(eleRecord).find("option:selected").text();
		}
		u=u.replace("#id#",id);
		if(typeof lgksOverlayFrame=="function") {
			lgksOverlayFrame(u,nm).dialog({
				close:function() {
					reloadDataTable(eleRecord);
				}
			});
		} else if(typeof openInNewTab=="function") {
			openInNewTab(nm,u);
		}
		else window.open(u,nm);
	}
	$(eleRecord).val('*');
	//alert(cmd);
}
function reloadDataTable(btn) {
	if(btn!=null) {
		//formID=$(btn).parents(".LGKSFORMTABLE").attr("id");
		//$jqGrid=$allGrids['#'+formID];
		if($(btn).parents(".LGKSFORMTABLE").find(".datagrid").length<=0) return;

		did=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").attr("id");
		if(typeof $("#"+did).jqGrid=="function") $("#"+did).jqGrid("setGridParam",{ search: false, postData: { "filters": ""}});
		//$("#"+did).trigger("reloadGrid");
		$("#"+did).parents(".formDataTable").find(".pager .ui-icon-refresh").click();
	}
}
function searchDataTable(btn) {
	//if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	//if($jqGrid==null) return;
	//$jqGrid.searchGrid(rptSearchOpts);
	if(btn!=null) {
		did=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").attr("id");
		$("#"+did).parents(".formDataTable").find(".pager .ui-icon-search").click();
	}
}
function colChange(btn) {
	if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	if($jqGrid==null) return;
	$jqGrid.columnChooser();
}
function editRecord(btn) {
	if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	if($jqGrid==null) return;

	var gsr = $jqGrid.getGridParam("selrow");
	id="#"+$($jqGrid).parents(".LGKSFORMTABLE").attr("id");
	lastId=$($jqGrid).parents(".LGKSFORMTABLE").find("#data_id").val();
	if(gsr){
		$jqGrid.GridToForm(gsr,id+"_form");
		callOnDemandFuncs(onEditFunc);
	} else {
		gridInfoDialog("Warning","Please select Row");
	}
	if($(frmID+" #formtoolbar #frm_btn_edit").length<=0) {
		setFormMode("new",gsr);
		$($jqGrid).parents(".LGKSFORMTABLE").find("#data_id").val(lastId);
	} else {
		setFormMode("edit",gsr);
	}
}
function viewRecord(btn) {
	if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	if($jqGrid==null) return;
	var gsr = $jqGrid.getGridParam("selrow");
	id="#"+$($jqGrid).parents(".LGKSFORMTABLE").attr("id");
	if(gsr){
		$jqGrid.viewGridRow(gsr);
	} else {
		gridInfoDialog("Warning","Please select Row");
	}
}
function printGrid(btn) {
	if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	if($jqGrid==null) return;
	id="#"+$($jqGrid).parents(".LGKSFORMTABLE").attr("id")+"_grid_table";
	html=getHTMLForGrid(id);
	html="<div align=center class=noprint><button onclick='window.print();' style='width:100px;height:30px;'>Print</button></div>"+html;
	OpenWindow=window.open('','Print Grid');
	OpenWindow.document.write(html);
	//OpenWindow.window.print();
}
function exportToExcel(btn) {
	if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	if($jqGrid==null) return;
	id="#"+$($jqGrid).parents(".LGKSFORMTABLE").attr("id")+"_grid_table";
	html=getCSVForGrid(id);
	id=createForm("services/?scmd=export&type=download&format=csv&src=csv",html);
	$("form#"+id).submit();
}
function exportToHTML(btn) {
	if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	if($jqGrid==null) return;
	id="#"+$($jqGrid).parents(".LGKSFORMTABLE").attr("id")+"_grid_table";
	html=getHTMLForGrid(id);
	id=createForm("services/?scmd=export&type=download&format=html&src=html",html);
	$("form#"+id).submit();
}
function mailGrid(btn) {
	if(btn!=null) $jqGrid=$(btn).parents(".LGKSFORMTABLE").find(".datagrid").jqGrid();
	if($jqGrid==null) return;
	id="#"+$($jqGrid).parents(".LGKSFORMTABLE").attr("id")+"_grid_table";
	html=getHTMLForGrid(id);
	$.mailform("","Report Grid "+new Date(),html);
}

function resizeSplits(e) {
	w=$(e).find(".leftdiv").width();
	if(w!=null && $jqGrid!=null) {
		$jqGrid.setGridWidth(w);
	}
}
function resizeHSplits(e) {
	h=$(e).find(".leftdiv").height()-80;
	if(h!=null && $jqGrid!=null) {
		$jqGrid.setGridHeight(h);
	}
}
function gridInfoDialog(title,msg) {
	$("#dialog_frm_1").detach();
	$("body").append("<div id=dialog_frm_1 title='"+title+"'>"+msg+"</div>");
	$("#dialog_frm_1").dialog();
}
