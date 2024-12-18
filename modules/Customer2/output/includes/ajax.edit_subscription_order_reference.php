<?php
$o_query = $o_main->db->query("SELECT * FROM subscriptionmulti WHERE id = '".$o_main->db->escape_str($_POST['subscriptionmultiId'])."'");
$v_collectingorder = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['subscriptionmultiId']) && $_POST['subscriptionmultiId'] > 0)
		{
			if($_POST['defaultInvoiceReferenceChoice'] < 2){
				$_POST['reference'] = '';
			} else if($_POST['defaultInvoiceReferenceChoice'] == 2){
				if(trim($_POST['reference']) == ''){
					$_POST['reference'] = 'empty';
				}
			}
			$s_sql = "UPDATE subscriptionmulti SET
			updated = NOW(),
			updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
			reference = '".$o_main->db->escape_str($_POST['reference'])."'
			WHERE id = '".$o_main->db->escape_str($_POST['subscriptionmultiId'])."'";
			$o_main->db->query($s_sql);
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_collectingorder['customerId'];
		return;
	}
}

$s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.id = ? AND customer_subunit.content_status = 0";
$o_query = $o_main->db->query($s_sql, array($v_collectingorder['customer_subunit_id']));
$subunit = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($v_collectingorder['customerId']));
$customerData = ($o_query ? $o_query->row_array() : array());


?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_subscription_order_reference";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="subscriptionmultiId" value="<?php print $_POST['subscriptionmultiId'];?>">

	<div class="inner">

		<div class="line">
			<div class="lineTitle"><?php echo $formText_DefaultInvoiceReferenceChoice_output; ?></div>
			<div class="lineInput">
				<?php if($subunit) {?>
					<input type="radio" value="1" autocomplete="off" name="defaultInvoiceReferenceChoice" <?php if($v_collectingorder['reference'] == "") echo 'checked';?> class="defaultInvoiceReferenceChooser" id="subunitSourceRef"/><label for="subunitSourceRef"><?php echo $formText_UseFromSubunit_output;?></label>
				<?php } else { ?>
					<input type="radio" value="0" autocomplete="off" name="defaultInvoiceReferenceChoice" <?php if($v_collectingorder['reference'] == "") echo 'checked';?> class="defaultInvoiceReferenceChooser" id="customerSourceRef"/><label for="customerSourceRef"><?php echo $formText_UseFromCustomerCard_output;?></label>
				<?php } ?>
				<input type="radio" value="2" autocomplete="off" name="defaultInvoiceReferenceChoice" <?php if($v_collectingorder['reference'] != "") echo 'checked';?> class="defaultInvoiceReferenceChooser" id="localSourceRef"/><label for="localSourceRef"><?php echo $formText_SpecifyHere_output;?></label>
			</div>
			<div class="clear"></div>
		</div>

		<div class="line">
			<div class="lineTitle"><?php echo $formText_InvoiceReference_output ?></div>
			<div class="lineInput defaultInvoiceReferenceLocal">
				<input type="text" class="popupforminput botspace" name="reference" value="<?php if($v_collectingorder['reference'] != 'empty') echo $v_collectingorder['reference']; ?>" autocomplete="off">
			</div>
			<div class="lineInput defaultInvoiceReferenceCustomer">
				<?php echo $customerData['defaultInvoiceReference'];?>
			</div>
			<?php if($subunit){ ?>
				<div class="lineInput defaultInvoiceReferenceSubunit">
					<?php echo $subunit['reference'];?>
				</div>
			<?php } ?>
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

	$(".defaultInvoiceReferenceChooser").off("change").on("change", function(){
        if($(".defaultInvoiceReferenceChooser:checked").val() == 0){
            $(".defaultInvoiceReferenceLocal").hide();
			$(".defaultInvoiceReferenceSubunit").hide();
			$(".defaultInvoiceReferenceCustomer").show();
        } else if($(".defaultInvoiceReferenceChooser:checked").val() == 1){
            $(".defaultInvoiceReferenceLocal").hide();
			$(".defaultInvoiceReferenceCustomer").hide();
			$(".defaultInvoiceReferenceSubunit").show();
		} else {
            $(".defaultInvoiceReferenceCustomer").hide();
			$(".defaultInvoiceReferenceSubunit").hide();
			$(".defaultInvoiceReferenceLocal").show();
        }
    }).change();
});
</script>
