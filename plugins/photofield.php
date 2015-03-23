<style>
.imageholder {
	border-size:2px;
	height:150px;
	width:165px;
	overflow:hidden;
	cursor:pointer;
}
.photocol .photofldinfomsg {
	width:100%;height:25px;
	text-align:center;
	line-height:25px;
	cursor:pointer;
	background:#555;
	color:#FFF;
	opacity:0.7;
	display:none;
}
.imageholder img {
	margin:auto;
	margin-top:2px;
	width:95%;
	height:95%;
}
.loadingBG {
	background:transparent url(media/images/loading.gif) no-repeat center center;
}
</style>
<script language='javascript'>
photoField_onclick=null;
photoField_onchange=null;
photFldDlg=null;
$(function() {
	if($(frmID+' .photofield').length>1) {
		lgksAlert("More then one photos are not allowed in a single form.");
		return;
	}
	if($(frmID+" #formtoolbar #frm_btn_edit").length<=0) {
		return;
	}
	if($(frmID+' .photofield').length==1) {
		$(frmID+' tbody').delegate("td.photocol div.photofldinfomsg","click",function() {
				if($(frmID+" #data_id").val().length>0 && frmMode=="update") {
					src=$(this).parents("td").find(".imageholder").attr("src");
					rel=$(this).parents("td").find(".imageholder").attr("rel");
					uploadPhoto(src,rel);
				} else {
					lgksAlert("Sorry, Photos Can Be Uploaded In Edit Mode.");
				}
			});
		$(frmID+' tbody').delegate("td.photocol div.imageholder","dblclick",function() {
				if($(frmID+" #data_id").val().length>0 && frmMode=="update") {
					if(photoField_onclick!=null) {
						if(typeof photoField_onclick=="function") photoField_onclick("");
						else eval(photoField_onclick);
					} else {
						src=$(this).parents("td").find(".imageholder").attr("src");
						rel=$(this).parents("td").find(".imageholder").attr("rel");
						uploadPhoto(src,rel);
					}
				} else {
					lgksAlert("Sorry, Photos Can Be Uploaded In Edit Mode.");
				}
			});
		$(frmID+' tbody').delegate("td.photocol div.imageholder","hover",function() {
				$(this).parents("td.photocol").find(".photofldinfomsg").toggle("fade");
			});
		registerPluginLoader(function() {
			$(frmID+' .photofield').parents("tr").css("display","none");
			
			src=$(frmID+' .photofield').attr("src");
			if(src==null || src.length<=0) {
				src="<?=_dbtable("photos")?>";
				$(frmID+' .photofield').attr("src",src);
			}
			
			n1=0;
			run=true;
			$(frmID+' tbody tr').each(function() {
					if(run) {
						if($(this).attr("class")==null || $(this).attr("class").length<=0) n1++;
					}
					if($(this).find("#frm_btn_reset").length>0) run=false;
				});
			if(n1>7) n=1000;
			else n=n1+1;
			s="<tr><td colspan=3></td><td class='photocol' width=165px rowspan="+n+"><div class='imageholder' src='"+src+"' align=center></div>";
			s+="<div class='photofldinfomsg'>Upload Photo</div></td></tr>";
			$(frmID+' tbody tr:first-child').before(s);
			
			photoField_onclick=$(frmID+' .photofield').attr("onclick");
			photoField_onchange=$(frmID+' .photofield').attr("onchange");
			
			$(frmID+' td.photocol>div.imageholder').hide();
			
			onEditFunc.push(updatePhotoFields);
			onClearFunc.push(clearPhotoFields);
			onNewFunc.push(newFormCreated);
		});
	}
});
function newFormCreated() {
	clearPhotoFields();
	$(frmID+' td.photocol>div.imageholder').hide("blind");
}
function updatePhotoFields() {
	$(frmID+' td.photocol>div.imageholder').slideDown();
	src=$(frmID+' .photofield').attr("src");
	v=$(frmID+' .photofield').val();
	clearPhotoFields();
	if(v==null || v.length<=0) {
		return;
	}
	loadPhoto(v,src);
}
function clearPhotoFields() {
	$(frmID+' .imageholder[src='+src+']').attr("rel",0);
	$(frmID+' .imageholder[src='+src+']').html("");
	$(frmID+' .photocol .photofldinfomsg').html("Upload Photo");
	$(frmID+" .imageholder").removeClass("loadingBG");
}
function loadPhoto(id,src) {
	if(src==null) return;
	s=getServiceCMD("viewphoto")+"&loc=db&dbtbl="+src+"&eicon=user&image="+id;
	$(frmID+" .imageholder").addClass("loadingBG");
	sl="<img src='"+s+"' width=95% height=90% alt='' />";
	$(frmID+' .imageholder[src='+src+']').attr("rel",id);
	$(frmID+' .imageholder[src='+src+']').html(sl);
	$(frmID+' .photocol .photofldinfomsg').html("Change Photo");
}
function uploadPhoto(src,id) {
	if(id==null || id=="undefined") id=0;
	forTable=$(frmID+' input[name=submit_table]').val();
	forPhotoCol=$(frmID+' .photofield').attr("name");
	forIdCol=$(frmID+' input#data_id').attr('name');
	forIdVal=$(frmID+' input#data_id').val();
	
	lnk=_link("modules")+"&mod=photocrop&popup=true&func=updatePreview&src="+src+"&rel="+id;
	lnk+="&forTable="+forTable+"&forPhotoCol="+forPhotoCol+"&forIdCol="+forIdCol+"&forIdVal="+forIdVal;
	$("#photoFieldUploaderFrame iframe").attr("src",lnk);
	photFldDlg=lgksPopup("#photoFieldUploaderFrame","Upload Photo",{
			width:600,
			height:550,
			resizable:"none",
			buttons: {
				Close:function() {
					updatePhotoFields();
					$(this).dialog("close");
				},
			}
		});
}
function updatePreview(msg) {
	if(!isNaN(msg)) {
		photFldDlg.dialog("close");
		$(frmID+' .photofield').val(msg);
		if(typeof reloadDataTable=="function") reloadDataTable();
		updatePhotoFields();
		return;
	} else if(msg.length>0) lgksAlert(msg);
}
</script>
<div id=photoFieldUploaderFrame style='display:none;width:100%;overflow:hidden;margin:0px;padding:0px;' title='Upload Photo'>
	<iframe style='width:100%;height:100%;border:0px;' src='' frameborder=0></iframe>
</div>
