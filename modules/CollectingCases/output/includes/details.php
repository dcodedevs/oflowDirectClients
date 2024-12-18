<?php

// List btn
require_once __DIR__ . '/list_btn.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM collecting_cases WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();
$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array());


$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
$debitor = ($o_query ? $o_query->row_array() : array());


$profile = array();
if($caseData['reminder_profile_id'] > 0){
	$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($caseData['reminder_profile_id']));
	$profile = $o_query ? $o_query->row_array() : array();
}
// if(!$profile) {
// 	if($debitor['creditor_reminder_profile_id'] > 0){
// 		$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
// 		$o_query = $o_main->db->query($s_sql, array($debitor['creditor_reminder_profile_id']));
// 		$profile = $o_query ? $o_query->row_array() : array();
// 	}
// 	if(!$profile){
// 		if($debitor['customer_type_collect'] == 0){
// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
// 			$profile = $o_query ? $o_query->row_array() : array();
// 		} else {
// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
// 			$profile = $o_query ? $o_query->row_array() : array();
// 		}
// 	}
// }
if(!$profile){
	echo $formText_MissingProfile_output;
	return;
}
$s_sql = "SELECT * FROM collecting_cases_process WHERE collecting_cases_process.id = ?";
$o_query = $o_main->db->query($s_sql, array($profile['reminder_process_id']));
$reminderProcess = ($o_query ? $o_query->row_array() : array());

$sql = "SELECT * FROM process_step_types WHERE id = ?";
$o_query = $o_main->db->query($sql, array($reminderProcess['process_step_type_id']));
$reminderProcessStepType = $o_query ? $o_query->row_array() : array();

function formatHour($hour){
	return str_replace(".", ",", floatval(number_format($hour, 2, ".", "")));
}

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : '1';
$mainlist_filter = $_SESSION['mainlist_filter'] ? ($_SESSION['mainlist_filter']) : 'reminderLevel';
$responsibleperson_filter = $_SESSION['responsibleperson_filter'] ? ($_SESSION['responsibleperson_filter']) : '';
$list_filter_main = $_SESSION['list_filter_main'] ? ($_SESSION['list_filter_main']) : '';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$casetype_filter = $_SESSION['casetype_filter'] ? $_SESSION['casetype_filter'] : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter."&mainlist_filter=".$mainlist_filter;

if($_GET['backToCreditor']){
	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CreditorsOverview&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$_GET['backToCreditor']."&mainlist_filter=case&list_filter=".$list_filter."&search_filter=".$search_filter;
}
$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseData['id']."&view=".$list_filter_main;

$registered_group_list = array();
$v_membersystem = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}

include("fnc_calculate_interest.php");
$collectingLevelArray = array(0=>$formText_NoUpdate_output, 1=>$formText_Reminder_output, 2=>$formText_DebtCollectionWarning_output, 3=>$formText_PaymentEncouragement_output,4=>$formText_HeavyFeeWarning_output, 5=>$formText_LastWarningBeforeLegalAction_output, 6=>$formText_LegalAction_output);

$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$system_settings = ($o_query ? $o_query->row_array() : array());

$objection_after_days = intval($system_settings['default_days_after_closed_objection_to_process']);
if($creditor['days_after_closed_objection_to_process'] != NULL){
    $objection_after_days = $creditor['days_after_closed_objection_to_process'];
}

$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($caseData['id']));
$objections = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql, array());
$main_statuses = ($o_query ? $o_query->result_array() : array());


$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE collecting_cases_main_status_id = ? ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql, array($caseData['status']));
$sub_statuses = ($o_query ? $o_query->result_array() : array());


$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($caseData['id']));
$invoice = ($o_query ? $o_query->row_array() : array());

// $interestArray = calculate_interest($invoice, $caseData);
// $totalInterest = 0;
// foreach($interestArray as $interest) {
// 	$interestRate = $interest['rate'];
// 	$interestAmount = $interest['amount'];
// 	$interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
// 	$interestTo = date("Y-m-d", strtotime($interest['dateTo']));
//
// 	$totalInterest += $interestAmount;
// }
// var_dump($totalInterest);

$currencyName = "";
if($invoice['currency'] == 'LOCAL') {
	$currencyName = " ".$creditor['default_currency'];
} else {
	$currencyName = " ".$invoice['currency'];
}
$connected_transactions = array();
$all_connected_transaction_ids = array($invoice['id']);
if($invoice['link_id'] > 0 && ($creditor['checkbox_1'])) {
	$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['id']));
	$connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
	foreach($connected_transactions_raw as $connected_transaction_raw){
		if(strpos($connected_transaction_raw['comment'], '_') === false){
			$connected_transactions[] = $connected_transaction_raw;
		}
	}
	foreach($connected_transactions as $connected_transaction){
		$all_connected_transaction_ids[] = $connected_transaction['id'];
	}
}
// if($variables->loggID == "byamba@dcode.no"){
// 	$s_sql = "SELECT ct.* FROM creditor_transactions ct
// 	JOIN collecting_cases cc ON cc.id = ct.collectingcase_id
// 	WHERE ct.collectingcase_id > 0 AND ct.open=1";
// 	$o_query = $o_main->db->query($s_sql);
// 	$creditor_transactions = ($o_query ? $o_query->result_array() : array());
// 	foreach($creditor_transactions as $invoice) {
// 		$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
// 		$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
// 		$invoice_payments = ($o_query ? $o_query->result_array() : array());
// 		if(count($invoice_payments) > 0){
// 			foreach($invoice_payments as $invoice_payment) {
// 				$s_sql = "SELECT * FROM collecting_cases_reset_info WHERE creditor_transaction_id = ? AND collecting_case_id = ? ORDER BY created DESC";
// 				$o_query = $o_main->db->query($s_sql, array($invoice_payment['id'], $invoice['collectingcase_id']));
// 				$resetHappened = ($o_query ? $o_query->row_array() : array());
// 				if(!$resetHappened){
// 					$s_sql = "INSERT INTO collecting_cases_reset_info SET created = NOW(), createdBy='import', creditor_transaction_id = ?, collecting_case_id = ?, info='reset by credited'";
// 					$o_query = $o_main->db->query($s_sql, array($invoice_payment['id'], $invoice['collectingcase_id']));
// 				}
// 			}
// 		}
// 	}
// }

