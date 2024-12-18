<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
$s_sql = "select * from customer_stdmembersystem_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $v_membersystem_config = $o_query->row_array();
}
$s_sql = "select * from customer_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $v_accountinfo = $o_query->row_array();
}

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['contactpersonId']) && $_POST['contactpersonId'] > 0)
		{
			if($_POST['door_access_code_type'] == 2)
			{
				$b_added = FALSE;
				foreach($_POST['intranet_membership_subscription_connections'] as $intranet_membership_subscription_connection)
				{
					if(empty($intranet_membership_subscription_connection)) continue;
					$b_added = TRUE;
				}
				if(!$b_added)
				{
					$fw_error_msg[] = $formText_AddAtLeastOneSubscription_Output;
					return;
				}
			}
			$s_sql = "UPDATE contactperson SET door_access_code_type = ? where id = ?";
			$o_query = $o_main->db->query($s_sql, array($_POST['door_access_code_type'], $_POST['contactpersonId']));
			
			$s_sql = "DELETE contactperson_doorcode_connection FROM contactperson_doorcode_connection WHERE contactperson_id = ?";
			$o_query = $o_main->db->query($s_sql, array($_POST['contactpersonId']));
			
			if($_POST['door_access_code_type'] == 2)
			{
				$b_added = FALSE;
				foreach($_POST['intranet_membership_subscription_connections'] as $intranet_membership_subscription_connection)
				{
					if(empty($intranet_membership_subscription_connection)) continue;
					$s_sql = "INSERT INTO contactperson_doorcode_connection SET contactperson_id = ?, subscriptionmulti_id = ?";
					$o_query = $o_main->db->query($s_sql, array($_POST['contactpersonId'], $intranet_membership_subscription_connection));
					$b_added = TRUE;
				}
				if(!$b_added)
				{
					$fw_error_msg[] = $formText_ChooseSubscription_Output;
				}
			}
    	}
		
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
		return;
	}
}

$b_is_activated = false;
if(isset($_POST['contactpersonId']) && $_POST['contactpersonId'] > 0)
{
	$s_sql = "select * from contactperson where id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['contactpersonId']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
	
	$contactPersonMembershipConnections = array();
	$s_sql = "select * from intranet_membership WHERE content_status < 2 ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql, array());
	$intranet_memberships = $o_query ? $o_query->result_array() : array();
	
	$s_sql = "select * from subscriptionmulti WHERE customerId = ? AND content_status < 2 ORDER BY subscriptionName ASC";
	$o_query = $o_main->db->query($s_sql, array($v_data['customerId']));
	$intranet_membership_subscriptions = $o_query ? $o_query->result_array() : array();
	
	if($v_data['door_access_code_type'] == 2)
	{
		$s_sql = "select * from contactperson_doorcode_connection where contactperson_id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_data['id']));
		$contactPersonMembershipSubscriptionConnections = $o_query ? $o_query->result_array() : array();
	}
}
?>
<div class="popupform">
<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form door-code" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_door_access_code_type";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="contactpersonId" value="<?php print $_POST['contactpersonId'];?>">
	<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">

	<div class="inner">

		<div class="line">
    		<div class="lineTitle"><?php echo $formText_DoorAccess_output; ?></div>
    		<div class="lineInput">
				<select class="intranet_membership_subscription_type" name="door_access_code_type" autocomplete="off" required>
					<option value="0"><?php echo $formText_None_Output;?></option>
					<option value="1" <?php if($v_data['door_access_code_type'] == 1) echo 'selected';?>><?php echo $formText_AnyCustomerSubscription_output;?></option>
					<option value="2" <?php if($v_data['door_access_code_type'] == 2) echo 'selected';?>><?php echo $formText_SpecifiedSubscriptions_output;?></option>
					<option value="3" <?php if($v_data['door_access_code_type'] == 3) echo 'selected';?>><?php echo $formText_NoSubscriptionNeeded_output;?></option>
				</select>
				<div class="membershipSubscriptionList" style="display: none;">
					<div class="listWrapper">
						<?php
						foreach($contactPersonMembershipSubscriptionConnections as $contactPersonMembershipSubscriptionConnection) {
							?>
							<div class="membershipSubscriptionConnectionRow">
								<select class="membershipSubscriptionConnectionSelect" name="intranet_membership_subscription_connections[]">
									<option value=""><?php echo $formText_Select_output;?></option>
									<?php
									foreach($intranet_membership_subscriptions as $intranet_membership_subscription) {
										?>
										<option value="<?php echo $intranet_membership_subscription['id']?>" <?php if($contactPersonMembershipSubscriptionConnection['subscriptionmulti_id'] == $intranet_membership_subscription['id']) echo 'selected';?>><?php echo $intranet_membership_subscription['subscriptionName'];?></option>
										<?php
									}
									?>
								</select>
								<span class="glyphicon glyphicon-trash removeMembershipSubscriptionConnectionSelect editEntryBtn"></span>
							</div>
							<?php
						}
						?>
					</div>
					<div class="addMembershipSubscriptionConnection editEntryBtn"><?php echo $formText_AddMembershipSubscriptionConnection_output;?></div>
					<div class="emptyMembershipSubscriptionConnection" style="display:none;">
						<select class="membershipSubscriptionConnectionSelect" name="intranet_membership_subscription_connections[]">
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php
							foreach($intranet_membership_subscriptions as $intranet_membership_subscription) {
								?>
								<option value="<?php echo $intranet_membership_subscription['id']?>"><?php echo $intranet_membership_subscription['subscriptionName'];?></option>
								<?php
							}
							?>
						</select>
						<span class="glyphicon glyphicon-trash removeMembershipSubscriptionConnectionSelect editEntryBtn"></span>
					</div>
				</div>
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
input[type="checkbox"][readonly] {
  pointer-events: none;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$(".intranet_membership_subscription_type").change(function(){
		if($(this).val() == 0 || $(this).val() == 1){
			$(".membershipSubscriptionList").hide();
		} else if($(this).val() == 2) {
			$(".membershipSubscriptionList").show();
		} else {
			$(".membershipSubscriptionList").hide();
		}
	}).change();
	$(".popupform .addMembershipConnection").off("click").on("click", function(){
		$(".popupform .membershipList .listWrapper").append($(".popupform .emptyMembershipConnection").clone().removeClass("emptyMembershipConnection").addClass("membershipConnectionRow").show());
		rebindButtons();
	})
	$(".popupform .addMembershipSubscriptionConnection").off("click").on("click", function(){
		$(".popupform .membershipSubscriptionList .listWrapper").append($(".popupform .emptyMembershipSubscriptionConnection").clone().removeClass("emptyMembershipSubscriptionConnection").addClass("membershipSubscriptionConnectionRow").show());
		rebindButtons();
	})
	rebindButtons();
	function rebindButtons(){
		$(".popupform .removeMembershipConnectionSelect").off("click").on("click", function(){
			$(this).parents(".membershipConnectionRow").remove();
		})
		$(".popupform .removeMembershipSubscriptionConnectionSelect").off("click").on("click", function(){
			$(this).parents(".membershipSubscriptionConnectionRow").remove();
		})
	}

	$("form.output-form.door-code").validate({
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
					$("#popup-validate-message").html('');
                    if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
					} else {
	                    if(data.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload");
	                        out_popup.close();
	                    }
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
});
</script>
