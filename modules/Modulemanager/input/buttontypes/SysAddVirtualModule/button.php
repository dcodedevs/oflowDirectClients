<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&".(isset($_GET['moduleID'])?"moduleID=".$_GET['moduleID']."&":'')."module=".$buttonSubmodule."&includefile=virtual_module";?>" class="mm-add-virtual-module script" role="menuitem"><?php echo $buttonsArray[1];?></a>
<script type="text/javascript">
$(function(){
	$('.mm-add-virtual-module').off('click').on('click', function(e){
		e.preventDefault();
		fw_loading_start();
		$.ajax({
			type: 'POST',
			cache: false,
			dataType: 'json',
			url: $(this).attr('href'),
			data: { fwajax: 1, fw_nocss: 1 },
			success: function(json) {
				$('#popupeditboxcontent').html(json.data.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		}).fail(function() {
			sendEmail_alert("<?php echo $formText_ErrorOccurredProcessingRequest_Output;?>", "danger");
			fw_loading_end();
		});
	});
});
</script>