<?php
$caseId = $_POST['caseId'] ? $o_main->db->escape_str($_POST['caseId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM collecting_cases WHERE id = $caseId";
$o_query = $o_main->db->query($sql);
$projectData = $o_query ? $o_query->row_array() : array();
include_once(__DIR__."/../../../CreditorsOverview/output/includes/fnc_process_open_cases_for_tabs.php");

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {

        if ($caseId) {
			if($_POST['creditor_id'] > 0 && $_POST['debitor_id'] > 0) {
	            $sql = "UPDATE collecting_cases SET
	            updated = now(),
	            updatedBy='".$variables->loggID."',
	            creditor_ref='".$o_main->db->escape_str($_POST['creditor_ref'])."',
	            creditor_id='".$o_main->db->escape_str($_POST['creditor_id'])."',
	            debitor_id='".$o_main->db->escape_str($_POST['debitor_id'])."',
				collecting_cases_process_step_id = '".$o_main->db->escape_str($_POST['collecting_cases_process_step_id'])."',
				kid_number = '".$o_main->db->escape_str($_POST['kid_number'])."',
				reminder_profile_id = '".$o_main->db->escape_str($_POST['reminder_profile_id'])."',
				choose_progress_of_reminderprocess = '".$o_main->db->escape_str($_POST['choose_progress_of_reminderprocess'])."',
				choose_move_to_collecting_process = '".$o_main->db->escape_str($_POST['choose_move_to_collecting_process'])."'
	            WHERE id = $caseId";

				$o_query = $o_main->db->query($sql);

				$insert_id = $caseId;
	            $fw_redirect_url = $_POST['redirect_url'];
			}
        } else {
			if($_POST['original_due_date'] == "") {
				$fw_error_msg = array($formText_MissingOriginalDueDate_output);
				return;
			}
			if($_POST['creditor_id'] > 0 && $_POST['debitor_id'] > 0) {
	            $sql = "INSERT INTO collecting_cases SET
	            created = now(),
	            createdBy='".$variables->loggID."',
	            creditor_ref='".$o_main->db->escape_str($_POST['creditor_ref'])."',
	            creditor_id='".$o_main->db->escape_str($_POST['creditor_id'])."',
	            debitor_id='".$o_main->db->escape_str($_POST['debitor_id'])."',
				kid_number = '".$o_main->db->escape_str($_POST['kid_number'])."',
				reminder_profile_id = '".$o_main->db->escape_str($_POST['reminder_profile_id'])."',
				choose_progress_of_reminderprocess = '".$o_main->db->escape_str($_POST['choose_progress_of_reminderprocess'])."',
				choose_move_to_collecting_process = '".$o_main->db->escape_str($_POST['choose_move_to_collecting_process'])."',
	            status = 3,
				collecting_process_started = NOW()";

				$o_query = $o_main->db->query($sql);
				if($o_query){
		            $insert_id = $o_main->db->insert_id();
		            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
				}
			}
	    }
		if($insert_id > 0){
			//trigger reordering
			$sql = "SELECT * FROM collecting_cases WHERE id = $insert_id";
			$o_query = $o_main->db->query($sql);
			$projectData = $o_query ? $o_query->row_array() : array();
			
			$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
			WHERE creditor_id = '".$o_main->db->escape_str($projectData['creditor_id'])."' AND external_customer_id = '".$o_main->db->escape_str($projectData['debitor_id'])."'";
			$o_query = $o_main->db->query($s_sql);
			process_open_cases_for_tabs($projectData['creditor_id'], 5);

			// $originalDueDate = "";
			// if($_POST['original_due_date'] != "") {
			// 	$originalDueDate = date("Y-m-d", strtotime($_POST['original_due_date']));
			// }
			//
			// $s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE collecting_case_id = ? AND claim_type = 1";
		    // $o_query = $o_main->db->query($s_sql, array($insert_id));
		    // if($o_query && $o_query->num_rows() == 1) {
		    // 	$case = ($o_query ? $o_query->row_array() : array());
			// 	$s_sql = "UPDATE collecting_cases_claim_lines SET
			// 	updated = now(),
			// 	updatedBy= ?,
			// 	name= ?,
	        //     original_due_date='".$o_main->db->escape_str($originalDueDate)."',
	        //     claim_type=1,
			// 	amount= ?
			// 	WHERE id = ?";
			// 	$o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['amount'], $case['id']));
			// } else {
			// 	$s_sql = "INSERT INTO collecting_cases_claim_lines SET
			// 	id=NULL,
			// 	moduleID = ?,
			// 	created = now(),
			// 	createdBy= ?,
			// 	collecting_case_id = ?,
			// 	name= ?,
	        //     original_due_date='".$o_main->db->escape_str($originalDueDate)."',
	        //     claim_type='1',
			// 	amount= ?";
			// 	$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $insert_id, $_POST['name'], $_POST['amount']));
			// 	$_POST['cid'] = $o_main->db->insert_id();
			// }
		}

	}
}
if($action == "statusChange" && $caseId) {
     $sql = "UPDATE collecting_cases SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    status='".$o_main->db->escape_str($status)."'
    WHERE id = $caseId";
    $o_query = $o_main->db->query($sql);
	return;
}
if($action == "subStatusChange" && $caseId) {
     $sql = "UPDATE collecting_cases SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    sub_status='".$o_main->db->escape_str($status)."'
    WHERE id = $caseId";
    $o_query = $o_main->db->query($sql);
	return;
}
if($action == "processCase" && $caseId) {
    $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseId));
    $case = ($o_query ? $o_query->row_array() : array());
    if($case){
		ob_start();
	    include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/default.php");
	    if(is_file(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php")){
	        include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php");
	    }
		$s_sql = "SELECT creditor.* FROM  creditor WHERE creditor.id = ?";
		$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
		$creditor = ($o_query ? $o_query->row_array() : array());
		$casesToGenerate = array();
		$manualProcessing = 1;
        $creditorId = $creditor['id'];
        $collecting_case_id = $case['id'];
		if(intval($case['status']) == 0 || $case['status'] == 1) {
        	include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_cases.php");

		} else if(intval($case['status']) == 7 || $case['status'] == 3) {
	        include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_cases_collecting.php");
		}
		// if(count($casesToGenerate) > 0) {
	    //     $v_return['log'] = $log;
		// 	$_POST['casesToGenerate'] = $casesToGenerate;
		//     include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_actions.php");
		// }
		$result_output = ob_get_contents();
		$result_output = trim(preg_replace('/\s\s+/', '', $result_output));
	    ob_end_clean();
		echo $result_output;
    } else {
		echo $formText_MissingCase_output;
	}
	return;
}
if($action == "stopCase" && $caseId) {

    $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseId));
    $case = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
	$invoice = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
	$invoice_payments = ($o_query ? $o_query->result_array() : array());

	$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
	$claim_transactions = ($o_query ? $o_query->result_array() : array());

	$status = $projectData['status'];
	if($projectData['status'] == 1 || intval($projectData['status']) == 0){
		$status = 2;
	} else if($projectData['status'] == 3){
		$status = 4;
	}
	$sub_status = $_POST['sub_status'];
	if($sub_status > 0) {

		$totalPayments = 0;
		foreach($invoice_payments as $invoice_payment) {
			$totalPayments += $invoice_payment['amount'];
		}
		$feeAmount = $invoice['amount'] + $totalPayments;

	 	$sql = "UPDATE collecting_cases SET
	    updated = now(),
	    updatedBy='".$variables->loggID."',
	    sub_status = ?,
		stopped_date = NOW(),
		status = ?,
		fee_income = ?
	    WHERE id = $caseId";
	    $o_query = $o_main->db->query($sql, array($sub_status, $status, $feeAmount));
		if($o_query){
			$fw_redirect_url = $_POST['redirect_url'];
		} else {
			$fw_error_msg = array($formText_ErrorUpdatingEntry_output);
		}
	} else {
		$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE collecting_cases_main_status_id = ? ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql, array($status));
		$sub_statuses = ($o_query ? $o_query->result_array() : array());
		if(count($sub_statuses) > 0) {
			?>
			<div class="popupform popupform-<?php echo $caseId;?>">
				<div id="popup-validate-message-case" style="display:none;"></div>
				<form class="output-form-case main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_case";?>" method="post">
					<input type="hidden" name="fwajax" value="1">
					<input type="hidden" name="fw_nocss" value="1">
					<input type="hidden" name="caseId" value="<?php echo $caseId;?>">
					<input type="hidden" name="action" value="<?php echo $action;?>">
			        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseId; ?>">
					<div class="inner">
						<div class="line">
							<div class="lineTitle"><?php echo $formText_SubStatus_Output; ?></div>
							<div class="lineInput">
								<select name="sub_status" required>
									<option value=""><?php echo $formText_Select_output;?></option>
									<?php foreach($sub_statuses as $sub_status) { ?>
										<option value="<?php echo $sub_status['id'];?>" <?php if($sub_status['id'] == $projectData['sub_status']) echo 'selected';?>>
											<?php echo $sub_status['name'];?>
										</option>
									<?php } ?>
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
			</div>
			<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
			<script type="text/javascript">

			$(document).ready(function() {
			    $(".popupform-<?php echo $caseId;?> form.output-form-case").validate({
			        ignore: [],
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
			                    if(data.redirect_url !== undefined)
			                    {
			                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
			                        out_popup.close();
			                    }
			                }
			            }).fail(function() {
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").show();
			                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('.popupform-<?php echo $caseId;?> #popupeditboxcontent').height());
			                fw_loading_end();
			            });
			        },
			        invalidHandler: function(event, validator) {
			            var errors = validator.numberOfInvalids();
			            if (errors) {
			                var message = errors == 1
			                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
			                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").html(message);
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").show();
			                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
			            } else {
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").hide();
			            }
			            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
			        },
			        errorPlacement: function(error, element) {
			            if(element.attr("name") == "creditor_id") {
			                error.insertAfter(".popupform-<?php echo $caseId;?> .selectCreditor");
			            }
			            if(element.attr("name") == "debitor_id") {
			                error.insertAfter(".popupform-<?php echo $caseId;?> .selectDebitor");
			            }
			        },
			        messages: {
			            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
			            debitor_id: "<?php echo $formText_SelectTheDebitor_output;?>",
			        }
			    });
			});

			</script>
			<?php
		} else {

			$totalPayments = 0;
			foreach($invoice_payments as $invoice_payment) {
				$totalPayments += $invoice_payment['amount'];
			}
			$feeAmount = $invoice['amount'] + $totalPayments;

			$sql = "UPDATE collecting_cases SET
			updated = now(),
			updatedBy='".$variables->loggID."',
			sub_status = 0,
			stopped_date = NOW(),
			status = ?,
			fee_income = ?
			WHERE id = $caseId";
			$o_query = $o_main->db->query($sql, array($feeAmount, $status));
		}
	}
	return;
}
if($action == "reactivateCase" && $caseId) {
	$status = $projectData['status'];
	if($projectData['status'] == 4){
		$status = 3;
	} else if($projectData['status'] == 2){
		$status = 1;
	}
	$sql = "UPDATE collecting_cases SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    sub_status = 0,
	status = ?
    WHERE id = $caseId";
    $o_query = $o_main->db->query($sql, array($status));
	return;
}
/*if($action == "statusChangeInvoice" && $caseId) {

    $sql = "SELECT * FROM project WHERE id = $caseId";
	$o_query = $o_main->db->query($sql);
    $projectData = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM people  WHERE people.id = ?";
    $o_query = $o_main->db->query($s_sql, array($projectData['invoiceresponsibleId']));
    $invoiceResponsible = ($o_query ? $o_query->row_array() : array());

	if(!$invoiceResponsible || $invoiceResponsible['email'] == $variables->loggID || $status == 0) {
		$hasCorrectOrders = false;

		$s_sql = "SELECT * FROM customer_collectingorder WHERE customer_collectingorder.projectId = ? AND (customer_collectingorder.invoiceNumber > 0) AND customer_collectingorder.content_status < 2  ORDER BY customer_collectingorder.date ASC";
		$o_query = $o_main->db->query($s_sql, array($projectData['id']));
		$invoicedCollectingOrders = ($o_query ? $o_query->result_array() : array());

		$s_sql = "SELECT * FROM customer_collectingorder WHERE customer_collectingorder.projectId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status < 2  ORDER BY customer_collectingorder.date ASC";
		$o_query = $o_main->db->query($s_sql, array($projectData['id']));
		$uninvoicedCollectingOrders = ($o_query ? $o_query->result_array() : array());

		if(count($invoicedCollectingOrders) > 0 && count($uninvoicedCollectingOrders) == 0) {
			$hasCorrectOrders = true;
		}
		if($hasCorrectOrders) {
		    $sql = "UPDATE project SET
		    updated = now(),
		    updatedBy='".$variables->loggID."',
		    invoiceResponsibleStatus='".$o_main->db->escape_str($status)."'
		    WHERE id = $caseId";
		    $o_query = $o_main->db->query($sql);
		} else {
			$fw_return_data = "message";
			?>
			<div class="line">
				<?php echo $formText_CanNotSetAsInvoiced_Output; ?>
				<div>
					<label><?php echo $formText_NoInvoicedOrders_output?></label>
				</div>
			</div>
			<?php
		}
	} else {
		$fw_return_data = "message";
		?>
		<div class="line">
			<?php echo $formText_CanNotSetAsInvoiced_Output; ?>
			<?php echo $formText_YouAreNotInvoiceResponsible_Output; ?>
			<div>
				<?php echo $formText_InvoiceResponsible_output;?>: <label><?php echo $invoiceResponsible['name']." ".$invoiceResponsible['middle_name']." ".$invoiceResponsible['last_name']?></label>
			</div>
		</div>
		<?php
	}

	return;

}*/
if($action == "deleteProject" && $caseId) {
    $sql = "DELETE FROM project
    WHERE id = $caseId";
    $o_query = $o_main->db->query($sql);
}
if($caseId) {

	if($projectData['status'] == 0 || $projectData['status'] == 1){
		$s_sql = "SELECT * FROM collecting_cases_process WHERE collecting_cases_process.id = ?";
		$o_query = $o_main->db->query($s_sql, array($projectData['reminder_process_id']));
		$collectingProcess = ($o_query ? $o_query->row_array() : array());
	} else if($projectData['status'] == 3 || $projectData['status'] == 7){
		$s_sql = "SELECT * FROM collecting_cases_process WHERE collecting_cases_process.id = ?";
		$o_query = $o_main->db->query($s_sql, array($projectData['collecting_process_id']));
		$collectingProcess = ($o_query ? $o_query->row_array() : array());
	}

	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($projectData['creditor_id']));
    $creditor = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM customer  WHERE customer.id = ?";
    $o_query = $o_main->db->query($s_sql, array($projectData['debitor_id']));
    $debitor = ($o_query ? $o_query->row_array() : array());

}
$collectingLevelArray = array(0=>$formText_NoUpdate_output, 1=>$formText_Reminder_output, 2=>$formText_DebtCollectionWarning_output, 3=>$formText_PaymentEncouragement_output,4=>$formText_HeavyFeeWarning_output, 5=>$formText_LastWarningBeforeLegalAction_output, 6=>$formText_LegalAction_output);
$creditor_reminder_custom_profiles = array();
if($creditor){
	$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE content_status < 2 AND creditor_id = ? ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql, $creditor['id']);
	$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());
}
?>