if($variables->loggID == "byamba@dcode.no") {
	include_once(__DIR__."/../../../CollectingCases/output/includes/fnc_generate_pdf.php");
	if($caseData['create_letter']){
		?>
		<div class="error">
			<?php echo $formText_LastLetterWasNotCreated_output;?>
			<span class="create_last_letter"><?php echo $formText_CreateLetter_output;?></span>
		</div>
		<style>
			.create_last_letter {
				cursor: pointer;
				color: #46b2e2;
			}
		</style>
		<script type="text/javascript">
			$(function(){
				$(".create_last_letter").off("click").on("click", function(e){
					e.preventDefault();
					var self = $(this);
					var data = {
						case_id: "<?php echo $cid;?>",
					};
					bootbox.confirm('<?php echo $formText_CreateLetter_output; ?>', function(result) {
						if (result) {
							ajaxCall('create_letter', data, function(json) {
								if(json.html != ""){
									$('#popupeditboxcontent').html('');
									$('#popupeditboxcontent').html(json.html);
									out_popup = $('#popupeditbox').bPopup(out_popup_options);
									$("#popupeditbox:not(.opened)").remove();
									out_popup.addClass("close-reload");
								} else {
									loadView("details", {cid:"<?php echo $cid;?>"});
								}
							});
						}
					});
				})
			})
		</script>
		<?php
	}
	// $case_ids = array("38920", "38919", "38918", "38917", "38916", "38915", "38914", "38913", "38912", "38911", "38910", "38909", "33060");
	// foreach($case_ids as $case_id){
	// 	$result = generate_pdf($case_id);
	// 	if(count($result['errors']) > 0){
	// 		foreach($result['errors'] as $error){
	// 			echo $formText_LetterFailedToBeCreatedForCase_output." ".$caseToGenerate." ".$error."</br>";
	// 		}
	// 	} else {
	// 		var_dump($result);
	// 	}
	// }
}

