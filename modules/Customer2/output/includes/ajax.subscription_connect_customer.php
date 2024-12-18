<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{

		if($_POST['customerId'] > 0){
			$subscriptionIds = isset($_POST['subscriptionIds']) ? $_POST['subscriptionIds'] : array();
			foreach($subscriptionIds as $subscriptionId) {
				$s_sql = "SELECT * FROM subscriptionmulti WHERE id = '".$o_main->db->escape_str($subscriptionId)."'";
				$o_query = $o_main->db->query($s_sql);
				$contactperson = $o_query ? $o_query->row_array() : array();

				if($contactperson){
					$s_sql = "UPDATE subscriptionmulti SET
					updated = now(),
					updatedBy= ?,
					customerId= ?
					WHERE id = ?";
					$o_main->db->query($s_sql, array($variables->loggID, $_POST['customerId'], $contactperson['id']));
				}
			}

			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
			return;
		} else {			
			$fw_error_msg = array($formText_MissingCustomer_output);
			return;
		}
	}
}
foreach($_POST['subscriptionId'] as $subscriptionId){
	$s_sql = "SELECT * FROM subscriptionmulti WHERE id = '".$o_main->db->escape_str($subscriptionId)."'";
    $o_query = $o_main->db->query($s_sql);
    $selectedSubscriptionmulti = $o_query ? $o_query->row_array() : array();
}
if((isset($selectedSubscriptionmulti['previous_customer_name']) && $selectedSubscriptionmulti['previous_customer_name'] != ""))
{
	if($selectedSubscriptionmulti['previous_customer_name'] != ""){
		$sql_where = " name LIKE '".$o_main->db->escape_str(mb_substr($selectedSubscriptionmulti['previous_customer_name'], 0, 3))."%'";
	}

	$s_sql = "SELECT * FROM customer WHERE customer.content_status < 2 AND (".$sql_where.")";
    $o_query = $o_main->db->query($s_sql);
    $suggestedCustomers = $o_query ? $o_query->result_array() : array();
}
?>
<div class="popupform">
    <div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=subscription_connect_customer";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<?php if(is_array($_POST['subscriptionId'])){
		foreach($_POST['subscriptionId'] as $subscriptionId){
			?>
			<input type="hidden" name="subscriptionIds[]" value="<?php print $subscriptionId;?>">
			<?php
		}
	} else { ?>
		<input type="hidden" name="subscriptionIds[]" value="<?php print $_POST['subscriptionId'];?>">
	<?php } ?>


	<div class="inner">
        <div class="popupformTitle"><?php echo $formText_ConnectSubscriptionsToCustomer_output;?></div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_Subscriptions_Output; ?></div>
            <div class="lineInput">
                <?php
				echo count($_POST['subscriptionId'])." ".$formText_SubscriptionsAreGettingConnected_output;
                ?>
            </div>
            <div class="clear"></div>
        </div>
        <div class="line">
            <div class="lineTitle"><?php echo $formText_Customer_Output; ?></div>
            <div class="lineInput">
                <?php
                $s_sql = "SELECT * FROM customer  WHERE customer.id = ?";
                $o_query = $o_main->db->query($s_sql, array($customerId));
                $customer = ($o_query ? $o_query->row_array() : array());

                if($customer) { ?>
                <a href="#" class="selectCustomer"><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></a>
                <?php } else { ?>
                <a href="#" class="selectCustomer"><?php echo $formText_SelectCustomer_Output;?></a>
                <?php } ?>
                <input type="hidden" name="customerId" id="customerId" value="<?php print $customer['id'];?>" required>
            </div>
            <div class="clear"></div>
        </div>

		<div class="line">
            <div class="lineTitle"><?php echo $formText_SuggestedCustomers_Output; ?></div>
            <div class="lineInput">
                <?php
			 	if(count($suggestedCustomers) > 0){
					foreach($suggestedCustomers as $suggestedCustomer) {

						$s_sql = "SELECT s.*, st.name as subscriptionTypeName FROM subscriptionmulti s
						JOIN subscriptiontype st ON st.id = s.subscriptiontype_id
						WHERE s.customerId = '".$o_main->db->escape_str($suggestedCustomer['id'])."' AND s.content_status < 2 AND  s.startDate < CURDATE() AND (s.stoppedDate is null OR s.stoppedDate = '0000-00-00' OR s.stoppedDate > CURDATE())";
						$o_query = $o_main->db->query($s_sql);
						$activeSubscriptions = ($o_query ? $o_query->result_array() : array());
						?>
						<div class="suggested_customer" data-customername="<?php echo $suggestedCustomer['name']." ".$suggestedCustomer['middlename']." ".$suggestedCustomer['lastname'];?>" data-customerid="<?php echo $suggestedCustomer['id'];?>">
							<?php echo $suggestedCustomer['name']." ".$suggestedCustomer['middlename']." ".$suggestedCustomer['lastname'];?>
							<?php if(count($activeSubscriptions) > 0) {
								echo ' - ';
								$subscriptionTypeNames = array();
								foreach($activeSubscriptions as $activeSubscription) {
									$subscriptionTypeNames[] = $activeSubscription['subscriptionTypeName'];
								}
								echo implode(", ", $subscriptionTypeNames);
							}?>
						</div>
						<?php
					}
				} else {
					echo $formText_NoneFound_output;
				}
				?>
            </div>
            <div class="clear"></div>
        </div>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_output?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Connect_Output; ?>">
	</div>
</form>
</div>
<style>
.suggested_customer {
	color: #46b2e2;
	cursor: pointer;
	padding: 2px 0px;
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
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
						fw_loading_end();
						$("#popup-validate-message").show();
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
    $(".selectCustomer").unbind("click").bind("click", function(e){
        e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
                fw_loading_end();
            }
        });
    })

	$(".suggested_customer").off("click").on("click", function(e){
        e.preventDefault();
		var customerId = $(this).data("customerid");
		var customerName = $(this).data("customername");
		$(".output-form #customerId").val(customerId).trigger("change");
		$(".output-form .selectCustomer").html(customerName);
	})
});
</script>
