<?php
$processId = $_POST['processId'] ? $o_main->db->escape_str($_POST['processId']) : 0;
$processStepId = $_POST['processStepId'] ? $o_main->db->escape_str($_POST['processStepId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM collecting_cases_process WHERE id = $processId";
$o_query = $o_main->db->query($sql);
$processData = $o_query ? $o_query->row_array() : array();


if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$amount = '';
		foreach($_POST['amount'] as $key=>$value) {
			if($value != ""){
				$amount = $value;
			}
		}
		if($_POST['reminder_transaction_text'] != "" && $amount != "") {
			$sortnr = 0;
			$noInterestError = true;
			$noInterestError2 = true;

			$sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr DESC";
			$o_query = $o_main->db->query($sql, array($processData['id']));
			$maxData = $o_query ? $o_query->row_array() : array();
			$sortnr = intval($maxData['sortnr']) + 1;
			if($processData['type'] == 1) {
				$_POST['bank_account_choice'] = 1;
			}
			$sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
			$o_query = $o_main->db->query($sql, array($processData['id']));
			$steps = $o_query ? $o_query->result_array() : array();
			if($_POST['doNotAddInterest']){
				if($processStepId){
					foreach($steps as $single_step) {
						if($single_step['id'] == $processStepId) {
							break;
						}
						if(!$single_step['doNotAddInterest']){
							$noInterestError = false;
						}
					}
				} else {
					if(count($steps) > 0) {
						$noInterestError = false;
					}
				}
			} else {
				$afterCurrentStep = false;
				foreach($steps as $single_step) {
					if($afterCurrentStep) {
						if($single_step['doNotAddInterest']){
							$noInterestError2 = false;
						}
					}
					if($single_step['id'] == $processStepId) {
						$afterCurrentStep = true;
					}
				}
			}
			if($noInterestError) {
				if($noInterestError2) {
			        if ($processStepId) {
			            $sql = "UPDATE collecting_cases_process_steps SET
			            updated = now(),
			            updatedBy='".$variables->loggID."',
			            name='".$o_main->db->escape_str($_POST['name'])."',
			            days_after_due_date='".$o_main->db->escape_str($_POST['days_after_due_date'])."',
			            bank_account_choice='".$o_main->db->escape_str($_POST['bank_account_choice'])."',
			            collecting_cases_pdftext_id='".$o_main->db->escape_str($_POST['collecting_cases_pdftext_id'])."',
						status_id = '".$o_main->db->escape_str($_POST['status'])."',
						sub_status_id = '".$o_main->db->escape_str($_POST['sub_status'])."',
			            add_number_of_days_to_due_date='".$o_main->db->escape_str($_POST['add_number_of_days_to_due_date'])."',
			            sending_action='".$o_main->db->escape_str($_POST['sending_action'])."',
						reminder_transaction_text='".$o_main->db->escape_str($_POST['reminder_transaction_text'])."',
						doNotAddFee='".$o_main->db->escape_str($_POST['doNotAddFee'])."',
						doNotAddInterest='".$o_main->db->escape_str($_POST['doNotAddInterest'])."',
						show_collecting_company_logo='".$o_main->db->escape_str($_POST['show_collecting_company_logo'])."',
						reminder_amount ='".$o_main->db->escape_str($_POST['reminder_amount'])."',
						editable_reminder_text_for_customer='".$o_main->db->escape_str($_POST['editable_reminder_text_for_customer'])."'
			            WHERE id = $processStepId";

						$o_query = $o_main->db->query($sql);
						$step_id = $processStepId;
			            $fw_redirect_url = $_POST['redirect_url'];
			        } else {
			            $sql = "INSERT INTO collecting_cases_process_steps SET
			            created = now(),
			            createdBy='".$variables->loggID."',
			            name='".$o_main->db->escape_str($_POST['name'])."',
						sortnr = '".$o_main->db->escape_str($sortnr)."',
			            days_after_due_date='".$o_main->db->escape_str($_POST['days_after_due_date'])."',
			            collecting_cases_process_id='".$o_main->db->escape_str($processId)."',
			            bank_account_choice='".$o_main->db->escape_str($_POST['bank_account_choice'])."',
			            collecting_cases_pdftext_id='".$o_main->db->escape_str($_POST['collecting_cases_pdftext_id'])."',
						status_id = '".$o_main->db->escape_str($_POST['status'])."',
						sub_status_id = '".$o_main->db->escape_str($_POST['sub_status'])."',
			            add_number_of_days_to_due_date='".$o_main->db->escape_str($_POST['add_number_of_days_to_due_date'])."',
			            sending_action='".$o_main->db->escape_str($_POST['sending_action'])."',
						reminder_transaction_text='".$o_main->db->escape_str($_POST['reminder_transaction_text'])."',
						doNotAddFee='".$o_main->db->escape_str($_POST['doNotAddFee'])."',
						doNotAddInterest='".$o_main->db->escape_str($_POST['doNotAddInterest'])."',
						show_collecting_company_logo='".$o_main->db->escape_str($_POST['show_collecting_company_logo'])."',
						reminder_amount ='".$o_main->db->escape_str($_POST['reminder_amount'])."',
						editable_reminder_text_for_customer='".$o_main->db->escape_str($_POST['editable_reminder_text_for_customer'])."'";

						$o_query = $o_main->db->query($sql);
			            $step_id = $o_main->db->insert_id();
			            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
			        }
					if($step_id > 0){
						$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
						$o_query = $o_main->db->query($s_sql, array($step_id));
						$current_fees = ($o_query ? $o_query->result_array() : array());

						$updated_fee_ids = array();
						foreach($_POST['amount'] as $key=>$value) {
							$value = str_replace(" ", "", str_replace(",", ".", $value));
							$mainclaim_from_amount = str_replace(" ", "", str_replace(",", ".", $_POST['mainclaim_from_amount'][$key]));
							$fee_id = 0;
							if(strpos($key, "new_") === false){
								$fee_id = $key;
							}
							if($value != "") {
								if($fee_id > 0) {
									$sql = "UPDATE collecting_cases_process_step_fees SET
								   updated = now(),
								   updatedBy='".$variables->loggID."',
								   amount = '".$value."',
								   mainclaim_from_amount = '".$mainclaim_from_amount."'
								   WHERE id = $fee_id";

								   $o_query = $o_main->db->query($sql);
								   $updated_fee_ids[] = $fee_id;
							   } else {
								   $sql = "INSERT INTO collecting_cases_process_step_fees SET
								  created = now(),
								  createdBy='".$variables->loggID."',
								  amount = '".$value."',
								  mainclaim_from_amount = '".$mainclaim_from_amount."',
								  collecting_cases_process_step_id = '".$step_id."'";

								  $o_query = $o_main->db->query($sql);
								  if($o_query) {
									  $updated_fee_ids[] = $o_main->db->insert_id();
								  }
							   }
						   }
						}
						foreach($current_fees as $current_fee) {
							if(!in_array($current_fee['id'], $updated_fee_ids)) {
								$sql = "DELETE FROM collecting_cases_process_step_fees WHERE id = '".$current_fee['id']."'";
								$o_query = $o_main->db->query($sql);
							}
						}
					}
				} else {
					$fw_error_msg[] = $formText_CanNotSetInterestToBeAddedWhileNextStepsHasNoInterest_output;
				}
			} else {
				$fw_error_msg[] = $formText_CanNotSetInterestToNotBeAddedWhilePreviousStepsHasInterest_output;
			}
		}
	}
}
if($action == "deleteProcess" && $processStepId) {
    $sql = "DELETE FROM collecting_cases_process_steps
    WHERE id = $processStepId";
    $o_query = $o_main->db->query($sql);
}

