
<div class="p_headerLine">
	<div class="addTable btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_AddTable_Output; ?></div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$(".addTable").off("click").on("click", function(e){
		e.preventDefault();
		var data = { };
	    ajaxCall('add_table_view', data, function(obj) {
	        $('#popupeditboxcontent').html('');
	        $('#popupeditboxcontent').html(obj.html);
	        out_popup = $('#popupeditbox').bPopup(out_popup_options);
	        $("#popupeditbox:not(.opened)").remove();
	    });
	})
})
</script>