<div class="popupform popupform-<?php echo $caseId;?>">
	<div id="popup-validate-message-case" style="display:none;"></div>
	<form class="output-form-case main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_case";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="caseId" value="<?php echo $caseId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseId; ?>">
		<div class="inner">

			<div class="line customerInputWrapper">
				<div class="lineTitle"><?php echo $formText_Creditor_Output; ?></div>
				<div class="lineInput">
					<?php if($creditor) { ?>
					<a href="#" class="selectCreditor"><?php echo $creditor['companyname']?></a>
					<?php } else { ?>
					<a href="#" class="selectCreditor"><?php echo $formText_SelectCreditor_Output;?></a>
					<?php } ?>
					<input type="hidden" name="creditor_id" id="creditorId" value="<?php print $creditor['id'];?>" required>
				</div>
				<div class="clear"></div>
			</div>
			<div class="second_step_wrapper" <?php if($projectData) echo 'style="display: block;"'?>>
				<div class="line ">
					<div class="lineTitle"><?php echo $formText_Debitor_Output; ?></div>
					<div class="lineInput">
						<?php if($debitor) { ?>
						<a href="#" class="selectDebitor"><?php echo $debitor['name']?></a>
						<?php } else { ?>
						<a href="#" class="selectDebitor"><?php echo $formText_SelectDebitor_Output;?></a>
						<?php } ?>
						<input type="hidden" name="debitor_id" id="debitorId" value="<?php print $debitor['id'];?>" required>
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_KidNumber_Output; ?></div>
					<div class="lineInput">
	                    <input type="text" class="popupforminput botspace" autocomplete="off" name="kid_number" value="<?php echo $projectData['kid_number']; ?>" >
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ReminderProfileId_Output; ?></div>
					<div class="lineInput">
						<select name="reminder_profile_id" class="popupforminput botspace">
							<option value=""><?php echo $formText_Default_output;?></option>
							<?php
							foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {

								$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.id = ?";
							    $o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
							    $currentProcess = $o_query ? $o_query->row_array() : array();

								if($creditor_reminder_custom_profile['name'] == ""){
									$creditor_reminder_custom_profile['name'] = $currentProcess['fee_level_name']." ".$currentProcess['stepTypeName'];
								}
								?>
								<option value="<?php echo $creditor_reminder_custom_profile['id'];?>" <?php if($creditor_reminder_custom_profile['id'] == $projectData['reminder_profile_id']) echo 'selected';?>><?php echo $creditor_reminder_custom_profile['name'];?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseProgressOfReminderProccess_Output; ?></div>
					<div class="lineInput">
						<select name="choose_progress_of_reminderprocess" class="popupforminput botspace">
							<option value=""><?php echo $formText_Default_output;?></option>
							<option value="1" <?php if($projectData['choose_progress_of_reminderprocess'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($projectData['choose_progress_of_reminderprocess'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($projectData['choose_progress_of_reminderprocess'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseMoveToCollectingProcess_Output; ?></div>
					<div class="lineInput">
						<select name="choose_move_to_collecting_process" class="popupforminput botspace">
							<option value=""><?php echo $formText_Default_output;?></option>
							<option value="1" <?php if($projectData['choose_move_to_collecting_process'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($projectData['choose_move_to_collecting_process'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($projectData['choose_move_to_collecting_process'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php if(!$projectData){ ?>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_ClaimName_Output; ?></div>
						<div class="lineInput">
							<input type="text" class="popupforminput botspace" autocomplete="off" name="name" value="<?php echo $v_data['name']; ?>" required>
						</div>
						<div class="clear"></div>
					</div>
					<div class="line ">
						<div class="lineTitle"><?php echo $formText_OriginalDueDate_Output; ?></div>
						<div class="lineInput">
							<input type="text" class="popupforminput botspace datefield" autocomplete="off"  name="original_due_date" value="<?php if($v_data['original_due_date'] != "0000-00-00" && $v_data['original_due_date'] != ""){ echo date("d.m.Y", strtotime($v_data['original_due_date'])); }?>" required>
						</div>
						<div class="clear"></div>
					</div>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
						<div class="lineInput">
							<input type="text" class="popupforminput botspace" autocomplete="off" name="amount" value="<?php echo number_format($v_data['amount'], 2, ",", ""); ?>" required>
						</div>
						<div class="clear"></div>
					</div>
				<?php } ?>
			</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $caseId;?> form.output-form-case").validate({
        ignore: [],
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
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").show();
                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('.popupform-<?php echo $caseId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").html(message);
                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").show();
                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .selectCreditor");
            }
            if(element.attr("name") == "debitor_id") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .selectDebitor");
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
    $(".popupform-<?php echo $caseId;?> .selectCreditor").unbind("click").bind("click", function(e){
		e.preventDefault();
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
    $(".popupform-<?php echo $caseId;?> .selectDebitor").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, debitor: 1, creditor_id: $("#creditorId").val()};
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


    $(".popupform-<?php echo $caseId;?> .selectOwner").unbind("click").bind("click", function(){
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
});

</script>
<style>
.second_step_wrapper {
	display: none;
}
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
#popup-validate-message-case, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
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
</style>