$sql = "SELECT * FROM collecting_cases_process_steps WHERE id = $processStepId";
$o_query = $o_main->db->query($sql);
$processStepData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$letters = $o_query ? $o_query->result_array() : array();


$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$emails = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$main_statuses = ($o_query ? $o_query->result_array() : array());
$filterDownStatusArray = array();
if($processData['type'] == 0){
	$filterDownStatusArray = array(1,2,5);
} else if($processData['type'] == 1){
	$filterDownStatusArray = array(3,4,6,7);
}

$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
$o_query = $o_main->db->query($s_sql, array($processStepData['id']));
$fees = ($o_query ? $o_query->result_array() : array());
?>

<div class="popupform popupform-<?php echo $processId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_process_step";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="processId" value="<?php echo $processId;?>">
		<input type="hidden" name="processStepId" value="<?php echo $processStepId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$processId; ?>">
		<div class="inner">
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="name" value="<?php echo $processStepData['name']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_DaysAfterDueDate_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="days_after_due_date" value="<?php echo $processStepData['days_after_due_date']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<?php
			if(intval($processData['type']) == 0) {
			?>
	    		<div class="line">
	        		<div class="lineTitle"><?php echo $formText_BankAccount_output; ?></div>
	        		<div class="lineInput">
						<select name="bank_account_choice" autocomplete="off" >
						   <option value=""><?php echo $formText_Choose_output;?></option>
						   <option value="0" <?php if(0 == $processStepData['bank_account_choice']) echo 'selected';?>><?php echo $formText_UseOwnCreditorBankAccount_output;?></option>
						   <option value="1" <?php if(1 == $processStepData['bank_account_choice']) echo 'selected';?>><?php echo $formText_UseCollectingCompanyBankAccount_output;?></option>
					   </select>
	                </div>
	        		<div class="clear"></div>
	    		</div>
			<?php } ?>

    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_AddNumberOfDaysToDueDate_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="add_number_of_days_to_due_date" value="<?php echo $processStepData['add_number_of_days_to_due_date']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line ">
        		<div class="lineTitle"><?php echo $formText_LetterText_Output; ?></div>
        		<div class="lineInput">
					<select name="collecting_cases_pdftext_id" autocomplete="off">
						<option value="0"><?php echo $formText_None_output;?></option>
						<?php
						foreach($letters as $letter) {
							?>
							<option value="<?php echo $letter['id'];?>" <?php if($letter['id'] == $processStepData['collecting_cases_pdftext_id']) echo 'selected';?>><?php echo $letter['name'];?></option>
							<?php
						}
						?>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			<?php /*?>
			<div class="line emailText">
        		<div class="lineTitle"><?php echo $formText_EmailText_Output; ?></div>
        		<div class="lineInput">
					<select name="collecting_cases_emailtext_id" autocomplete="off">
						<option value="0"><?php echo $formText_None_output;?></option>
						<?php
						foreach($emails as $letter) {
							?>
							<option value="<?php echo $letter['id'];?>" <?php if($letter['id'] == $processStepData['collecting_cases_emailtext_id']) echo 'selected';?>><?php echo $letter['subject'];?></option>
							<?php
						}
						?>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>*/?>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_Status_Output; ?></div>
        		<div class="lineInput">
					<select name="status" autocomplete="off" class="statusChange">
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php
						foreach($main_statuses as $main_status) {
							if(in_array($main_status['id'], $filterDownStatusArray)){
								?>
								<option value="<?php echo $main_status['id'];?>" <?php if($main_status['id'] == $processStepData['status_id']) echo 'selected';?>><?php echo $main_status['name'];?></option>
								<?php
							}
						}
						?>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="subtituesWrapperBig">

			</div>

			<div class="line">
        		<div class="lineTitle"><?php echo $formText_SendingAction_Output; ?></div>
				<div class="lineInput">
					<select name="sending_action" class="changeSendingAction" autocomplete="off">
						<option value="0"><?php echo $formText_Select_output;?></option>
						<option value="1" <?php if($processStepData['sending_action'] == 1) echo 'selected';?>><?php echo $formText_SendLetter_output;?></option>
						<option value="2" <?php if($processStepData['sending_action'] == 2) echo 'selected';?>><?php echo $formText_SendEmailIfEmailExistsOrElseLetter_output;?></option>
						<option value="4" <?php if($processStepData['sending_action'] == 4) echo 'selected';?>><?php echo $formText_SendSmsIfMobileExistsOrEmailOrElseLetter_output;?></option>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_ReminderTransactionText_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="reminder_transaction_text" value="<?php echo $processStepData['reminder_transaction_text']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_ReminderAmount_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="reminder_amount" value="<?php echo number_format($processStepData['reminder_amount'], 2, ",", " "); ?>">
					<div class="fee_list">
						<?php if(count($fees) > 0) {
						foreach($fees as $fee) { ?>
							<div class="fee_block">
								<div class="line">
									<div class="lineTitle"><?php echo $formText_MainClaimFrom_Output; ?></div>
									<div class="lineInput">
										<input type="text" class="popupforminput botspace" autocomplete="off" name="mainclaim_from_amount[<?php echo $fee['id'];?>]" value="<?php echo number_format($fee['mainclaim_from_amount'], 2, ",", " "); ?>">
									</div>
									<div class="clear"></div>
								</div>

								<div class="line">
					        		<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
					        		<div class="lineInput">
										<input type="text" class="popupforminput botspace" autocomplete="off" name="amount[<?php echo $fee['id'];?>]" value="<?php echo number_format($fee['amount'], 2, ",", " "); ?>">
									</div>
					        		<div class="clear"></div>
					    		</div>
							</div>
						<?php }
						} else {
							?>
							<div class="fee_block">
								<div class="line">
									<div class="lineTitle"><?php echo $formText_MainClaimFrom_Output; ?></div>
									<div class="lineInput">
										<input type="text" class="popupforminput botspace" autocomplete="off" name="mainclaim_from_amount[0]" value="<?php echo number_format($fee['mainclaim_from_amount'], 2, ",", " "); ?>">
									</div>
									<div class="clear"></div>
								</div>

								<div class="line">
					        		<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
					        		<div class="lineInput">
										<input type="text" class="popupforminput botspace" autocomplete="off" name="amount[0]" value="<?php echo number_format($fee['amount'], 2, ",", " "); ?>">
									</div>
					        		<div class="clear"></div>
					    		</div>
							</div>
							<?php
						}
						 ?>
					 </div>
					 <div class="add_more_fee"><?php echo $formText_AddMoreFeeLevel_output;?></div>
					 <div class="initial_fee_block">
						 <div class="fee_block ">
							 <div class="line">
								 <div class="lineTitle"><?php echo $formText_MainClaimFrom_Output; ?></div>
								 <div class="lineInput">
									 <input type="text" class="popupforminput botspace" autocomplete="off" name="mainclaim_from_amount[placeholder]" value="">
								 </div>
								 <div class="clear"></div>
							 </div>

							 <div class="line">
								 <div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
								 <div class="lineInput">
									 <input type="text" class="popupforminput botspace" autocomplete="off" name="amount[placeholder]" value="">
								 </div>
								 <div class="clear"></div>
							 </div>
						 </div>
					 </div>
				</div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_AddFee_Output; ?></div>
        		<div class="lineInput">
					<select  class="popupforminput botspace" name="doNotAddFee">
						<option value="0"><?php echo $formText_Yes_output;?></option>
						<option value="1" <?php if($processStepData['doNotAddFee']) echo "selected"; ?>><?php echo $formText_No_output;?></option>
					</select>
				</div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_AddInterest_Output; ?></div>
        		<div class="lineInput">
					<select  class="popupforminput botspace" name="doNotAddInterest">
						<option value="0"><?php echo $formText_Yes_output;?></option>
						<option value="1" <?php if($processStepData['doNotAddInterest']) echo "selected"; ?>><?php echo $formText_No_output;?></option>
					</select>
				</div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_ShowCollectingCompanyLogo_Output; ?></div>
        		<div class="lineInput">
					<select  class="popupforminput botspace" name="show_collecting_company_logo">
						<option value="0"><?php echo $formText_No_output;?></option>
						<option value="1" <?php if($processStepData['show_collecting_company_logo']) echo "selected"; ?>><?php echo $formText_Yes_output;?></option>
					</select>
				</div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_EditableReminderTextForCustomer_Output; ?></div>
        		<div class="lineInput">
					<select  class="popupforminput botspace" name="editable_reminder_text_for_customer">
						<option value="0"><?php echo $formText_Yes_output;?></option>
						<option value="1" <?php if($processStepData['editable_reminder_text_for_customer']) echo "selected"; ?>><?php echo $formText_No_output;?></option>
					</select>
				</div>
        		<div class="clear"></div>
    		</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
	<?php
	foreach($main_statuses as $main_status) {
		$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE collecting_cases_main_status_id = ? ORDER BY id";
		$o_query = $o_main->db->query($s_sql, array($main_status['id']));
		$sub_statuses = ($o_query ? $o_query->result_array() : array());
		if(count($sub_statuses) > 0){
			?>
			<div class="line subtituesWrapper subtituesWrapper<?php echo $main_status['id'];?>">
				<div class="lineTitle"><?php echo $formText_SubStatus_Output; ?></div>
				<div class="lineInput">
					<select name="sub_status" autocomplete="off" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php
						foreach($sub_statuses as $sub_status) {
							?>
							<option value="<?php echo $sub_status['id'];?>" <?php if($sub_status['id'] == $processStepData['sub_status_id']) echo 'selected';?>><?php echo $sub_status['name'];?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}
	}
	?>
</div>
<style>
.subtituesWrapper {
	display: none;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $processId;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
			$("#popup-validate-message").html("").hide();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
					if(data.error != undefined){
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append("<div>"+value+"</div>");
						});
						$("#popup-validate-message").show();
					}  else {
	                    if(data.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
	                        out_popup.close();
	                    }
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $processId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $processId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $processId;?> #popupeditbox').css('height', $('.popupform-<?php echo $processId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $processId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $processId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $processId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $processId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $processId;?> .selectCreditor");
            }
            if(element.attr("name") == "debitor_id") {
                error.insertAfter(".popupform-<?php echo $processId;?> .selectDebitor");
            }
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            debitor_id: "<?php echo $formText_SelectTheDebitor_output;?>",
        }
    });
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
    $(".popupform-<?php echo $processId;?> .selectCreditor").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, creditor: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_creditors";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $processId;?> .selectDebitor").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, debitor: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })


    $(".popupform-<?php echo $processId;?> .selectOwner").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, owner: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })

	$(".resetInvoiceResponsible").on("click", function(){
		$("#invoiceResponsible").val("");
		$(".selectInvoiceResponsible").html("<?php echo $formText_SelectInvoiceResponsible_Output;?>");
	})
	$(".popupform .statusChange").change(function(){
		if($(this).val() > 0) {
			$(".popupform .subtituesWrapperBig").html($(".popupform .subtituesWrapper"+$(this).val()).clone().removeClass("subtituesWrapper"+$(this).val()).show());
		} else {
			$(".popupform .subtituesWrapperBig").html("");
		}
	}).change();

	$(".add_more_fee").off("click").on("click", function(){
		var index = $(".fee_list .fee_block").length;
		$(".fee_list").append($(".initial_fee_block").clone().removeClass("initial_fee_block").html().replaceAll("placeholder", "new_"+index));
	})
	$(".changeSendingAction").change(function(){
		if($(this).val() == 4){
			$(".smstextwrapper").show();
		} else {
			$(".smstextwrapper").hide();
		}
	}).change();
});

</script>
<style>
.categoryWrapper {
	display: none;
}
.resetInvoiceResponsible {
	margin-left: 20px;
}
.lineInput .otherInput {
    margin-top: 10px;
}
.lineInput input[type="radio"]{
    margin-right: 10px;
    vertical-align: middle;
}
.lineInput input[type="radio"] + label {
    margin-right: 10px;
    vertical-align: middle;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.popupform .lineInput.lineWhole {
	font-size: 14px;
}
.popupform .lineInput.lineWhole label {
	font-weight: normal !important;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.invoiceEmail {
    display: none;
}
label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: inline !important;
}
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}
.addSubProject {
    margin-bottom: 10px;
}
.add_more_fee {
	cursor: pointer;
	color: #46b2e2;
	margin-bottom: 10px;
}
.initial_fee_block {
	display: none;
}
.fee_block {
 border-bottom: 1px solid #cecece;
 padding-top: 10px;
}
</style>
