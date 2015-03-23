<?php
loadModule("editor");
loadEditor("nicedit");
$fp=checkModule("editor");
$iconsPath=getWebpath($fp)."nicedit/nicEditorIcons.gif";
?>
<style>
textarea.richtextarea {
	width: 95%;height: 70px;
}
.nicEdit-pane select {
	width: auto;
}
</style>
<script language='javascript'>
$(function() {
	registerPluginLoader(function() {
		$(frmID+" textarea.richtextarea[id]").each(function() {
			id=$(this).attr("id");
			new nicEditor({
					fullPanel : false,
					iconsPath : '<?=$iconsPath?>',
					maxHeight:150,
					buttonList : ['save','fontSize','fontFamily','fontFormat','bold','italic','underline','left','center','right','justify','ol','ul','indent','outdent','image','link','unlink','forecolor','bgcolor','hr','removeformat','strikethrough','subscript','superscript','close','arrow'],
				}).panelInstance(id);
		});
		$(frmID+" .nicEdit-main").parent().css("background","white");
		$(frmID+" .nicEdit-main").parent().css("margin-bottom","5px");
		$(frmID+" .nicEdit-panelContain").parent().css("width","98%");
		$(frmID+" .nicEdit-panelContain").parent().css("overflow","hidden");
		$(frmID+" .nicEdit-main").parent().css("width","98%");
		$(frmID+" .nicEdit-main").css("width","99%");
	});
	onPreSubmitFunc.push(updateTextareasNic);
	onEditFunc.push(updateRichtextareaNic);
	onClearFunc.push(updateRichtextareaNic);
});
function updateTextareasNic() {
	$(frmID+" textarea.richtextarea[id]").each(function() {
			id=$(this).attr("id");
			$(this).val(nicEditors.findEditor(id).getContent());
		});
}
function updateRichtextareaNic() {
	$(frmID+" textarea.richtextarea[id]").each(function() {
			id=$(this).attr("id");
			v=$(this).val();
			if(v==null) v="";
			else v=v.replace(/\\"/g,'');
			nicEditors.findEditor(id).setContent(v);
		});
}
</script>