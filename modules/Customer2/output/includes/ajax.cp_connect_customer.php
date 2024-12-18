<?php
if($moduleAccesslevel > 10)
{
	if($_POST['action'] == "createNewCustomer"){
		if($_POST['customerName'] != ""){
			$return = array();
			$s_sql = "INSERT INTO customer SET created = NOW(), createdBy = '".$variables->loggID."', name = '".$o_main->db->escape_str($_POST['customerName'])."', customerType = 0";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				$return['customerid'] = $o_main->db->insert_id();
				$return['customername'] = $_POST['customerName'];
				$fw_return_data = $return;
			} else {
				echo $formText_ErrorCreatingCustomer_output;
			}
		} else {
			echo $formText_MissingCustomerName_output;
		}
		return;
	}
	if($_POST['action'] == "deleteContactPerson"){
		if($_POST['contactpersonId'] != ""){
			$s_sql = "DELETE FROM contactperson WHERE id = '".$o_main->db->escape_str($_POST['contactpersonId'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				$fw_return_data = 1;
			} else {
				$fw_error_msg = $formText_ErrorCreatingCustomer_output;
			}
		} else {
			$fw_error_msg = $formText_MissingCustomerName_output;
		}
		return;

	}
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['firstname'] != "" && $_POST['lastname'] != "") {
			if($_POST['email'] != ""){
				if($_POST['customerId'] > 0){
					$contactpersonIds = isset($_POST['contactpersonIds']) ? $_POST['contactpersonIds'] : array();
					foreach($contactpersonIds as $contactpersonId) {
						$s_sql = "SELECT * FROM contactperson WHERE id = '".$o_main->db->escape_str($contactpersonId)."'";
						$o_query = $o_main->db->query($s_sql);
						$contactperson = $o_query ? $o_query->row_array() : array();
						if($contactperson){
							$s_sql = "UPDATE contactperson SET
							updated = now(),
							updatedBy= ?,
							customerId= ?,
							name= ?,
							middlename = ?,
							lastname = ?,
							email = ?,
							title = ?
							WHERE id = ?";
							$o_main->db->query($s_sql, array($variables->loggID, $_POST['customerId'],$_POST['firstname'],$_POST['middlename'],$_POST['lastname'],$_POST['email'],$_POST['title'], $contactperson['id']));
						}
					}

					$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
					return;
				} else {
					$fw_error_msg = array($formText_MissingCustomer_output);
					return;
				}
			} else {
				$fw_error_msg = array($formText_MissingEmail_output);
				return;
			}
		} else {
			$fw_error_msg = array($formText_FirstNameAndLastNameAreMandatory_output);
			return;
		}
	}
}
foreach($_POST['contactpersonId'] as $subscriptionId){
	$s_sql = "SELECT * FROM contactperson WHERE id = '".$o_main->db->escape_str($subscriptionId)."'";
    $o_query = $o_main->db->query($s_sql);
    $selectedContactperson = $o_query ? $o_query->row_array() : array();
}
$sql_where = "";
$domain_name = "";
$email = $selectedContactperson['email'];
$parts = explode("@",$email);
$domain_name = $parts[1];

