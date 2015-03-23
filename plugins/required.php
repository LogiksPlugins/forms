<style>
.requiredspan {
	color: red;
	font-size: 18px;
	font-weight: bold;
}
</style>
<script>
$(function() {
	setTimeout(function() {
		$(".field_required").each(function() {
			td=$(this).closest("td");
			td=td.prev().prev();
			if(td!=null) {
				if(td.find(".requiredspan").length>0) return;
				html=td.html();
				td.html(html+" (<span class='requiredspan'>*</span>)");
				$(this).hide();
			}
			/*
			td=$(this).closest("tr").find("td.columnName");
			html=td.html();
			td.html(html+" (<span class='requiredspan'>*</span>)");
			$(this).hide();
			*/
		});
	}, 100);
});
</script>
