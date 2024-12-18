<?php
$o_query = $o_main->db->query("SELECT * FROM customer_subunit WHERE id = '".$o_main->db->escape_str($_POST['cid'])."'");
$v_collectingorder = $o_query ? $o_query->row_array() : array();

if($v_collectingorder['delivery_date'] == '0000-00-00') $v_collectingorder['delivery_date'] = '';
$v_country = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_country[$v_item['countryID']] = $v_item['name'];
	}
}

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($v_collectingorder)
		{
			$s_sql = "UPDATE customer_subunit SET
			updated = NOW(),
			updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
			name = '".$o_main->db->escape_str($_POST['name'])."',
			reference = '".$o_main->db->escape_str($_POST['reference'])."',
			delivery_address_line_1 = '".$o_main->db->escape_str($_POST['delivery_address_line_1'])."',
			delivery_address_city = '".$o_main->db->escape_str($_POST['delivery_address_city'])."',
			delivery_address_postal_code = '".$o_main->db->escape_str($_POST['delivery_address_postal_code'])."',
			delivery_address_country = '".$o_main->db->escape_str($_POST['delivery_address_country'])."',
			content_status = '".$o_main->db->escape_str($_POST['content_status'])."'
			WHERE id = '".$o_main->db->escape_str($v_collectingorder['id'])."'";
			$o_main->db->query($s_sql);
		} else {
            $s_sql = "INSERT INTO customer_subunit SET
			created = NOW(),
			createdBy = '".$o_main->db->escape_str($variables->loggID)."',
			name = '".$o_main->db->escape_str($_POST['name'])."',
			reference = '".$o_main->db->escape_str($_POST['reference'])."',
			delivery_address_line_1 = '".$o_main->db->escape_str($_POST['delivery_address_line_1'])."',
			delivery_address_city = '".$o_main->db->escape_str($_POST['delivery_address_city'])."',
			delivery_address_postal_code = '".$o_main->db->escape_str($_POST['delivery_address_postal_code'])."',
			delivery_address_country = '".$o_main->db->escape_str($_POST['delivery_address_country'])."',
			customer_id = '".$o_main->db->escape_str($_POST['customerId'])."',
			content_status = '".$o_main->db->escape_str($_POST['content_status'])."'";
			$o_main->db->query($s_sql);
        }

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_subunit";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">

	<div class="inner">
        <div class="popupformTitle"><?php echo $formText_EditSubunit_output;?></div>

		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Status_Output; ?></div>
    		<div class="lineInput">
				<select name="content_status" class="content_status_changer" autocomplete="off" class="popupforminput botspace">
					<option value="0"><?php echo $formText_Active_output;?></option>
					<option value="1" <?php if($v_collectingorder['content_status'] == 1) echo 'selected';?>><?php echo $formText_InActive_output;?></option>
				</select>
				<div class="inactiveWarning">
					<?php echo $formText_YouNeedToManuallyCheckAndEventuallyStopSubcontent_output;?>
				</div>
			</div>
    		<div class="clear"></div>
		</div>
        <div class="line">
    		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
    		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="name" value="<?php echo $v_collectingorder['name'];?>"></div>
    		<div class="clear"></div>
		</div>
        <div class="line">
    		<div class="lineTitle"><?php echo $formText_AddressLine1_Output; ?></div>
    		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="delivery_address_line_1" value="<?php echo $v_collectingorder['delivery_address_line_1'];?>"></div>
    		<div class="clear"></div>
		</div>

		<div class="line">
    		<div class="lineTitle"><?php echo $formText_City_Output; ?></div>
    		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="delivery_address_city" value="<?php echo $v_collectingorder['delivery_address_city'];?>" ></div>
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
		<div class="line">
    		<div class="lineTitle"><?php echo $formText_DefaultInvoiceReference_output; ?></div>
    		<div class="lineInput"><input type="text" class="popupforminput botspace" autocomplete="off" name="reference" value="<?php echo $v_collectingorder['reference'];?>"></div>
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
.inactiveWarning {
	display: none;
	margin-top: 10px;
	margin-bottom: 10px;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$(".content_status_changer").change(function(){
		if($(this).val() == 1){
			$(".inactiveWarning").show();
		} else {
			$(".inactiveWarning").hide();
		}
	})
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
});
</script>
