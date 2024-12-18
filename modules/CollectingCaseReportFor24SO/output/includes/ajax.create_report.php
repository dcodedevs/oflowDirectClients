<?php
if($_POST['output_form_submit']) {
	$date = $_POST['date'];
	require_once("fnc_create_report_for_24so.php");
	ob_start();
	create_report_for_24so($date);
	$output =  ob_get_contents();
	// if($output != "") {
	// 	$fw_error_msg[] =$output;
	// }
	return;
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_report";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">

		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ReportDate_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datefield" autocomplete="off" name="date" value="<?php if($_POST['date'] != "0000-00-00" && $_POST['date'] != "") echo date("d.m.Y", strtotime($_POST['date'])); ?>" >
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_CreateReport_Output; ?>"></div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$("form.output-form").validate({
	submitHandler: function(form) {
		fw_loading_start();
		var formdata = $(form).serializeArray();
		var data = {};
		$(formdata).each(function(index, obj){
			data[obj.name] = obj.value;
		});

		$("#popup-validate-message").html("").hide();
		$.ajax({
			url: $(form).attr("action"),
			cache: false,
			type: "POST",
			dataType: "json",
			data: data,
			success: function (data) {
				fw_loading_end();
				if(data.error !== undefined)
				{
					$.each(data.error, function(index, value){
						var _type = Array("error");
						if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
						$("#popup-validate-message").append(value);
					});
					$("#popup-validate-message").show();
					fw_loading_end();
					fw_click_instance = fw_changes_made = false;
				} else {
					out_popup.addClass("close-reload-date").data("date", $(".datefield").val()).close();
				}
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
		});
	},
	invalidHandler: function(event, validator) {
		var errors = validator.numberOfInvalids();
		if (errors) {
			var message = errors == 1
			? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
			: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

			$("#popup-validate-message").html(message);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
		} else {
			$("#popup-validate-message").hide();
		}
		setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
	}
});
$(".datefield").datepicker({
	dateFormat: "d.m.yy",
	firstDay: 1
})
</script>