?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle">
						<div class="" style="float: left">
							<?php echo $formText_CaseNumber_output;?>
							<div class="caseId"><span class="caseIdText"><?php echo $caseData['id'];?></span></div>
							<!-- <div class="caseCreated">
								<?php echo $formText_Created_output.": ". $caseData['created'];?> <?php echo $formText_CreatedBy_output.": ".$caseData['createdBy'];?>
							</div> -->
						</div>

						<div class="clear"></div>
					</div>

					<div class="p_contentBlock">
					    <div class="caseDetails">
					        <table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
					        	<tr>
					                <td class="txt-label"><?php echo $formText_Creditor_output;?></td>
					                <td class="txt-value">
					                	<?php echo $creditor['companyname'];?>
					                </td>
					            </tr>
					        	<tr>
					                <td class="txt-label"><?php echo $formText_CreditorRef_output;?></td>
					                <td class="txt-value">
					                	<?php echo $caseData['creditor_ref'];?>
					                </td>
					            </tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_Debitor_output;?></td>
					                <td class="txt-value">
					                	<?php echo $debitor['name']." ".$debitor['middlename']." ".$debitor['lastname'];?>
					                </td>
					            </tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_InvoiceEmail_output;?></td>
					                <td class="txt-value">
					                	<?php echo $debitor['invoiceEmail'];?>
					                </td>
					            </tr>
					            <tr>
					                <td class="txt-label"><?php echo $formText_DebitorAddress_output;?></td>
					                <td class="txt-value">
										<?php echo $debitor['paStreet']." ".$debitor['paPostalNumber']." ".$debitor['paCity'];?>
									</td>
					            </tr>
					            <tr>
					                <td class="txt-label"><?php echo $formText_DebitorCustomerType_output;?></td>
					                <td class="txt-value">
										<?php if($customerData['customer_type_for_collecting_cases'] == 0) {
											echo $formText_UseCrmCustomerType_output;
										} else if($customerData['customer_type_for_collecting_cases'] == 1) {
											echo $formText_Company_output;
										} else if($customerData['customer_type_for_collecting_cases'] == 2) {
											echo $formText_PrivatePerson_output;
										}
										?>
										<span class="glyphicon glyphicon-pencil edit_collecting_address" data-customer-id="<?php echo $debitor['id'];?>"></span>
									</td>
					            </tr>

					            <tr>
					                <td class="txt-label"><?php echo $formText_ReminderProfileId_Output;?></td>
					                <td class="txt-value"><?php
										$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($caseData['reminder_profile_id']));
										$creditor_reminder_custom_profile = ($o_query ? $o_query->row_array() : array());

										$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.id = ?";
									    $o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
									    $currentProcess = $o_query ? $o_query->row_array() : array();

										if($creditor_reminder_custom_profile['name'] == ""){
											$creditor_reminder_custom_profile['name'] = $currentProcess['fee_level_name']." ".$currentProcess['stepTypeName'];
										}
										?>
										<span class="showProfileSettings">
											<?php
											echo $creditor_reminder_custom_profile['name']; ?>
										</span>
									</td>
					            </tr>
					            <tr>
					                <td class="txt-label"><?php echo $formText_ChooseProgressOfReminderProccess_Output;?></td>
					                <td class="txt-value"><?php switch($caseData['choose_progress_of_reminderprocess']) {
										case 0:
											echo $formText_Default_output;
										break;
										case 1:
											echo $formText_Manual_output;
										break;
										case 2:
											echo $formText_Automatic_output;
										break;
										case 3:
											echo $formText_DoNotSent_output;
										break;
									} ?></td>
					            </tr>
					            <tr>
					                <td class="txt-label"><?php echo $formText_ChooseMoveToCollectingProcess_Output;?></td>
					                <td class="txt-value"><?php switch($caseData['choose_move_to_collecting_process']) {
										case 0:
											echo $formText_Default_output;
										break;
										case 1:
											echo $formText_Manual_output;
										break;
										case 2:
											echo $formText_Automatic_output;
										break;
										case 3:
											echo $formText_DoNotSent_output;
										break;
									} ?></td>
					            </tr>


					            <tr>
					                <td class="txt-label"><?php echo $formText_ConnectOtherParts_output;?></td>
					                <td class="txt-value">
										<?php
										$s_sql = "SELECT * FROM collecting_cases_other_parts WHERE collecting_cases_other_parts.collecting_case_id = ?";
										$o_query = $o_main->db->query($s_sql, array($caseData['id']));
										$otherParts = ($o_query ? $o_query->result_array() : array());
										foreach($otherParts as $otherPart) {
											$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
											$o_query = $o_main->db->query($s_sql, array($otherPart['customer_id']));
											$otherPartCustomer = ($o_query ? $o_query->row_array() : array());
											?>
											<div class="">
												<?php echo $otherPartCustomer['name']." ".$otherPartCustomer['middlename']." ".$otherPartCustomer['lastname']?>
												<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-otherpart editBtnIcon" data-case-id="<?php echo $cid; ?>" data-otherpart-id="<?php echo $otherPart['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
												<?php if($moduleAccesslevel > 110) { ?>
												<button class="output-btn small output-delete-otherpart editBtnIcon" data-case-id="<?php echo $cid; ?>" data-otherpart-id="<?php echo $otherPart['id'];?>">
													<span class="glyphicon glyphicon-trash"></span>
												<?php } ?>
											</div>
											<?php
										}
										?>
										<a href="#" class="output-edit-otherpart" data-case-id="<?php echo $cid?>">+ <?php echo $formText_AddAnother_output;?></a>
									</td>
					            </tr>
								<tr>
					                <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-case-detail editBtnIcon" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?></td>
					            </tr>

					        </table>
							<?php

							$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = '".$o_main->db->escape_str($profile['reminder_process_id'])."'  ORDER BY sortnr ASC";
							$o_query = $o_main->db->query($s_sql);
							$all_steps = $o_query ? $o_query->result_array() : array();

							if(intval($caseData['status']) == 0){
								$caseData['status'] = 1;
							}
							$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig WHERE id = ? ORDER BY id ASC";
							$o_query = $o_main->db->query($s_sql, array(intval($caseData['status'])));
							$collecting_case_status = ($o_query ? $o_query->row_array() : array());

							$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE id = ? ORDER BY id ASC";
							$o_query = $o_main->db->query($s_sql, array(intval($caseData['sub_status'])));
							$collecting_case_substatus = ($o_query ? $o_query->row_array() : array());

						    // $s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND (status = 0 OR status is null) ORDER BY sortnr ASC";
						    // $o_query = $o_main->db->query($s_sql);
						    // $active_payment_plan = ($o_query ? $o_query->row_array() : array());
							?>
							<div class="collectinglevelDisplay">
								<div>
									<?php echo $formText_Status_output;?>
									<span class="levelText">
										<?php
										if($invoice['open'] == 0) {
											echo $formText_Closed_output;
										} else {
											echo $collecting_case_status['name'];
										}
										?>
										<span class="edit_case_status glyphicon glyphicon-pencil"></span>
									</span>
								</div>
								<?php if(count($sub_statuses) > 0) {?>
									<div>
										<?php echo $formText_SubStatus_output;?>
										<span class="levelText">
											<?php
											echo $collecting_case_substatus['name'];
											?>
										</span>
									</div>
								<?php } ?>
								<div>
									<?php echo $formText_DueDate_output;?>
									<?php if($active_payment_plan){
										?>
										<span class="levelText"><?php echo $formText_ActivePaymentAgreement_output;?></span>
										<?php
									} else { ?>
										<span class="processText">
											<?php
											if($caseData['due_date'] != "0000-00-00" && $caseData['due_date'] != "") echo date("d.m.Y", strtotime($caseData['due_date']));
											?>
											<span class="edit_due_date glyphicon glyphicon-pencil"></span>
										</span>
									<?php } ?>
								</div>
								<br/>
								<?php

								$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? AND (objection_closed_date = '0000-00-00' OR objection_closed_date is null) ORDER BY created DESC";
								$o_query = $o_main->db->query($s_sql, array($caseData['id']));
								$activeObjections = ($o_query ? $o_query->result_array() : array());

								if(count($activeObjections) > 0){
									echo $formText_CaseHasActiveObjections_output;
								}
								$current_step = array();

								$next_step = $all_steps[0];
								foreach($all_steps as $index=>$all_step) {
									if($all_step['id'] == $caseData['collecting_cases_process_step_id']){
										$current_step = $all_step;
										$next_step = $all_steps[$index+1];
									}
								}
								?>

								<div>
									<?php echo $formText_ReminderProcess_output;?>
									<span class="processText">
										<?php
										echo $reminderProcess['fee_level_name']." ".$reminderProcessStepType['name'];
										?>
									</span>
								</div>
								<div class="processStepWrapper <?php if(intval($caseData['collecting_cases_process_step_id']) == 0 && ($caseData['status'] == 0 || $caseData['status'] == 1)) echo 'active_step';?>">
									<?php echo $formText_NotStarted_output;?>
								</div>
								<?php

								foreach($all_steps as $all_step) {
									$isCurrentStep = false;
									$isNextStep = false;
									if($current_step['id']  == $all_step['id']){
										$isCurrentStep = true;
									}
									if($next_step['id']  == $all_step['id']){
										$isNextStep = true;
									}
									?>
									<div class="processStepWrapper <?php if($current_step['id'] == $all_step['id']){ echo 'active_step'; }?>">
										<?php
										echo $all_step['name'];
										if($isCurrentStep){
											echo " (".$formText_CurrentStep_output.")";
										} else if($isNextStep) {
											if(count($activeObjections) == 0) {
												?>
												(<?php echo $formText_NextStep_output;?>
													<?php
													if($caseData['due_date'] != "0000-00-00" && $caseData['due_date'] != ""){
														$dueDateTime = strtotime($caseData['due_date']);
														$correctDueDateTime = strtotime("+".($next_step['days_after_due_date'])." days", $dueDateTime);

														$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? AND (objection_closed_date <> '0000-00-00' AND objection_closed_date is not null) ORDER BY objection_closed_date DESC";
														$o_query = $o_main->db->query($s_sql, array($caseData['id']));
														$closedObjection = ($o_query ? $o_query->row_array() : array());
														$objectionTime = 0;
														if($closedObjection) {
															$objectionTime = strtotime("+".($objection_after_days)." days", strtotime($closedObjection['objection_closed_date']));
														}
														if($objectionTime > $correctDueDateTime){
															$correctDueDateTime = $objectionTime;
														}

														?>
														<?php echo date("d.m.Y", $correctDueDateTime);?>
														<?php
													}
													?>
												)
												<?php
											}
										}
										?>
										<div class="clear"></div>
									</div>
									<?php
								}
								?>
								<br/>
								<?php /*?>
								<div class="information">
									<?php
									$transitionText = $formText_Manual_output;
									if($creditor['choose_progress_of_reminderprocess'] == 1 && $creditor['choose_how_to_create_collectingcase']){
										$transitionText = $formText_Automatic_output;
									}

									echo $formText_TransitionFromReminderToCollecting_output.": ".$transitionText;?>
								</div>*/?>

								<?php
								?>
								<?php
								if($variables->useradmin){
									?>
									<div class="processCase" data-case-id="<?php echo $caseData['id'];?>"><?php echo $formText_ProcessCase_output;?></div>
									<div class="clear"></div><div class="createNewRest" data-case-id="<?php echo $caseData['id'];?>"><?php echo $formText_CreateNewRestNote_output;?></div>
									<?php
								}

								?>
								<?php /*if($caseData['status'] == 2 || $caseData['status'] == 4) { ?>
									<div class="reactivateCase" data-case-id="<?php echo $caseData['id'];?>">
										<?php echo $formText_ReactivateCase_output;?>
									</div>
								<?php } else { ?>
									<div class="stopCase" data-case-id="<?php echo $caseData['id'];?>">
										<?php echo $formText_StopCase_output;?>
									</div>
								<?php }*/ ?>

								<div class="resetTheCaseFully" data-case-id="<?php echo $caseData['id'];?>">
									<?php echo $formText_ResetCaseFully_output;?>
								</div>

								<div class="clear"></div>
							</div>
							<div class="clear"></div>
					    </div>
					</div>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle dropdown_content_show white">
							<?php echo $formText_Claims_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-claims" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock noTopPadding dropdown_content">
							<?php

							$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
							$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
							$all_invoice_payments = ($o_query ? $o_query->result_array() : array());

							$invoice_payments = array();
							foreach($all_invoice_payments as $all_invoice_payment) {
								if(!in_array($all_invoice_payment['id'], $all_connected_transaction_ids)){
									$invoice_payments[] = $all_invoice_payment;
								}
							}

							$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$claims = ($o_query ? $o_query->result_array() : array());

							$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
							$claim_transactions = ($o_query ? $o_query->result_array() : array());
							// var_dump($invoice['link_id']);
							$totalSumPaid = 0;
							$totalSumPaidInvoice = 0;
							$totalSumDue = 0;

							?>
							<table class="claimsTable table table-borderless">
								<tr>
									<th width="70%"><?php echo $formText_Name_Output; ?></th>
									<th width="10%" style="text-align: right;">
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_Amount_Output;?>
									</th>
									<th width="10%" style="text-align: right;"></th>
								</tr>
								<?php if($invoice){ ?>
									<tr>
										<td width="70%">
											<?php echo $formText_InvoiceNumber_output." ".$invoice['invoice_nr'];?>.
											<?php if($invoice['due_date'] != "0000-00-00" && $invoice['due_date'] != ""){ echo $formText_DueDate_output." ".date("d.m.Y", strtotime($invoice['due_date'])); }?>
										</td>
										<td width="10%" style="text-align: right;">

										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($invoice['collecting_case_original_claim'], 2, ",", " ").$currencyName;
											$totalSumDue += $invoice['collecting_case_original_claim'];
											?>
										</td>
										<td width="10%" style="text-align: right;">

										</td>
									</tr>
								<?php } ?>

								<?php if(count($connected_transactions) > 0){ ?>
									<?php foreach($connected_transactions as $connected_transaction) { ?>
										<tr>
											<td width="70%">
												<?php if($connected_transaction['system_type'] == "CreditnoteCustomer") { echo $formText_CreditNote_output; } else { echo $formText_InvoiceNumber_output;} echo " ".$connected_transaction['invoice_nr'];?>.
												<?php if($connected_transaction['due_date'] != "0000-00-00" && $connected_transaction['due_date'] != ""){ echo $formText_DueDate_output." ".date("d.m.Y", strtotime($connected_transaction['due_date'])); }?>
											</td>
											<td width="10%" style="text-align: right;">

											</td>
											<td width="10%" style="text-align: right;">
												<?php
												echo number_format($connected_transaction['amount'], 2, ",", " ").$currencyName;
												$totalSumDue += $connected_transaction['amount'];
												?>
											</td>
											<td width="10%" style="text-align: right;">

											</td>
										</tr>
									<?php } ?>
								<?php } ?>
								<?php foreach($invoice_payments as $invoice_payment) {?>
									<tr>
										<td width="70%">
											<?php if($invoice_payment['system_type'] == 'Payment') { echo $formText_Payment_output; } else {  echo $formText_CreditNote_output;} echo " ".date("d.m.Y", strtotime($invoice_payment['date']));?>
										</td>
										<td width="10%" style="text-align: right;">

										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($invoice_payment['amount'], 2, ",", " ").$currencyName;
											$totalSumPaidInvoice += $invoice_payment['amount'];
											?>
										</td>
										<td width="10%" style="text-align: right;">

										</td>
									</tr>
								<?php } ?>
								<?php
								foreach($claim_transactions as $claim_transaction) {
									$claim_text_array = explode("_", $claim_transaction['comment']);
									?>
									<tr>
										<td width="70%">
											<?php echo $claim_text_array[0];?>
										</td>
										<td width="10%" style="text-align: right;">

										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($claim_transaction['amount'], 2, ",", " ").$currencyName;
											$totalSumDue += $claim_transaction['amount'];
											?>
										</td>
										<td width="10%" style="text-align: right;">
										</td>
									</tr>
								<?php
								}

								foreach($claims as $claim) {
									?>
									<tr>
										<td width="70%">
											<?php echo $claim['name'];?>
											<?php if($claim['original_due_date'] != "0000-00-00" && $claim['original_due_date'] != ""){ echo date("d.m.Y", strtotime($claim['original_due_date'])); }?>
										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($claim['interest_percent'], 2, ",", " ");
											?>
										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($claim['amount'], 2, ",", " ").$currencyName;
											$totalSumDue += $claim['amount'];
											?>
										</td>
										<td width="10%" style="text-align: right;">
											<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-claims editBtnIcon" data-case-id="<?php echo $cid; ?>" data-claim-id="<?php echo $claim['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
											<?php if($moduleAccesslevel > 110) {

										 	?>
											<button class="output-btn small output-delete-claims editBtnIcon" data-case-id="<?php echo $cid; ?>" data-claim-id="<?php echo $claim['id'];?>">
												<span class="glyphicon glyphicon-trash"></span>
											</button>
											<?php
											} ?>
										</td>
									</tr>
								<?php }
								?>

								<?php
								$totalSumDue += $totalSumPaidInvoice;
								$totalSumDueAfterPayment = number_format($totalSumDue - $totalSumPaid, 2, ".", "");
								?>
								<tr class="spaceWrapper"><td colspan="3"></td></tr>
								<tr class="totalSum">
									<td width="70%" class="first">
										<?php echo $formText_TotalSumPaidInvoice_Output; ?><br/>
										<?php echo $formText_TotalSumPaid_Output; ?><br/>
										<?php echo $formText_TotalSumDue_Output; ?><br/>
									</td>
									<td width="10%" style="text-align: right;">
									</td>
									<td width="10%" class="second" style="text-align: right;">
										<?php echo number_format($totalSumPaidInvoice, 2, ",", " ").$currencyName; ?><br/>
										<?php echo number_format($totalSumPaid, 2, ",", " ").$currencyName; ?><br/>
										<?php echo number_format($totalSumDueAfterPayment, 2, ",", " ").$currencyName; ?><br/>
									</td>
									<td width="10%" style="text-align: right;"></td>
								</tr>

								<tr>
									<td colspan="4" style="text-align: right;">
										<?php /*?>
										<a href="<?php echo $_SERVER['PHP_SELF'].'/../../modules/'.$module.'/output/includes/generatePdf.php?caseId='.$cid;?>" class="generatePdf" target="_blank">
											<?php echo $formText_DownloadPdf_output; ?>
										</a> */ ?>
									</td>
								</tr>
							</table>

							<?php
							$s_sql = "SELECT * FROM collecting_cases_interest_calculation
							WHERE collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."'
							ORDER BY created ASC";
			                $o_query = $o_main->db->query($s_sql);
			                $collecting_cases_interest_calculations = ($o_query ? $o_query->result_array() : array());
							if(count($collecting_cases_interest_calculations) > 0) {
								?>
								<table class="claimsTable table table-borderless">
									<tr>
										<th width="70%"><?php echo $formText_Name_Output; ?></th>
										<th width="10%" style="text-align: right;">
										</th>
										<th width="10%" style="text-align: right;">
											<?php echo $formText_Amount_Output;?>
										</th>
									</tr>
									<?php foreach($collecting_cases_interest_calculations as $collecting_cases_interest_calculation) { ?>
										<tr>
											<td width="70%"><?php echo $formText_Interest_Output; ?> (<?php echo date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_from']))." - ".date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_to']))?>)</td>
											<td width="10%" style="text-align: right;">
												<?php echo number_format($collecting_cases_interest_calculation['rate'], 2, ",", " ");?>
											</td>
											<td width="10%" style="text-align: right;">
												<?php
												echo number_format($collecting_cases_interest_calculation['amount'], 2, ",", " ").$currencyName;
												?>
											</td>
										</tr>
									<?php } ?>
								</table>
								<?php
							}
							?>
							<span class="edit_claim_fees_setting fas fa-cog" ></span>
						</div>
					</div>
					<div class="p_contentBlockWrapper">

						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_NotesAndFiles_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-comment" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<?php

							$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$comments = ($o_query ? $o_query->result_array() : array());
							foreach($comments as $comment) {
								?>
								<div class="commentBlock">
									<table class="table table-borderless">
										<tr>
											<td width="90%" class="createdLabel"><?php echo date("d.m.Y H:i", strtotime($comment['created']));?> | <?php echo $comment['createdBy'];?></td>
											<td width="10%" style="text-align: right;">
												<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-comment editBtnIcon" data-case-id="<?php echo $cid; ?>" data-comment-id="<?php echo $comment['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
												<?php if($moduleAccesslevel > 110) { ?>
						    					<button class="output-btn small output-delete-comment editBtnIcon" data-case-id="<?php echo $cid; ?>" data-comment-id="<?php echo $comment['id'];?>">
							    					<span class="glyphicon glyphicon-trash"></span>
						    					<?php } ?>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<?php echo nl2br($comment['text']);?>
												<?php
												$files = json_decode($comment['files']);
												foreach($files as $file) {
													$fileParts = explode('/',$file[1][0]);
													$fileName = array_pop($fileParts);
													$fileParts[] = rawurlencode($fileName);
													$filePath = implode('/',$fileParts);
													$fileUrl = $extradomaindirroot."/../".$file[1][0];
													$fileName = $file[0];
													if(strpos($file[1][0],'uploads/protected/')!==false)
													{
														$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_notesandfiles&field=files&ID='.$comment['id'];
													}
												?>
													<div class="project-file">
														<div class="project-file-file">
															<a href="<?php echo $fileUrl;?>" download><?php echo $fileName;?></a>
														</div>
													</div>
													<?php
												}
												?>
											</td>
										</tr>
									</table>
								</div>
								<?php
							}
							?>
						</div>
					</div>

					<div class="p_contentBlockWrapper">

						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_ClaimAction_Output;?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">

							<table class="table table-borderless">
								<tr>
									<th><?php echo $formText_Date_Output; ?></th>
									<th><?php echo $formText_ActionType_Output; ?></th>
									<th><?php echo $formText_PerformedDate_Output; ?></th>
									<th><?php echo $formText_TotalAmount_Output; ?></th>
									<th><?php echo $formText_DueDate_Output; ?></th>
									<th><?php echo $formText_Pdf_Output; ?></th>
									<th></th>
								</tr>
								<?php


								$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status < 2 AND case_id = ? ORDER BY created DESC";
								$o_query = $o_main->db->query($s_sql, array($caseData['id']));
								$v_claim_letters = ($o_query ? $o_query->result_array() : array());
								foreach($v_claim_letters as $v_claim_letter){									
									?>
									<tr>
										<td><?php echo date("d.m.Y", strtotime($v_claim_letter['created'])); ?></td>
										<td><?php
										if($v_claim_letter['sending_status'] > 0){
											if($v_claim_letter['performed_action'] == 0){
												echo $formText_SendLetter_output;
											} else if($v_claim_letter['performed_action'] == 1){
												echo $formText_SendEmail_output;
											} else if($v_claim_letter['performed_action'] == 5){
												echo $formText_SendEhf_output;
											}
										} else {
											switch(intval($v_claim_letter['sending_action'])) {
												case 0:
													echo $formText_None_output;
												break;
												case 1:
													echo $formText_SendLetter_output;
												break;
												case 2:
													echo $formText_SendEmailIfEmailExistsOrElseLetter_output;
												break;
											}
										}
										?></td>
										<td><?php
										if($v_claim_letter['sending_status'] == 1){
											if($v_claim_letter['performed_date'] != "0000-00-00" && $v_claim_letter['performed_date'] != "") echo date("d.m.Y", strtotime($v_claim_letter['performed_date']));

										} else if($v_claim_letter['sending_status'] == -1) {
											echo $formText_CurrentlySending_output;
										} else if($v_claim_letter['sending_status'] == 2){
											echo $formText_SendingFailed_output;
										}
										?></td>
										<td><?php if($v_claim_letter['total_amount'] != "") echo number_format($v_claim_letter['total_amount'], 2, ",", " ")?></td>
										<td><?php if($v_claim_letter['due_date'] != "0000-00-00" && $v_claim_letter['due_date'] != "") echo date("d.m.Y", strtotime($v_claim_letter['due_date'])); ?></td>
										<td>
											<?php if($v_claim_letter['pdf'] != "") {
												$fileParts = explode('/',$v_claim_letter['pdf']);
												$fileName = array_pop($fileParts);
												$fileParts[] = rawurlencode($fileName);
												$filePath = implode('/',$fileParts);
												$fileUrl = $extradomaindirroot.$v_claim_letter['pdf'];
												$fileName = basename($v_claim_letter['pdf']);
												if(strpos($v_claim_letter['pdf'],'uploads/protected/')!==false)
												{
													$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_claim_letter&field=pdf&ID='.$v_claim_letter['id'];
												}
												?>
												<div class="project-file">
													<div class="project-file-file">
														<a href="<?php echo $fileUrl;?>" download target="_blank"><?php echo $formText_Download_Output;?></a>
													</div>
												</div>
											<?php } ?>
										</td>
										<td><span class="delete_letter glyphicon glyphicon-trash" data-letter-id="<?php echo $v_claim_letter['id']?>"></span></td>
									</tr>
									<?php
								}
								?>
							</table>
							<?php
							$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status = 2 AND case_id = ? ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$deleted_claim_letters = ($o_query ? $o_query->result_array() : array());
							if(count($deleted_claim_letters) > 0) {
								?>
								<div class="showDeletedClaimLetters"><?php echo $formText_ShowDeletedClaimLetters_output; echo " ".count($deleted_claim_letters)?> <span class="glyphicon glyphicon-menu-down"></span><span class="glyphicon glyphicon-menu-up"></span></div>
								<div class="deletedClaimLetters">
									<table class="table table-borderless">
										<tr>
											<th><?php echo $formText_Date_Output; ?></th>
											<th><?php echo $formText_ActionType_Output; ?></th>
											<th><?php echo $formText_PerformedDate_Output; ?></th>
											<th><?php echo $formText_TotalAmount_Output; ?></th>
											<th><?php echo $formText_DueDate_Output; ?></th>
											<th><?php echo $formText_Pdf_Output; ?></th>
											<th><?php echo $formText_DeletionComment_Output; ?></th>
										</tr>
										<?php
										foreach($deleted_claim_letters as $v_claim_letter) {
											?>
											<tr>
												<td><?php echo date("d.m.Y", strtotime($v_claim_letter['created'])); ?></td>
												<td><?php
												if($v_claim_letter['sending_status'] > 0){
													if($v_claim_letter['performed_action'] == 0){
														echo $formText_SendLetter_output;
													} else if($v_claim_letter['performed_action'] == 1){
														echo $formText_SendEmail_output;
													} else if($v_claim_letter['performed_action'] == 5){
														echo $formText_SendEhf_output;
													}
												} else {
													switch(intval($v_claim_letter['sending_action'])) {
														case 0:
															echo $formText_None_output;
														break;
														case 1:
															echo $formText_SendLetter_output;
														break;
														case 2:
															echo $formText_SendEmailIfEmailExistsOrElseLetter_output;
														break;
													}
												}
												?></td>
												<td><?php
												if($v_claim_letter['sending_status'] == 1){
													if($v_claim_letter['performed_date'] != "0000-00-00" && $v_claim_letter['performed_date'] != "") echo date("d.m.Y", strtotime($v_claim_letter['performed_date']));

												} else if($v_claim_letter['sending_status'] == -1) {
													echo $formText_CurrentlySending_output;
												} else if($v_claim_letter['sending_status'] == 2){
													echo $formText_SendingFailed_output;
												}
												?></td>
												<td><?php if($v_claim_letter['total_amount'] != "") echo number_format($v_claim_letter['total_amount'], 2, ",", " ")?></td>
												<td><?php if($v_claim_letter['due_date'] != "0000-00-00" && $v_claim_letter['due_date'] != "") echo date("d.m.Y", strtotime($v_claim_letter['due_date'])); ?></td>
												<td>
													<?php if($v_claim_letter['pdf'] != "") {

														$fileParts = explode('/',$v_claim_letter['pdf']);
														$fileName = array_pop($fileParts);
														$fileParts[] = rawurlencode($fileName);
														$filePath = implode('/',$fileParts);
														$fileUrl = $extradomaindirroot.$v_claim_letter['pdf'];
														$fileName = basename($v_claim_letter['pdf']);
														if(strpos($v_claim_letter['pdf'],'uploads/protected/')!==false)
														{
															$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_claim_letter&field=pdf&ID='.$v_claim_letter['id'];
														}
														?>
														<div class="project-file">
															<div class="project-file-file">
																<a href="<?php echo $fileUrl;?>" download target="_blank"><?php echo $formText_Download_Output;?></a>
															</div>
														</div>
													<?php } ?>

												</td>
												<td><?php echo nl2br($v_claim_letter['delete_comment']);?></td>
											</tr>
											<?php
										}
										?>
									</table>
								</div>
								<?php
							}
							?>
						</div>
					</div>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_Objection_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-objection" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<table class="table">
								<tr>
									<th><?php echo $formText_Type_output;?></th>
									<th><?php echo $formText_Message_output;?></th>
									<th><?php echo $formText_Closed_output;?></th>
									<th></th>
								</tr>
								<?php
								$type_messages = array("", $formText_WantsInvoiceCopy_output,$formText_WantsDefermentOfPayment_output,$formText_WantsInstallmentPayment_output,$formText_HasAnObjectionToTheAmount_output,$formText_HasAnObjectionToTheProductService_output);

								foreach($objections as $objection) {
									?>
									<tr>
										<td><?php echo $type_messages[$objection['objection_type_id']];?></td>
										<td><?php echo $objection['message_from_debitor'];?></td>
										<td><?php
										if($objection['objection_closed_date'] != "0000-00-00" && $objection['objection_closed_date'] != ""){
											echo date("d.m.Y", strtotime($objection['objection_closed_date']))." ".$objection['objection_closed_by']."<br/>".nl2br($objection['objection_closed_handling_description']);
										}
										?></td>
										<td>
											<button class="output-btn small output-edit-objection editBtnIcon" data-objection-id="<?php echo $objection['id'];?>" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
											<button class="output-btn small output-close-objection editBtnIcon" data-objection-id="<?php echo $objection['id'];?>" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Close_output;?></button>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed:0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
		if($(this).is('.close-reload')) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		}
	}
};
$(function(){
	// $(".generatePdf").off("click").on("click", function(e) {
	// 	e.preventDefault();
	// 	var data = {
	// 		caseId: '<?php echo $cid;?>',
	// 	};
	// 	ajaxCall('generatePdf', data, function(json) {
	// 		if(json.data != undefined) {
	// 			var a = document.createElement('a');
	// 			a.href =  '<?php echo $extradomaindirroot."/uploads/";?>'+json.data;
	// 			a.setAttribute('target', '_blank');
	// 			a.click();
	// 		}
	// 	});
	// })
	$(".showProfileSettings").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: '<?php echo $cid;?>'
		};
		ajaxCall('show_profile_settings', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".addAdditionalDays").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: '<?php echo $cid;?>',
			process_step_id: $(this).data("process_step_id"),
			cid: $(this).data("id")
		};
		ajaxCall('add_additional_days', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".caseStatusChange").on('change', function(e){
		e.preventDefault();
		var caseId  = $(this).data('case-id');
		var data = {
			caseId: caseId,
			action:"statusChange",
			status: $(this).val()
		};
		ajaxCall('edit_case', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	});
	$(".caseSubStatusChange").on('change', function(e){
		e.preventDefault();
		var caseId  = $(this).data('case-id');
		var data = {
			caseId: caseId,
			action:"subStatusChange",
			status: $(this).val()
		};
		ajaxCall('edit_case', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	});
	$(".output-edit-case-detail").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id')
		};
		ajaxCall('edit_case', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-comment").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('comment-id')
		};
		ajaxCall('edit_comment', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-comment").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('comment-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_comment', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
	$(".output-edit-payment").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('comment-id')
		};
		ajaxCall('edit_payment', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-payment-plan").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('paymentplan-id')
		};
		ajaxCall('edit_payment_plan', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-claims").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('claim-id')
		};
		ajaxCall('edit_claims', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-claims").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('claim-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_claims', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});


    $(".dropdown_content_show").unbind("click").bind("click", function(e){
        var parent = $(this);
        if($(e.target).hasClass("dropdown_content_show") || $(e.target).hasClass("showArrow") || $(e.target).parent().hasClass("showArrow")){
            var dropdown = parent.next(".p_contentBlock.dropdown_content");
            if(dropdown.is(":visible")) {
                dropdown.slideUp();
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
            } else {
                if(parent.hasClass("autoload")) {
                    dropdown.slideDown(0);
                    parent.removeClass("autoload");
                } else {
                    dropdown.slideDown();
                }
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            }
        }
    })

	$(".output-edit-messages-creditor").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('message-id')
		};
		ajaxCall('edit_message_creditor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-messages-creditor").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('message-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_message_creditor', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});

	$(".output-edit-messages-debitor").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('message-id')
		};
		ajaxCall('edit_message_debitor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-messages-debitor").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('message-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_message_debitor', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
	$(".output-edit-objection").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: $(this).data('case-id'),
			cid: $(this).data('objection-id')
		};
		ajaxCall('edit_objection', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-close-objection").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: $(this).data('case-id'),
			cid: $(this).data('objection-id')
		};
		ajaxCall('close_objection', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});


	$(".output-edit-otherpart").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('otherpart-id')
		};
		ajaxCall('edit_other_part', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-otherpart").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('otherpart-id'),
			action: "delete"
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_other_part', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
	$(".stopCase").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "stopCase"
		};
		bootbox.confirm('<?php echo $formText_ConfirmStopCase_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_case', data, function(json) {
					if(json.html != ""){
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(json.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
						out_popup.addClass("close-reload");
					} else {
						loadView("details", {cid:"<?php echo $cid;?>"});
					}
				});
			}
		});
	})
	$(".resetTheCaseFully").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			case_id: $(this).data('case-id'),
			action: "stopCase"
		};
		ajaxCall('reset_case_full', data, function(json) {
			if(json.html != ""){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				out_popup.addClass("close-reload");
			} else {
				loadView("details", {cid:"<?php echo $cid;?>"});
			}
		});
	})
	$(".reactivateCase").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "reactivateCase"
		};
		bootbox.confirm('<?php echo $formText_ConfirmStopCase_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_case', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	})
	$(".processCase").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "processCase"
		};
		bootbox.confirm('<?php echo $formText_LaunchCaseProcessAndLetterCreation_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_case', data, function(json) {
					if(json.html != ""){
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(json.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
						out_popup.addClass("close-reload");
					} else {
						loadView("details", {cid:"<?php echo $cid;?>"});
					}
				});
			}
		});
	})
	$(".edit_due_date").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_due_date', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".createNewRest").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			case_id: $(this).data('case-id'),
		};
		bootbox.confirm('<?php echo $formText_CreateNewRestNote_output; ?>', function(result) {
			if (result) {
				ajaxCall('create_new_restnote', data, function(json) {
					if(json.html != ""){
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(json.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
						out_popup.addClass("close-reload");
					} else {
						loadView("details", {cid:"<?php echo $cid;?>"});
					}
				});
			}
		});
	})
	$(".edit_case_status").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_case_status', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_case_step").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_case_step', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_claim_fees_setting").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_claim_fees_setting', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".delete_letter").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			letter_id: $(this).data('letter-id')
		};
		ajaxCall('delete_claimletter', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".showDeletedClaimLetters").off("click").on("click", function(){
		$(".deletedClaimLetters").slideToggle();
		$(this).toggleClass("active");
	})

	$(".edit_collecting_address").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			customer_id: $(this).data("customer-id"),
			creditor_id: '<?php echo $creditor['id'];?>'
		};
		ajaxCall('edit_collecting_address', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
})
</script>
<style>
	.edit_collecting_address {
		color: #46b2e2;
		cursor: pointer;
		margin-left: 10px;
	}
	.addAdditionalDays {
		color: #46b2e2;
		cursor: pointer;
	}
	.processStepWrapper {
		padding: 5px 0px;
		border-bottom: 1px solid #cecece;
	}
	.addAdditionalDaysRight {
		float: right;
	}
	.generatePdf {
		color: #46b2e2;
		cursor: pointer;
	}
	.totalSum {
		background: #f0f0f0;
	}
	.spaceWrapper td,
	.totalSum td {
		border: 0 !important;
	}
	.totalSum td.first {
		padding: 10px 10px !important;
	}
	.totalSum td.second {
		padding: 10px 0px !important;
	}
	.caseDetails .txt-label {
		width:30%;
	}
	.p_pageContent .btn-edit {
		text-align: right;
		margin-top: -15px;
	}
	.p_pageContent .btn-edit-table {
		margin-top: -25px;
	}
	.p_pageDetailsTitle .caseId {
		display: inline-block;
	}
	.caseStatus {
		float: right;
	}
	.p_contentBlockWrapper {
		position: relative;
		border-bottom: 2px solid #316896;
	}
	.p_contentBlockWrapper .p_contentBlock {
		border-bottom:0;
	}
	.p_contentBlockWrapper .p_pageDetailsSubTitle .showArrow {
	    float: right;
	    cursor: pointer;
	    color: #2996E7;
	    margin-left: 10px;
	    position: absolute;
	    right: 10px;
	    top: 12px;
	}
	.p_contentBlock.noTopPadding {
		padding-top: 0;
	}

	.table-borderless > tbody > tr > td,
	.table-borderless > tbody > tr > th,
	.table-borderless > tfoot > tr > td,
	.table-borderless > tfoot > tr > th,
	.table-borderless > thead > tr > td,
	.table-borderless > thead > tr > th {
		border: 0;
	}
	.commentBlock {
		border-bottom: 1px solid #ddd;
		border-radius: 0px;
		padding: 10px 0px;
	}
	.commentBlock .createdLabel {
		color: #8f8f8f !important;
	}
	.commentBlock .table {
		margin-bottom: 0;
	}
	.feedbackBlock {
		background: #f0f0f0;
	}
	#p_container .commentBlock td {
		padding: 0px 0px;
	}

	.ticketCommentBlock {
	    text-align: left;
	    width: 70%;
		float: right;
	}
	.ticketCommentBlock .inline_info {
	    float: right;
	    margin-left: 10px;
	}
	.ticketCommentBlock .table {
		display: block;
	    margin-bottom: 0;
		border: 1px solid #ddd;
	    border-radius: 5px;
	    margin-bottom: 10px;
	    padding: 7px 15px;
		margin-top: 5px;
	    background: #f0f0f0;
	}
	.ticketCommentBlock.from_customer {
	    text-align: left;
	    float: left;
	}
	.ticketCommentBlock.from_customer .table {
	    background: #bcdef7;
	}
	.ticketCommentBlock.from_customer .inline_info {
	    float: left;
	    margin-right: 10px;
	    margin-left: 0;
	}

	.employeeImage {
		width: 40px;
		height: 40px;
		overflow: hidden;
		position: relative;
		border-radius: 20px;
		overflow: hidden;
	    float: right;
	    margin-left: 10px;
	}
	.employeeImage img {
		width: calc(100% + 4px);
		height: auto;
		position: absolute;
	  	left: 50%;
	  	top: 50%;
	  	transform: translate(-50%, -50%);
	}
	.employeeInfo {
	    float: right;
	    width: calc(100% - 50px);
	}
	.ticketCommentBlock.from_customer .employeeImage {
	    float: left;
	    margin-left: 0;
	    margin-right: 10px;
	}
	.ticketCommentBlock.from_customer .employeeInfo {
	    float: left;
	}
	.detailContainer {
		margin-bottom: 10px;
	}
	.claimsTable > tbody > tr > td,
	.claimsTable > tbody > tr > th,
	.claimsTable > tfoot > tr > td,
	.claimsTable > tfoot > tr > th,
	.claimsTable > thead > tr > td,
	.claimsTable > thead > tr > th {
		border-bottom: 1px solid #ddd;
		padding: 5px 0px;
	}
	.caseDetails {
		position: relative;
	}
	.caseDetails .mainTable {
		width: 50%;
		float: left;
	}
	.caseDetails .collectinglevelDisplay {
		float: right;
		width: 45%;
		padding: 10px 15px;
		border: 2px solid #80d88a;
		border-radius: 5px;
	}
	.caseDetails .collectinglevelDisplay .active_step {
		font-weight: bold;
	}
	.stopCase {
		font-size: 13px;
		line-height: 15px;
		background: #194273;
		color: #FFF;
		padding: 6px 15px;
		border-radius: 3px;
		border: 1px solid transparent;
		margin: 15px 0 0 3px;
		float: right;
		cursor: pointer;
	}
	.resetTheCaseFully {
		font-size: 13px;
		line-height: 15px;
		background: #194273;
		color: #FFF;
		padding: 6px 15px;
		border-radius: 3px;
		border: 1px solid transparent;
		margin: 15px 0 0 3px;
		float: right;
		cursor: pointer;
	}
	.reactivateCase {
		font-size: 13px;
		line-height: 15px;
		background: #194273;
		color: #FFF;
		padding: 6px 15px;
		border-radius: 3px;
		border: 1px solid transparent;
		margin: 15px 0 0 3px;
		float: right;
		cursor: pointer;
	}
	.levelText {
		font-weight: bold;
		float: right;
		margin-left: 30px;
		color: #80d88a;
	}
	.processText {
		float: right;
		margin-left: 30px;
	}
	.paymentPlanTable {
		width: 60%;
	}
	.timeExplanation i {
        color: #bbb;
    }
    .timeExplanation .hoverSpan {
        display: none;
		position: absolute;
		padding: 5px 10px;
		background: #fff;
		border: 1px solid #cecece;
		max-width: 300px;
    }
    .timeExplanation:hover .hoverSpan {
        display: block;
        z-index: 100;
    }
	.processCase {
		float: left;
		margin-top: 20px;
		cursor: pointer;
		color: #46b2e2;
	}
	.createNewRest {
		float: left;
		margin-top: 20px;
		cursor: pointer;
		color: #46b2e2;

	}
	.edit_due_date {
		cursor: pointer;
		color: #46b2e2;
	}
	.edit_case_step {
		cursor: pointer;
		color: #46b2e2;
	}
	.edit_case_status {
		cursor: pointer;
		color: #46b2e2;
	}
	.edit_claim_fees_setting {
		cursor: pointer;
		color: #46b2e2;
	}
	.delete_letter {
		cursor: pointer;
		color: #46b2e2;
	}
	.showDeletedClaimLetters {
		margin-top: 10px;
		cursor: pointer;
		color: #46b2e2;
	}
	.showDeletedClaimLetters .glyphicon-menu-up {
		display: none;
	}
	.showDeletedClaimLetters.active .glyphicon-menu-down {
		display: none;
	}
	.showDeletedClaimLetters.active .glyphicon-menu-up {
		display: inline;
	}
	.deletedClaimLetters {
		margin-top: 10px;
		display: none;
	}
	.showProfileSettings {
		cursor: pointer;
		color: #46b2e2;
	}
	.regenerate_interest {
		cursor: pointer;
		color: #46b2e2;
	}
</style>