if((isset($_POST['customerName']) && $_POST['customerName'] != "") || $domain_name != "")
{
	if($_POST['customerName'] != ""){
		$sql_where = " name LIKE '".$o_main->db->escape_str(mb_substr($_POST['customerName'], 0, 3))."%'";
	}
	if($domain_name != "") {
		if($_POST['customerName']  != ""){
			$sql_where .= " OR ";
		}
		$sql_where .= " name LIKE '".$o_main->db->escape_str(mb_substr($domain_name, 0, 3))."%'";
	}

	$s_sql = "SELECT * FROM customer WHERE customer.content_status < 2 AND (".$sql_where.")";
    $o_query = $o_main->db->query($s_sql);
    $suggestedCustomers = $o_query ? $o_query->result_array() : array();
}
?>
<div class="popupform">
    <div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=cp_connect_customer";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<?php if(is_array($_POST['contactpersonId'])){
		foreach($_POST['contactpersonId'] as $subscriptionId){
			?>
			<input type="hidden" name="contactpersonIds[]" value="<?php print $subscriptionId;?>">
			<?php
		}
	} else { ?>
		<input type="hidden" name="contactpersonIds[]" value="<?php print $_POST['contactpersonId'];?>">
	<?php } ?>

	<div class="inner">
        <div class="popupformTitle"><?php echo $formText_ConnectContactPersonToCustomer_output;?></div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_FirstName_Output; ?></div>
            <div class="lineInput">
				<input type="text" name="firstname" value="<?php echo $selectedContactperson['name'];?>" required class="popupforminput botspace" autocomplete="off"/>
            </div>
            <div class="clear"></div>
        </div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_MiddleName_Output; ?></div>
            <div class="lineInput">
				<input type="text" name="middlename" value="<?php echo $selectedContactperson['middlename'];?>" class="popupforminput botspace" autocomplete="off"/>
            </div>
            <div class="clear"></div>
        </div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_LastName_Output; ?></div>
            <div class="lineInput">
				<input type="text" name="lastname" value="<?php echo $selectedContactperson['lastname'];?>"  class="popupforminput botspace" autocomplete="off"/>
            </div>
            <div class="clear"></div>
        </div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_Email_Output; ?></div>
            <div class="lineInput">
				<input type="text" name="email" value="<?php echo $selectedContactperson['email'];?>" class="popupforminput botspace" autocomplete="off"/>
            </div>
            <div class="clear"></div>
        </div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_Title_Output; ?></div>
            <div class="lineInput">
				<input type="text" name="title" value="<?php echo $selectedContactperson['title'];?>" class="popupforminput botspace" autocomplete="off"/>
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


                <a href="#" class="createNewCustomer" data-customername="<?php echo $_POST['customerName'];?>" ><?php echo $formText_CreateNewCustomer_Output;?></a>
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
	<div class="copyContactpersonToNewsletter"><?php echo $formText_CopyContactpersonToNewsletter_output;?></div>
	<div class="clear"></div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_output?></button>
		<button type="button" class="output-btn b-large deleteContactPerson"><?php echo $formText_DeleteContactperson_output?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Connect_Output; ?>">
	</div>
</form>
</div>
<style>
.popupeditbox .popupformbtn button.deleteContactPerson {
	background-color: #fff;
	color: #194273;
	border: 1px solid #194273;
}
.suggested_customer {
	color: #46b2e2;
	cursor: pointer;
	padding: 2px 0px;
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
	$(".deleteContactPerson").off("click").on("click", function(e){
		e.preventDefault();
		bootbox.confirm('<?php echo $formText_ConfirmDeleting_output; ?>', function(result) {
			if (result) {
		        fw_loading_start();
				var data = {
					contactpersonId: '<?php echo $selectedContactperson['id']?>',
					action: 'deleteContactPerson'
				};
				ajaxCall('cp_connect_customer', data, function(data) {
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
						fw_loading_end();
						$("#popup-validate-message").show();
					} else {
						out_popup.addClass("close-reload");
						out_popup.close();
					}
				});
			}
		}).css({"z-index": 10000});
	})
	$(".copyContactpersonToNewsletter").off("click").on("click", function(){
		fw_loading_start();
		var data = {
			contactpersonId: '<?php echo $selectedContactperson['id']?>'
		};
		ajaxCall('cp_copy_to_newsletter', data, function(json) {

			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(json.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
		});
	})
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
	$(".suggested_customer").off("click").on("click", function(e){
        e.preventDefault();
		var customerId = $(this).data("customerid");
		var customerName = $(this).data("customername");
		$(".output-form #customerId").val(customerId).trigger("change");
		$(".output-form .selectCustomer").html(customerName);
	})
	$(".createNewCustomer").off("click").on("click", function(e){
		e.preventDefault();
        fw_loading_start();
		var data = {
			customerName: $(this).data("customername"),
			action: 'createNewCustomer'
		};
		ajaxCall('cp_connect_customer', data, function(json) {
			if(json.data){
				var customerId = json.data.customerid;
				var customerName = json.data.customername;
				$(".output-form #customerId").val(customerId).trigger("change");
				$(".output-form .selectCustomer").html(customerName);
			} else {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(json.html);
				out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			}
		});
	})
    $(".selectCustomer").unbind("click").bind("click", function(e){
        e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, customer_group: 1, show_subscriptiontypes: 1};
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
});
</script>
