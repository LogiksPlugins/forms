<script language='javascript'>
brwsDlg=null;
brwsFld=null;
defBaseDir="<?=APPS_MEDIA_FOLDER?>";
$(function() {
	$(frmID+' input.filebrowser, '+frmID+' input.photobrowser').attr("readonly","readonly");
	$(frmID+' input.filebrowser, '+frmID+' input.photobrowser').removeClass("readonly");
	$(frmID+' input.filebrowser, '+frmID+' input.photobrowser').addClass("readonly");
	$(frmID+' input.filebrowser, '+frmID+' input.photobrowser').css("background-color","#fff");
	
	btn="<div class='infield field_popup frmbtn' onclick='browseFile(this)' title='File/Photo Browser' style='float:right;'></div>";
	$(frmID+' input.filebrowser').parents("td").append(btn);
	$(frmID+' input.photobrowser').parents("td").append(btn);
});
function browseFile(btn) {
	//url="index.php?page=modules&site=<?=SITENAME?>&mod=fileselectors&popup=direct";
	//url="plugins/modules/fileselectors/index.php?popup=direct";
	
	brwsFld=$(btn).parents("td").find("input");
	src=brwsFld.attr("src");
	if(src!=null && src.length>0) baseDir=src;
	else baseDir=defBaseDir;
	if(baseDir==defBaseDir) {
		if(brwsFld.hasClass('filebrowser')) {
			url="index.php?page=modules&site=<?=SITENAME?>&mod=fileselectors&popup=direct&type=Files&action=js&func=closeBrowser&baseDir="+baseDir;
			brwsDlg=lgksOverlayFrame(url,"Browse");
		} else if(brwsFld.hasClass('photobrowser')) {
			url="index.php?page=modules&site=<?=SITENAME?>&mod=fileselectors&popup=direct&type=Images&action=js&func=closeBrowser&baseDir="+baseDir;
			brwsDlg=lgksOverlayFrame(url,"Browse");
		} else if(brwsFld.hasClass('docbrowser')) {
			url="index.php?page=modules&site=<?=SITENAME?>&mod=fileselectors&browse=dbdocs&popup=direct&type=Files&action=js&func=closeBrowser&src="+baseDir;
			brwsDlg=lgksOverlayFrame(url,"Browse");
		} else if(brwsFld.hasClass('mediabrowser')) {
			url="index.php?page=modules&site=<?=SITENAME?>&mod=fileselectors&browse=dbmedia&popup=direct&type=Images&action=js&func=closeBrowser&src="+baseDir;
			brwsDlg=lgksOverlayFrame(url,"Browse");
		}
	} else {
		url="index.php?page=modules&site=<?=SITENAME?>&mod=fileselectors&popup=direct&action=js&type=Others&func=closeBrowser&baseDir="+baseDir;
		brwsDlg=lgksOverlayFrame(url,"Browse");
	}
}
function closeBrowser(fl) {
	bp="<?=APPS_FOLDER.SITENAME."/"?>";
	if(fl.indexOf(bp)==0) {
		fl=fl.substr(bp.length);
	}
	if(brwsDlg!=null) brwsDlg.dialog("close");
	if(brwsFld!=null) brwsFld.val(decodeURIComponent(fl));
}
</script>
