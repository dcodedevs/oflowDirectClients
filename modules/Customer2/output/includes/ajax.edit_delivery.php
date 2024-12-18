<?php
$v_country = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_country[$v_item['countryID']] = $v_item['name'];
	}
}
$o_query = $o_main->db->query("SELECT * FROM customer_collectingorder WHERE id = '".$o_main->db->escape_str($_POST['collectingorderId'])."'");
$v_collectingorder = $o_query ? $o_query->row_array() : array();

if($v_collectingorder['delivery_date'] == '0000-00-00') $v_collectingorder['delivery_date'] = '';

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['collectingorderId']) && $_POST['collectingorderId'] > 0)
		{

			$s_sql = "UPDATE customer_collectingorder SET
			updated = NOW(),
			updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
			delivery_address_line_1 = '".$o_main->db->escape_str($_POST['delivery_address_line_1'])."',
			delivery_address_line_2 = '".$o_main->db->escape_str($_POST['delivery_address_line_2'])."',
			delivery_address_city = '".$o_main->db->escape_str($_POST['delivery_address_city'])."',
			delivery_address_postal_code = '".$o_main->db->escape_str($_POST['delivery_address_postal_code'])."',
			delivery_address_country = '".$o_main->db->escape_str($_POST['delivery_address_country'])."'
			WHERE id = '".$o_main->db->escape_str($_POST['collectingorderId'])."'";
			$o_main->db->query($s_sql);
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_collectingorder['projectId'];
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_delivery";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="collectingorderId" value="<?php print $_POST['collectingorderId'];?>">

	<div class="inner">

		<div class="line">
		<div class="lineTitle"><?php echo $formText_AddressLine1_Output; ?></div>
		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="delivery_address_line_1" value="<?php echo $v_collectingorder['delivery_address_line_1'];?>"></div>
		<div class="clear"></div>
		</div>

		<div class="line">
		<div class="lineTitle"><?php echo $formText_AddressLine2_Output; ?></div>
		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="delivery_address_line_2" value="<?php echo $v_collectingorder['delivery_address_line_2'];?>"></div>
		<div class="clear"></div>
		</div>

		<div class="line">
		<div class="lineTitle"><?php echo $formText_City_Output; ?></div>
		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="delivery_address_city" value="<?php echo $v_collectingorder['delivery_address_city'];?>"></div>
		<div class="clear"></div>
		</div>

		<div class="line">
		<div class="lineTitle"><?php echo $formText_PostalCode_Output; ?></div>
		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="delivery_address_postal_code" value="<?php echo $v_collectingorder['delivery_address_postal_code'];?>"></div>
		<div class="clear"></div>
		</div>

		<div class="line">
		<div class="lineTitle"><?php echo $formText_Country_Output; ?></div>
		<div class="lineInput"><select class="popupforminput botspace" autocomplete="off" name="delivery_address_country">
		<?php
		if($v_collectingorder['delivery_address_country'] == '') $v_collectingorder['delivery_address_country'] = 'no';
		foreach($v_country as $s_code => $s_name)
		{
			?><option value="<?php echo $s_code;?>"<?php echo ($s_code == $v_collectingorder['delivery_address_country'] ? ' selected':'');?>><?php echo $s_name;?></option><?php
		}
		?></select>
		</div>
		<div class="clear"></div>
		</div>

	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
	</div>
</form>
</div>
<style>

.popupform label.error {
	display: none !important;
}
.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					fw_loading_end();
					/*if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {*/
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
					//}
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
	$(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: 'dd.mm.yy',
		showButtonPanel: true,
		 closeText: 'Clear',
		 onClose: function (dateText, inst) {
			var event = arguments.callee.caller.caller.arguments[0];
			// If "Clear" gets clicked, then really clear it
			if ($(event.delegateTarget).hasClass('ui-datepicker-close')) {
				$(this).val('');
			}
		 }
	});
});
</script>
