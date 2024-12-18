<div class="p_headerLine">
    <div class="top_filter_column">
    	<div class="addNewBtn btnStyle">
    		<div class="plusTextBox active">
    			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
    			<div class="text"><?php
                echo $formText_AddNew_Output;
                ?></div>
    		</div>
    		<div class="clear"></div>
    	</div>
	</div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
$(function(){
	$(".addNewBtn").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: 0
		};
		ajaxCall('add_autoreport', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
})
</script>
