<?php

$s_sql = "SELECT * FROM contactperson WHERE id = '".$o_main->db->escape_str($_POST['contactpersonId'])."'";
$o_query = $o_main->db->query($s_sql);
$contactperson = $o_query ? $o_query->row_array() : array();
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{

		if($contactperson){
			if($_POST['subscriberlistId'] > 0){

				$s_sql = "SELECT * FROM sys_email_subscriber WHERE email = '".$o_main->db->escape_str($contactperson['email'])."' AND subscriberlist_id = '".$o_main->db->escape_str($_POST['subscriberlistId'])."'";
				$o_query = $o_main->db->query($s_sql);
				$subscriberlistEntry = $o_query ? $o_query->row_array() : array();
				if($subscriberlistEntry) {
					$fw_error_msg = array($formText_ReceiverWithSuchEmailAlreadyExists_output." ".$contactperson['email']);
					return;
				} else {
					$s_sql = "INSERT INTO sys_email_subscriber SET
					created = now(),
					createdBy= ?,
					name= ?,
					last_name = ?,
					email = ?,
					subscriberlist_id = ?,
					subscriber_signup_date = NOW(),
					subscriber_signup_info = ?";
					$o_main->db->query($s_sql, array($variables->loggID, $contactperson['name'],$contactperson['lastname'],$contactperson['email'], $_POST['subscriberlistId'], $formText_AddedBy_output." ".$variables->loggID));

					$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
					return;
				}
			} else {
				$fw_error_msg = array($formText_MissingSubscriberlistId_output);
				return;
			}
		} else {
			$fw_error_msg = array($formText_MissingContactpersonId_output);
			return;
		}
	}
}

?>
<div class="popupform">
    <div id="popup-validate-message2" style="display:none;"></div>
<form class="output-form2" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=cp_copy_to_newsletter";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="contactpersonId" value="<?php echo $_POST['contactpersonId'];?>">

	<div class="inner">
        <div class="popupformTitle"><?php echo $formText_CopyContactPersonToNewsletterSubscriberlist_output;?></div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_SubscriberList_Output; ?></div>
            <div class="lineInput">
				<?php
				$s_sql = "SELECT * FROM email_subscriber_list WHERE content_status < 2 AND (deleted is null OR deleted = 0) ORDER BY name ";
				$o_query = $o_main->db->query($s_sql);
				$subscribersList = $o_query ? $o_query->result_array() : array();

				?>
				<select name="subscriberlistId">
					<option value=""><?php echo $formText_Select_output?></option>
					<?php
					foreach($subscribersList as $subscriberList){
						?>
						<option value="<?php echo $subscriberList['id'];?>"><?php echo $subscriberList['name']?></option>
						<?php
					}
					?>
				</select>
			</div>
            <div class="clear"></div>
        </div>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_output?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Copy_Output; ?>">
	</div>
</form>
</div>
<style>
#popup-validate-message2 {
	font-weight: bold;
	color: #c11;
	padding-bottom: 10px;
}
.popupeditbox .popupformbtn button.deleteContactPerson {
	background-color: #fff;
	color: #194273;
	border: 1px solid #194273;
}
.suggested_customer {
	color: #46b2e2;
	cursor: pointer;
}
.createNewCustomer {
	margin-left: 20px;
}
.copyContactpersonToNewsletter {
	float: right;
	color: #46b2e2;
	cursor: pointer;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form2").validate({
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
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message2").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
						fw_loading_end();
						$("#popup-validate-message2").show();
					} else {
						if(data.redirect_url !== undefined)
						{
							out_popup2.close();
						}
					}
				}
			}).fail(function() {
				$("#popup-validate-message2").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
				$("#popup-validate-message2").show();
				$('#popupeditbox2').css('height', $('#popupeditboxcontent2').height());
				fw_loading_end();
			});
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message2").html(message);
				$("#popup-validate-message2").show();
				$('#popupeditbox2').css('height', $('#popupeditboxcontent2').height());
			} else {
				$("#popup-validate-message2").hide();
			}
			setTimeout(function(){ $('#popupeditbox2').height(''); }, 200);
		}
	});
});
</script>
