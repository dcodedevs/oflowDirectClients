<?php
$profile_id = isset($_POST['profile_id']) ? $_POST['profile_id'] : 0;
$creditor_id = isset($_POST['creditor_id']) ? $_POST['creditor_id'] : 0;
include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$pdfTexts = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$emailTexts = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM process_step_types WHERE content_status < 2 ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql);
	$process_step_types = $o_query ? $o_query->result_array() : array();

	$profile = array();
	if($profile_id){
		$s_sql = "SELECT creditor_reminder_custom_profiles.*,collecting_cases_process.process_step_type_id, collecting_cases_process.available_for FROM creditor_reminder_custom_profiles
		JOIN collecting_cases_process ON collecting_cases_process.id = creditor_reminder_custom_profiles.reminder_process_id
		WHERE creditor_reminder_custom_profiles.id = ?";
		$o_query = $o_main->db->query($s_sql, array($profile_id));
		$profile = ($o_query ? $o_query->row_array() : array());
		if($profile){

			$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp
			LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
			WHERE ccp.id = '".$o_main->db->escape_str($profile['reminder_process_id'])."' ORDER BY ccp.sortnr ASC";
			$o_query = $o_main->db->query($s_sql);
			$currentProcess = ($o_query ? $o_query->row_array() : array());
			$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
			$o_query = $o_main->db->query($s_sql, array($currentProcess['id']));
			$old_steps = ($o_query ? $o_query->result_array() : array());
			$steps = array();
			foreach($old_steps as $old_step) {
				$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
				$o_query = $o_main->db->query($s_sql, array($old_step['id']));
				$fees = ($o_query ? $o_query->result_array() : array());
				$old_step['fees'] = $fees;
				$steps[] = $old_step;
			}
			$currentProcess['steps'] = $steps;

			$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ? AND content_status < 2 ORDER BY sortnr ASC";
			$o_query = $o_main->db->query($s_sql, array($profile['id']));
			$profile_value_un = $o_query ? $o_query->result_array() : array();
			$profile_values = array();
			foreach($profile_value_un as $profile_value){
				$s_sql = "SELECT * FROM creditor_reminder_custom_profile_value_fees WHERE creditor_reminder_custom_profile_value_id = ? ORDER BY mainclaim_from_amount ASC";
				$o_query = $o_main->db->query($s_sql, array($profile_value['id']));
				$fees = ($o_query ? $o_query->result_array() : array());
				$profile_value['fees'] = $fees;

				$profile_values[$profile['reminder_process_id']][$profile_value['collecting_cases_process_step_id']] = $profile_value;
			}
			$_POST['available_for'] = $profile['available_for'];
		}
	}

	$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp
	LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE (ccp.creditor_id = 0 OR ccp.creditor_id is null) 
	AND ccp.available_for = '".$o_main->db->escape_str($_POST['available_for'])."' ORDER BY ccp.sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$default_processes_un = ($o_query ? $o_query->result_array() : array());

	$default_processes = array();
	foreach($default_processes_un as $default_process) {
		$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		$o_query = $o_main->db->query($s_sql, array($default_process['id']));
		$old_steps = ($o_query ? $o_query->result_array() : array());
		$steps = array();
		foreach($old_steps as $old_step) {
			$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
			$o_query = $o_main->db->query($s_sql, array($old_step['id']));
			$fees = ($o_query ? $o_query->result_array() : array());
			$old_step['fees'] = $fees;
			$steps[] = $old_step;
		}
		$default_process['steps'] = $steps;
		$default_processes[] = $default_process;
	}

	if($_POST['action'] == "deleteProfile"){
		
		$s_sql = "SELECT * FROM collecting_cases WHERE reminder_profile_id = ?";
		$o_query = $o_main->db->query($s_sql, array($profile_id));
		$cases_connected_count = ($o_query ? $o_query->num_rows() : 0);
		if($cases_connected_count == 0){
			$s_sql = "DELETE creditor_reminder_custom_profiles FROM creditor_reminder_custom_profiles WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($profile_id));
		} else {
			$fw_return_data = "warning";
			echo $formText_CanNotDeleteWithCasesConnected_output;
		}

		return;
	}
	if($_POST['output_form_submit']) {
		$profile_process_id = $_POST['process_id'];
		$profile_type = $_POST['profile_type'];

		$cases_connected_count = 0;
		if($profile){
			$s_sql = "SELECT * FROM collecting_cases WHERE reminder_profile_id = ?";
			$o_query = $o_main->db->query($s_sql, array($profile['id']));
			$cases_connected_count = ($o_query ? $o_query->num_rows() : 0);
		}
		if($profile_process_id > 0){
		// if($cases_connected_count == 0){
			$profile_sql = "";
			if($profile_name != ""){
				$profile_sql = ", name = '".$o_main->db->escape_str($profile_name)."'";
			}
			if($profile_type > 0){
				$profile_sql .= ", type = '".$o_main->db->escape_str($profile_type)."'";

			}
			if($_POST['days_after_due_date_move_to_collecting_specify'] == ""){
				$_POST['days_after_due_date_move_to_collecting'] = "";
			}
			if($profile){
				$profile_process_id = $profile['reminder_process_id'];
				$s_sql = "UPDATE creditor_reminder_custom_profiles SET
				updated = NOW(),
				updatedBy = '".$o_main->db->escape_str($username)."'".$profile_sql.",
				creditor_id = '".$o_main->db->escape_str($creditor['id'])."',
				reminder_process_id = '".$o_main->db->escape_str($profile_process_id)."',
				specify_days_here= '".$o_main->db->escape_str($_POST['days_after_due_date_move_to_collecting_specify'])."',
				days_after_due_date_move_to_collecting = '".$o_main->db->escape_str($_POST['days_after_due_date_move_to_collecting'])."',
				collecting_process_move_to = '".$o_main->db->escape_str($_POST['collecting_process_move_to'])."'
				WHERE id = '".$o_main->db->escape_str($profile['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$profileId = $profile['id'];
			} else {
				$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
				created = NOW(),
				createdBy = '".$o_main->db->escape_str($username)."'".$profile_sql.",
				creditor_id = '".$o_main->db->escape_str($creditor['id'])."',
				reminder_process_id = '".$o_main->db->escape_str($profile_process_id)."',
				specify_days_here= '".$o_main->db->escape_str($_POST['days_after_due_date_move_to_collecting_specify'])."',
				days_after_due_date_move_to_collecting = '".$o_main->db->escape_str($_POST['days_after_due_date_move_to_collecting'])."',
				collecting_process_move_to = '".$o_main->db->escape_str($_POST['collecting_process_move_to'])."'";
				$o_query = $o_main->db->query($s_sql);
				if($o_query){
					$profileId = $o_main->db->insert_id();
				}
			}
			if($profileId > 0) {
				$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($profile_process_id));
				$process = ($o_query ? $o_query->row_array() : array());

				$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
				$o_query = $o_main->db->query($s_sql, array($process['id']));
				$steps = ($o_query ? $o_query->result_array() : array());
				$step_ids = array();
				foreach($steps as $step) {
					if($step['id'] == $_POST['step_id']){
						$step_ids[] = $step['id'];
						$email_text = $_POST['email_text'][$step['id']];
						$reminder_transaction_text = $_POST['reminder_transaction_text'][$step['id']];
						if($reminder_transaction_text == 1){
							$reminder_transaction_text_custom = $_POST['reminder_transaction_text_custom'][$step['id']];
						} else {
							$reminder_transaction_text_custom = "";
						}


						$pdf_text = $_POST['pdf_text'][$step['id']];
						$reminder_amount = $_POST['reminder_amount'][$step['id']];
						if($reminder_amount == 1){
							$reminder_amount_custom = str_replace(" ","",str_replace(",",".", $_POST['reminder_amount_custom'][$step['id']]));
						} else {
							$reminder_amount_custom = "";
						}
						$reminder_amount_type = $_POST['reminder_amount_type'][$step['id']];

						$sql_update = "";
						if(isset($_POST['pdf_text_selector'][$step['id']])) {
							if($_POST['pdf_text_selector'][$step['id']] == 1){
								if(isset($_POST['pdf_text'][$step['id']]) || isset($_POST['pdf_text_title'][$step['id']])){
									$sql_update .= ", pdftext_title = '".$o_main->db->escape_str($_POST['pdf_text_title'][$step['id']])."'";
									$sql_update .= ", pdftext_text = '".$o_main->db->escape_str($_POST['pdf_text'][$step['id']])."'";
								}
							} else {
								$sql_update .= ", pdftext_title = ''";
								$sql_update .= ", pdftext_text = ''";
							}
						}
						if(isset($_POST['add_number_of_days_to_due_date_selector'][$step['id']])) {
							if($_POST['add_number_of_days_to_due_date_selector'][$step['id']] == 1){
								if(isset($_POST['add_number_of_days_to_due_date'][$step['id']])){
									$sql_update .= ", add_number_of_days_to_due_date = '".$o_main->db->escape_str($_POST['add_number_of_days_to_due_date'][$step['id']])."'";
								}
							} else {
								$sql_update .= ", add_number_of_days_to_due_date = ''";
							}
						}
						if(isset($_POST['days_after_due_date_selector'][$step['id']])) {
							if($_POST['days_after_due_date_selector'][$step['id']] == 1){
								if(isset($_POST['days_after_due_date'][$step['id']])){
									$sql_update .= ", days_after_due_date = '".$o_main->db->escape_str($_POST['days_after_due_date'][$step['id']])."'";
								}
							} else {
								$sql_update .= ", days_after_due_date = ''";
							}
						}
						if(isset($_POST['reminder_amount'][$step['id']])){
							$sql_update .= ", reminder_amount = '".$o_main->db->escape_str($reminder_amount_custom)."'";
						}
						if(isset($_POST['reminder_fee'][$step['id']])){
							$sql_update .= ", doNotAddFee = '".$o_main->db->escape_str($_POST['reminder_fee'][$step['id']])."'";
						}
						if(isset($_POST['sending_action'][$step['id']])){
							$sql_update .= ", sending_action = '".$o_main->db->escape_str($_POST['sending_action'][$step['id']])."'";
						}
						if(isset($_POST['show_collecting_company_logo'][$step['id']])){
							$sql_update .= ", show_collecting_company_logo = '".$o_main->db->escape_str($_POST['show_collecting_company_logo'][$step['id']])."'";
						}
						if(isset($_POST['extra_text_in_sms'][$step['id']])) {
							$sql_update .= ", extra_text_in_sms = '".$o_main->db->escape_str($_POST['extra_text_in_sms'][$step['id']])."'";
						}
						$sql_update .= ", reminder_amount_type = '".$o_main->db->escape_str($reminder_amount_type)."'";

						$noInterestError = true;
						$noInterestError2 = true;
						if(isset($_POST['reminder_interest'][$step['id']])){
							$notAddInterest = $_POST['reminder_interest'][$step['id']];
							if($notAddInterest == 0){
								$notAddInterest = $step['doNotAddInterest'] + 1;
							}
							if($notAddInterest == 2){
								foreach($steps as $single_step) {
									if($single_step['id'] == $step['id']) {
										break;
									}
									$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE collecting_cases_process_step_id = ? AND creditor_reminder_custom_profile_id = ?";
									$o_query = $o_main->db->query($s_sql, array($single_step['id'], $profile['id']));
									$step_profile_value = ($o_query ? $o_query->row_array() : array());
									if($step_profile_value['doNotAddInterest'] > 0) {
										if($step_profile_value['doNotAddInterest'] == 1){
											$noInterestError = false;
										}
									} else {
										if(!$single_step['doNotAddInterest']){
											$noInterestError = false;
										}
									}
								}
							} else if($notAddInterest == 1){
								$afterCurrentStep = false;
								foreach($steps as $single_step) {
									if($afterCurrentStep) {
										$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE collecting_cases_process_step_id = ? AND creditor_reminder_custom_profile_id = ?";
										$o_query = $o_main->db->query($s_sql, array($single_step['id'], $profile['id']));
										$step_profile_value = ($o_query ? $o_query->row_array() : array());
										if($step_profile_value['doNotAddInterest'] > 0) {
											if($step_profile_value['doNotAddInterest'] == 2){
												$noInterestError2 = false;
											}
										} else {
											if($single_step['doNotAddInterest']){
												$noInterestError2 = false;
											}
										}
									}
									if($single_step['id'] == $step['id']) {
										$afterCurrentStep = true;
									}
								}
							}
							$sql_update .= ", doNotAddInterest = '".$o_main->db->escape_str($_POST['reminder_interest'][$step['id']])."'";
						}
						if($noInterestError){
							if($noInterestError2){
								$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE collecting_cases_process_step_id = ? AND creditor_reminder_custom_profile_id = ?";
								$o_query = $o_main->db->query($s_sql, array($step['id'], $profile['id']));
								$step_profile_value = ($o_query ? $o_query->row_array() : array());
								if($step_profile_value) {
									$s_sql = "UPDATE creditor_reminder_custom_profile_values SET
									updated = NOW(),
									updatedBy = '".$o_main->db->escape_str($username)."',
									creditor_reminder_custom_profile_id = '".$o_main->db->escape_str($profileId)."',
									collecting_cases_process_step_id = '".$o_main->db->escape_str($step['id'])."',
									collecting_cases_emailtext_id = '".$o_main->db->escape_str($email_text)."',
									reminder_transaction_text = '".$o_main->db->escape_str($reminder_transaction_text_custom)."'".$sql_update."
									WHERE id = '".$o_main->db->escape_str($step_profile_value['id'])."'";
									$o_query = $o_main->db->query($s_sql);
									$profile_value_id = $step_profile_value['id'];
								} else {
									$s_sql = "INSERT INTO creditor_reminder_custom_profile_values SET
									created = NOW(),
									createdBy = '".$o_main->db->escape_str($username)."',
									creditor_reminder_custom_profile_id = '".$o_main->db->escape_str($profileId)."',
									collecting_cases_process_step_id = '".$o_main->db->escape_str($step['id'])."',
									collecting_cases_emailtext_id = '".$o_main->db->escape_str($email_text)."',
									reminder_transaction_text = '".$o_main->db->escape_str($reminder_transaction_text_custom)."'".$sql_update;
									$o_query = $o_main->db->query($s_sql);
									$profile_value_id = $o_main->db->insert_id();
								}
								if($profile_value_id > 0){

									$s_sql = "SELECT * FROM creditor_reminder_custom_profile_value_fees WHERE creditor_reminder_custom_profile_value_id = ? ORDER BY mainclaim_from_amount ASC";
									$o_query = $o_main->db->query($s_sql, array($profile_value_id));
									$current_fees = ($o_query ? $o_query->result_array() : array());

									$updated_fee_ids = array();
									$all_amounts_from = array();
									$hasDuplicateMainClaimFromAmount = false;
									foreach($_POST['amount'] as $key=>$value) {
										$value = str_replace(" ", "", str_replace(",", ".", $value));
										if($value != ""){
											$mainclaim_from_amount = str_replace(" ", "", str_replace(",", ".", $_POST['mainclaim_from_amount'][$key]));
											if(!in_array($mainclaim_from_amount, $all_amounts_from)){
												$all_amounts_from[] = $mainclaim_from_amount;
											} else {
												$hasDuplicateMainClaimFromAmount = true;
											}
										}
									}
									if(!$hasDuplicateMainClaimFromAmount) {
										if($reminder_amount_type == 1) {
											if($_POST['reminder_amount'][$step['id']]) {
												foreach($_POST['amount'] as $key=>$value) {
													$value = str_replace(" ", "", str_replace(",", ".", $value));
													$mainclaim_from_amount = str_replace(" ", "", str_replace(",", ".", $_POST['mainclaim_from_amount'][$key]));
													$fee_id = 0;
													if(strpos($key, "new_") === false){
														$fee_id = $key;
													}
													if($value != "") {
														if($fee_id > 0) {
															$sql = "UPDATE creditor_reminder_custom_profile_value_fees SET
														   updated = now(),
														   updatedBy='".$variables->loggID."',
														   amount = '".$value."',
														   mainclaim_from_amount = '".$mainclaim_from_amount."'
														   WHERE id = '".$fee_id."'";

														   $o_query = $o_main->db->query($sql);
														   $updated_fee_ids[] = $fee_id;
													   } else {
														   $sql = "INSERT INTO creditor_reminder_custom_profile_value_fees SET
														  created = now(),
														  createdBy='".$variables->loggID."',
														  amount = '".$value."',
														  mainclaim_from_amount = '".$mainclaim_from_amount."',
														  creditor_reminder_custom_profile_value_id = '".$profile_value_id."'";

														  $o_query = $o_main->db->query($sql);
														  if($o_query) {
															  $updated_fee_ids[] = $o_main->db->insert_id();
														  }
													   }
												   }
												}
											}
										}
										foreach($current_fees as $current_fee) {
											if(!in_array($current_fee['id'], $updated_fee_ids)) {
												$sql = "DELETE FROM creditor_reminder_custom_profile_value_fees WHERE id = '".$current_fee['id']."'";
												$o_query = $o_main->db->query($sql);
											}
										}
									} else {
										$fw_error_msg[] = 'DuplicateAmounts';
									}
								}
							} else {
								$fw_error_msg[]  = 'InterestError2';
							}
						} else {
							$fw_error_msg[]  = 'InterestError';
						}
					}
				}
				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$creditor_id;
				
			} else {
				$fw_error_msg[]  = 'failed to update';
			}
		// } else {
		// 	$fw_error_msg[]  = 'there are cases connected to profile';
		// }
		} else{
			$fw_error_msg[]  = 'Missing process';
		}
		if(count($fw_error_msg) == 0) {
			//trigger reordering 	

			$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
			WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND (open = 1 OR IFNULL(tab_status, 0) <> 0)";
			$o_query = $o_main->db->query($s_sql);
			process_open_cases_for_tabs($creditor['id'], 6);
		}

		return;
	}
	?>
	<div class="popupform">
		<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_creditor&inc_obj=ajax&inc_act=edit_profile";?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="creditor_id" value="<?php print $_POST['creditor_id'];?>">
			<input type="hidden" name="profile_id" value="<?php print $_POST['profile_id'];?>">
			<input type="hidden" name="available_for" value="<?php print $_POST['available_for'];?>">

			<div class="inner">
				<?php

				if($_POST['action'] == "changeText" || $_POST['action'] == "changeAmount" || $_POST['action'] == "changeAmount2" ||  $_POST['action'] == "changeInterest" ||  $_POST['action'] == "changeShowLogo" || $_POST['action'] == "changeFee" || $_POST['action'] == "changeSending" || $_POST['action'] == "changeSmsText" || $_POST['action'] == "changeBeforeDays" || $_POST['action'] == "changeAfterDays") {
					if($currentProcess){
						$steps = $currentProcess['steps'];
					?>
						<input type="hidden" name="process_id" value="<?php echo $currentProcess['id']?>"/>
						<input type="hidden" name="step_id" value="<?php echo $_POST['step_id']?>"/>
						<?php
						foreach($steps as $step) {
							if($step['id'] == $_POST['step_id']){
								$stepPdfText = array();

								foreach($pdfTexts as $letter) {
									if($letter['id'] == $step['collecting_cases_pdftext_id']) {
										$stepPdfText = $letter;
									}
								}
								$stepEmailText = array();

								foreach($emails as $letter) {
									if($letter['id'] == $step['collecting_cases_emailtext_id']) {
										$stepEmailText = $letter;
									}
								}
								$profile_value = $profile_values[$currentProcess['id']][$step['id']];
								?>
								<div class="stepWrapper">
									<div class="stepName"><?php echo $step['name']; ?> </div>
									<?php
									if($_POST['action'] == "changeText") { ?>
									<div class="line">
										<div class="lineTitle"><?php echo $formText_PdfText_Output; ?></div>
										<div class="lineInput">
											<select name="pdf_text_selector[<?php echo $step['id']?>]" class="popupforminput changePdfText" autocomplete="off" data-title="<?php echo htmlspecialchars($stepPdfText['title']);?>" data-text="<?php echo htmlspecialchars($stepPdfText['text']);?>">
												<option value=""><?php echo $formText_UseDefault_output;?> (<?php if($stepPdfText) { ?><?php echo $stepPdfText['name']?> <?php } else { echo $formText_None_output; }?>)</option>
												<option value="1" <?php if($profile_value['pdftext_title'] != "" || $profile_value['pdftext_text'] != "") { echo 'selected';}?>><?php echo $formText_Customize_output;?></option>
											</select>
										</div>
										<div class="clear"></div>
										<br/>
										<div class="pdf_custom_values">
											<div class="line">
												<div class="lineTitle"><?php echo $formText_TitlePdf_Output; ?></div>
												<div class="lineInput">
													<input type="text" autocomplete="off" class="popupforminput botspace title_pdf_input" value="<?php echo $profile_value['pdftext_title']; ?>" name="pdf_text_title[<?php echo $step['id']?>]"/>
												</div>
												<div class="clear"></div>
											</div>
											<div class="line">
												<div class="lineTitle"><?php echo $formText_TextPdf_Output; ?></div>
												<div class="lineInput">
													<textarea autocomplete="off" class="popupforminput botspace text_pdf_input" name="pdf_text[<?php echo $step['id']?>]"><?php echo $profile_value['pdftext_text']; ?></textarea>
												</div>
												<div class="clear"></div>
											</div>
										</div>
									</div>
									<?php } else if($_POST['action'] == "changeAmount") { ?>
									<?php /*?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_EmailText_Output; ?></div>
											<div class="lineInput">
												<select name="email_text[<?php echo $step['id']?>]" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?> (<?php if($stepEmailText) { ?><?php echo $stepEmailText['subject']?> <?php } else { echo $formText_None_output; }?>)</option>
													<?php
													foreach($emails as $letter) {
														?>
														<option value="<?php echo $letter['id'];?>" <?php if($letter['id'] == $profile_value['collecting_cases_emailtext_id']) echo 'selected';?>><?php echo $letter['subject'];?></option>
														<?php
													}
													?>
												</select>
											</div>
											<div class="clear"></div>
										</div>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_ReminderTransactionText_Output; ?></div>
											<div class="lineInput">
												<select name="reminder_transaction_text[<?php echo $step['id']?>]" class="changeReminderText" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?> (<?php echo $step['reminder_transaction_text']?>)</option>
													<option value="1" <?php if($profile_value['reminder_transaction_text'] != "") { echo 'selected';}?>><?php echo $formText_Customize_output;?></option>
												</select>
												<input type="text" class="popupforminput botspace reminder_transaction_text_custom" value="<?php echo $profile_value['reminder_transaction_text']?>" name="reminder_transaction_text_custom[<?php echo $step['id']?>]"/>
											</div>
											<div class="clear"></div>
										</div>*/?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_ReminderAmount_Output; ?></div>
											<div class="lineInput">
												<select name="reminder_amount[<?php echo $step['id']?>]" class="popupforminput changeReminderAmount" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?> (<?php echo number_format($step['reminder_amount'], 2, ","," ")?>)</option>
													<option value="1" <?php if($profile_value['reminder_amount'] > 0) { echo 'selected';}?>><?php echo $formText_Customize_output;?></option>
												</select>
												<input type="text" autocomplete="off" class="popupforminput botspace reminder_amount_custom" value="<?php echo number_format($profile_value['reminder_amount'], 2, ","," ")?>" name="reminder_amount_custom[<?php echo $step['id']?>]"/>
											</div>
											<div class="clear"></div>
										</div>
									<?php } else if($_POST['action'] == "changeAmount2") {
										?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_ReminderAmountType_Output; ?></div>
											<div class="lineInput">
												<select name="reminder_amount_type[<?php echo $step['id']?>]" class="popupforminput botspace changeReminderAmountType" autocomplete="off">
													<option value=""><?php echo $formText_UseSingle_output;?> </option>
													<option value="1" <?php if($profile_value['reminder_amount_type'] == 1) { echo 'selected';}?>><?php echo $formText_UseMultiple_output;?></option>
												</select>
											</div>
											<div class="clear"></div>
										</div>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_ReminderAmount_Output; ?></div>
											<div class="lineInput">
												<select name="reminder_amount[<?php echo $step['id']?>]" class="popupforminput changeReminderAmount2" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?> </option>
													<option value="1" <?php if(count($profile_value['fees']) > 0 || $profile_value['reminder_amount'] > 0) { echo 'selected';}?>><?php echo $formText_Customize_output;?></option>
												</select>
												<div class="reminder_single_default">
													<?php echo number_format($step['reminder_amount'], 2, ","," ")?>
												</div>
												<div class="reminder_single_custom">
													<input type="text" autocomplete="off" class="popupforminput botspace" value="<?php echo number_format($profile_value['reminder_amount'], 2, ","," ")?>" name="reminder_amount_custom[<?php echo $step['id']?>]"/>
												</div>
												<div class="reminder_step_values">
													<?php
													foreach($step['fees'] as $fee) {
														?>
														<div class="fee_block">
															<div class="line">
																<div class="lineTitle"><?php echo $formText_MainClaimFrom_Output; ?></div>
																<div class="lineInput">
																	<?php echo number_format($fee['mainclaim_from_amount'], 2, ",", " "); ?>
																</div>
																<div class="clear"></div>
															</div>

															<div class="line">
																<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
																<div class="lineInput">
																	<?php echo number_format($fee['amount'], 2, ",", " "); ?>
																</div>
																<div class="clear"></div>
															</div>
														</div>
													<?php } ?>
												</div>

												<div class="reminder_custom_values">
													<div class="fee_list">
													<?php
													if(count($profile_value['fees']) > 0) {
														foreach($profile_value['fees'] as $fee) {
															?>
															<div class="fee_block">
																<?php if(intval($fee['mainclaim_from_amount']) != 0) { ?>
																	<div class="line">
																		<div class="lineTitle"><?php echo $formText_MainClaimFrom_Output; ?></div>
																		<div class="lineInput">
																			<input type="text" class="popupforminput botspace" autocomplete="off" name="mainclaim_from_amount[<?php echo $fee['id'];?>]" value="<?php echo number_format($fee['mainclaim_from_amount'], 2, ",", " "); ?>">
																		</div>
																		<div class="clear"></div>
																	</div>
																<?php } ?>

																<div class="line">
																	<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
																	<div class="lineInput">
																		<input type="text" class="popupforminput botspace" autocomplete="off" name="amount[<?php echo $fee['id'];?>]" value="<?php echo number_format($fee['amount'], 2, ",", " "); ?>">
																	</div>
																	<div class="clear"></div>
																</div>
																<span class="delete_fee_block glyphicon glyphicon-trash"></span>
																<div class="clear"></div>
															</div>
														<?php }
													} else {
														?>
														<div class="fee_block">
															<div class="line">
																<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
																<div class="lineInput">
																	<input type="text" class="popupforminput botspace" autocomplete="off" name="amount[new_0]" value="">
																</div>
																<div class="clear"></div>
															</div>
														</div>
														<?php
													} ?>
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
														 <span class="delete_fee_block glyphicon glyphicon-trash"></span>
														 <div class="clear"></div>
													 </div>
												 </div>
												</div>
											</div>
											<div class="clear"></div>
										</div>
										<?php
									} else if($_POST['action'] == "changeFee") { ?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_AddFee_Output; ?></div>
											<div class="lineInput">
												<select name="reminder_fee[<?php echo $step['id']?>]" class="popupforminput changeReminderAmount" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?>: <?php if(!$step['doNotAddFee']){ echo $formText_Yes_output; } else { echo $formText_No_output; }?></option>
													<option value="1" <?php if($profile_value['doNotAddFee'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_output;?></option>
													<option value="2" <?php if($profile_value['doNotAddFee'] == 2) { echo 'selected';}?>><?php echo $formText_No_output;?></option>
												</select>
											</div>
											<div class="clear"></div>
										</div>
									<?php } else if($_POST['action'] == "changeInterest") { ?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_AddInterest_Output; ?></div>
											<div class="lineInput">
												<select name="reminder_interest[<?php echo $step['id']?>]" class="popupforminput changeReminderAmount" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?>: <?php if(!$step['doNotAddInterest']){ echo $formText_Yes_output; } else { echo $formText_No_output; }?></option>
													<option value="1" <?php if($profile_value['doNotAddInterest'] == 1) { echo 'selected';}?>><?php echo $formText_Yes_output;?></option>
													<option value="2" <?php if($profile_value['doNotAddInterest'] == 2) { echo 'selected';}?>><?php echo $formText_No_output;?></option>
												</select>
											</div>
											<div class="clear"></div>
										</div>
									<?php } else if($_POST['action'] == "changeSending") { ?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_SendingAction_Output; ?></div>
											<div class="lineInput">
												<select name="sending_action[<?php echo $step['id']?>]" class="popupforminput" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?>: <?php if($step['sending_action'] == 1){ echo $formText_SendLetter_output; } else if($step['sending_action'] == 2) { echo $formText_SendEmailIfEmailExistsOrElseLetter_output; }?></option>
													<option value="1" <?php if($profile_value['sending_action'] == 1) { echo 'selected';}?>><?php echo $formText_SendLetter_output;?></option>
													<option value="2" <?php if($profile_value['sending_action'] == 2) { echo 'selected';}?>><?php echo $formText_SendEmailIfEmailExistsOrElseLetter_output;?></option>
													<option value="4" <?php if($profile_value['sending_action'] == 4) { echo 'selected';}?>><?php echo $formText_SendSmsIfMobileExistsOrEmailOrElseLetter_output;?></option>
												</select>
											</div>
											<div class="clear"></div>
										</div>
									<?php } else if($_POST['action'] == "changeShowLogo") { ?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_ShowCollectingCompanyLogo_Output; ?></div>
											<div class="lineInput">
												<select name="show_collecting_company_logo[<?php echo $step['id']?>]" class="popupforminput" autocomplete="off">
													<option value=""><?php echo $formText_UseDefault_output;?>: <?php if($step['show_collecting_company_logo']){ echo $formText_Yes_output; } else { echo $formText_No_output; }?></option>
													<option value="1" <?php if($profile_value['show_collecting_company_logo'] == 1) { echo 'selected';}?>><?php echo $formText_No_output;?></option>
													<option value="2" <?php if($profile_value['show_collecting_company_logo'] == 2) { echo 'selected';}?>><?php echo $formText_Yes_output;?></option>
												</select>
											</div>
											<div class="clear"></div>
										</div>
									<?php } else if($_POST['action'] == "changeBeforeDays") {
										?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_AddNumberOfDaysToDueDate_Output; ?></div>
											<div class="lineInput">
												<select name="add_number_of_days_to_due_date_selector[<?php echo $step['id']?>]" class="popupforminput changeBeforeDays" autocomplete="off" data-days="<?php echo htmlspecialchars($step['add_number_of_days_to_due_date']);?>">
													<option value=""><?php echo $formText_UseDefault_output;?> (<?php echo $step['add_number_of_days_to_due_date'];?>)</option>
													<option value="1" <?php if($profile_value['add_number_of_days_to_due_date'] != "") { echo 'selected';}?>><?php echo $formText_Customize_output;?></option>
												</select>
											</div>
											<div class="clear"></div>
											<br/>
											<div class="days_custom_values">
												<div class="line">
													<div class="lineTitle"><?php echo $formText_AddNumberOfDaysToDueDate_Output; ?></div>
													<div class="lineInput">
														<input type="text" autocomplete="off" class="popupforminput botspace" value="<?php echo $profile_value['add_number_of_days_to_due_date']; ?>" name="add_number_of_days_to_due_date[<?php echo $step['id']?>]"/>
													</div>
													<div class="clear"></div>
												</div>
											</div>
										</div>
										<?php
									}  else if($_POST['action'] == "changeAfterDays") {
										?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_DaysAfterDueDate_Output; ?></div>
											<div class="lineInput">
												<select name="days_after_due_date_selector[<?php echo $step['id']?>]" class="popupforminput changeAfterDays" autocomplete="off" data-days="<?php echo htmlspecialchars($step['days_after_due_date']);?>">
													<option value=""><?php echo $formText_UseDefault_output;?> (<?php echo $step['days_after_due_date']?>)</option>
													<option value="1" <?php if($profile_value['days_after_due_date'] != "") { echo 'selected';}?>><?php echo $formText_Customize_output;?></option>
												</select>
											</div>
											<div class="clear"></div>

											<br/>
											<div class="days_custom_values">
												<div class="line">
													<div class="lineTitle"><?php echo $formText_DaysAfterDueDate_Output; ?></div>
													<div class="lineInput">
														<input type="text" autocomplete="off" class="popupforminput botspace" value="<?php echo $profile_value['days_after_due_date']; ?>" name="days_after_due_date[<?php echo $step['id']?>]"/>
													</div>
													<div class="clear"></div>
												</div>
											</div>
										</div>
										<?php
									} else if($_POST['action'] == "changeSmsText") {
										?>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_ExtraSmsText_Output; ?></div>
											<div class="lineInput">
												<textarea class="popupforminput botspace" name="extra_text_in_sms[<?php echo $step['id']?>]"><?php echo $profile_value['extra_text_in_sms'];?></textarea>
											</div>
											<div class="clear"></div>
										</div>
										<?php
									}  ?>
								</div>
								<?php
							}
						}
					}
				} else {
					?>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_ProfileType_Output; ?></div>
						<div class="lineInput">
							<select name="profile_type" required>
								<option value=""><?php echo $formText_Select_output?></option>
								<option value="0" <?php if(intval($profile['type']) == 0) echo 'selected';?>><?php echo $formText_CreditorSpecified_output?></option>
								<option value="1" <?php if($profile['type'] == 1) echo 'selected';?>><?php echo $formText_OflowSpecified_output?></option>
							</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_ProcessStepType_Output; ?></div>
						<div class="lineInput">
							<select name="process_step_type_id" class="processStepType" required>
								<option value=""><?php echo $formText_Select_output?></option>
								<?php foreach($process_step_types as $type) { ?>
									<option value="<?php echo $type['id']?>" <?php if($profile['process_step_type_id'] == $type['id']) echo 'selected';?>><?php echo $type['name'];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="clear"></div>
					</div>
					<?php
					foreach($process_step_types as $type) {
						?>
						<div class="line stepTypeWrapper stepTypeWrapper<?php echo $type['id'];?>">
							<?php
							foreach($default_processes as $default_process) {
								if($default_process['process_step_type_id'] == $type['id']){
									$steps = $default_process['steps'];
									?>
									<div class="linewrapper">
										<div class="lineTitle"><input  class="processSelector" type="radio" autocomplete="off" name="process_id" <?php if($profile['reminder_process_id'] == $default_process['id']) echo 'checked';?> value="<?php echo $default_process['id'];?>" id="process_<?php echo $default_process['id']?>"/><label for="process_<?php echo $default_process['id']?>"><?php echo $default_process['fee_level_name']; ?></label></div>
										<div class="lineInput">
											<?php
											foreach($steps as $step) {
												$stepPdfText = array();

												foreach($pdfTexts as $letter) {
													if($letter['id'] == $step['collecting_cases_pdftext_id']) {
														$stepPdfText = $letter;
													}
												}
												$stepEmailText = array();

												foreach($emails as $letter) {
													if($letter['id'] == $step['collecting_cases_emailtext_id']) {
														$stepEmailText = $letter;
													}
												}
												$profile_value = $profile_values[$default_process['id']][$step['id']];
												?>
												<div class="stepWrapper">
													<?php /*?>
													<div class="line">
														<div class="lineTitle"><?php echo $formText_EmailText_Output; ?></div>
														<div class="lineInput">
															<select name="email_text[<?php echo $step['id']?>]" autocomplete="off">
																<option value=""><?php echo $formText_UseDefault_output;?> (<?php if($stepEmailText) { ?><?php echo $stepEmailText['subject']?> <?php } else { echo $formText_None_output; }?>)</option>
																<?php
																foreach($emails as $letter) {
																	?>
																	<option value="<?php echo $letter['id'];?>" <?php if($letter['id'] == $profile_value['collecting_cases_emailtext_id']) echo 'selected';?>><?php echo $letter['subject'];?></option>
																	<?php
																}
																?>
															</select>
														</div>
														<div class="clear"></div>
													</div>
													<div class="line">
														<div class="lineTitle"><?php echo $formText_ReminderTransactionText_Output; ?></div>
														<div class="lineInput">
															<select name="reminder_transaction_text[<?php echo $step['id']?>]" class="changeReminderText" autocomplete="off">
																<option value=""><?php echo $formText_UseDefault_output;?> (<?php echo $step['reminder_transaction_text']?>)</option>
																<option value="1" <?php if($profile_value['reminder_transaction_text'] != "") { echo 'selected';}?>><?php echo $formText_Customize_output;?></option>
															</select>
															<input type="text" class="popupforminput botspace reminder_transaction_text_custom" value="<?php echo $profile_value['reminder_transaction_text']?>" name="reminder_transaction_text_custom[<?php echo $step['id']?>]"/>
														</div>
														<div class="clear"></div>
													</div>*/?>
													<div class="line">
														<div class="lineTitle"><?php echo $step['name']; ?></div>
														<div class="lineInput">
															<div style="padding: 5px 0px">
																<?php echo $formText_ReminderAmount_Output;?> (<?php echo number_format($step['reminder_amount'], 2, ","," ")?>)
															</div>
														</div>
														<div class="clear"></div>
													</div>
												</div>
												<?php
											}
											?>
										</div>
										<div class="clear"></div>
									</div>
									<?php
								}
							}
							?>
						</div>
						<?php
					}
					?>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_CollectingProcessMoveTo_Output; ?></div>
						<div class="lineInput">
							<select name="collecting_process_move_to" class="processMoveTo">
								<option value=""><?php echo $formText_Default_output?></option>
								<?php 		
								foreach($default_processes as $default_process){
									$sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
									$o_query = $o_main->db->query($sql, array($default_process['alternative_process_move_to']));
									$alternative_collecting_processes = $o_query ? $o_query->row_array() : array();
									if($alternative_collecting_processes){
										?>
										<option <?php if($default_process['id'] != $profile['reminder_process_id']) { ?>style="display: none;" <?php } ?> value="<?php echo $alternative_collecting_processes['id']?>" data-process-id="<?php echo $default_process['id'];?>" <?php if($profile['collecting_process_move_to'] == $alternative_collecting_processes['id']) echo 'selected';?>><?php echo $alternative_collecting_processes['name'];?></option>
										<?php
									}
								} ?>
							</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_DaysAfterDueDateMoveToCollecting_Output; ?></div>
						<div class="lineInput">
							<select name="days_after_due_date_move_to_collecting_specify" class="specifyHereDays" autocomplete="off">
								<option value=""><?php echo $formText_Default_output?></option>
								<option value="1" <?php if($profile['specify_days_here'] == 1) echo 'selected';?>><?php echo $formText_SpecifyHere_output?></option>
							</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="line daysAfterDueDateWrapper">
						<div class="lineTitle"><?php echo $formText_DaysAfterDueDateMoveTo_collecting_Output; ?></div>
						<div class="lineInput">
							<input type="text" class="popupforminput botspace" autocomplete="off" name="days_after_due_date_move_to_collecting" value="<?php if($profile['days_after_due_date_move_to_collecting'] > 0) echo $profile['days_after_due_date_move_to_collecting']; ?>">
						</div>
						<div class="clear"></div>
					</div>
				<?php } ?>
			</div>
			<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
		</form>
	</div>
	<style>

	.stepWrapper {
	}
	.stepWrapper .line {
	}
	.stepWrapper .stepName {
		font-weight: bold;
		font-size: 14px;
	}
	.processWrapper {
		display: none;
	}
	.reminder_amount_custom {
		margin-top: 10px;
		display: none;
	}
	.reminder_transaction_text_custom {
		margin-top: 10px;
		display: none;
	}
	.stepTypeWrapper {
		display: none;
	}
	.stepTypeWrapper label {
		margin-left: 10px;
	}
	.stepTypeWrapper .linewrapper {
		border-bottom: 1px solid #cecece;
		margin-bottom: 10px;
	}
	.pdf_custom_values {
		display: none;
	}
	.days_custom_values {
		display: none;
	}
	.initial_fee_block {
		display: none;
	}
	.add_more_fee {
		cursor: pointer;
		color: #1c5daf;
	}
	.fee_block {
		border-bottom: 1px solid #cecece;
		padding-top: 10px;
	}
	.fee_block .delete_fee_block  {
		cursor: pointer;
		color: #1c5daf;
		float: right;
		margin-bottom: 10px;
	}
	.reminder_single_default {
		margin-top: 10px;
	}
	.reminder_single_custom {
		margin-top: 10px;
	}
	.daysAfterDueDateWrapper {
		display: none;
	}
	</style>
	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript">

	$(".processSelector").change(function(){
		var process_id = $(this).val();
		$(".processWrapper").hide();
		$(".processMoveTo option:not(:first)").hide();
		if(process_id != "") {
			$(".processWrapper"+process_id).show();
			console.log(process_id, $(".processMoveTo option[data-process-id='"+process_id+"']"));
			$(".processMoveTo option[data-process-id='"+process_id+"']").show();
		}
		$(window).resize();
	})
	$(".processStepType").change(function(){
		var process_id = $(this).val();
		$(".stepTypeWrapper").hide();
		if(process_id != "") {
			$(".stepTypeWrapper"+process_id).show();
		}
		$(window).resize();
	}).change();
	$(".changeReminderText").change(function(){
		var parent = $(this).parent(".lineInput");
		if($(this).val() == 1){
			parent.find(".reminder_transaction_text_custom").show();
		} else {
			parent.find(".reminder_transaction_text_custom").hide();
		}
		$(window).resize();
	}).change();
	$(".changeReminderAmount").change(function(){
		var parent = $(this).parent(".lineInput");
		if($(this).val() == 1){
			parent.find(".reminder_amount_custom").show();
		} else {
			parent.find(".reminder_amount_custom").hide();
		}
		$(window).resize();
	}).change();
	function triggerAmountChange(el) {
		var parentMain = el.parents(".stepWrapper");
		var changeReminderAmountType = parentMain.find(".changeReminderAmountType");
		var changeReminderAmount = parentMain.find(".changeReminderAmount2");

		var parent = changeReminderAmount.parent(".lineInput");
		if(changeReminderAmountType.val() == 1){
			parent.find(".reminder_single_custom").hide();
			parent.find(".reminder_single_default").hide();

			if(changeReminderAmount.val() == 1){
				parent.find(".reminder_step_values").hide();
				parent.find(".reminder_custom_values").show();
			} else {
				parent.find(".reminder_custom_values").hide();
				parent.find(".reminder_step_values").show();
			}
		} else {
			parent.find(".reminder_step_values").hide();
			parent.find(".reminder_custom_values").hide();

			if(changeReminderAmount.val() == 1){
				parent.find(".reminder_single_default").hide();
				parent.find(".reminder_single_custom").show();
			} else {
				parent.find(".reminder_single_custom").hide();
				parent.find(".reminder_single_default").show();
			}
		}
		$(window).resize();
	}
	$(".changeReminderAmountType").change(function(){
		triggerAmountChange($(this));
	})
	$(".changeReminderAmount2").change(function(){
		triggerAmountChange($(this));
		// var parent = $(this).parent(".lineInput");
		// if($(this).val() == 1){
		// 	parent.find(".reminder_step_values").hide();
		// 	parent.find(".reminder_custom_values").show();
		// } else {
		// 	parent.find(".reminder_custom_values").hide();
		// 	parent.find(".reminder_step_values").show();
		// }
		// $(window).resize();
	}).change();
	$(".changePdfText").change(function(){
		var parent = $(this).parents(".line");
		if($(this).val() == 1){
			if($(".title_pdf_input").val() == "" && $(".text_pdf_input").html() == ""){
				$(".title_pdf_input").val($(this).data("title"));
				$(".text_pdf_input").html($(this).data("text"));
			}
			parent.find(".pdf_custom_values").show();
		} else {
			parent.find(".pdf_custom_values").hide();
		}
		$(window).resize();
	}).change();

	$(".changeAfterDays").change(function(){
		var parent = $(this).parents(".line");
		if($(this).val() == 1){
			parent.find(".days_custom_values").show();
		} else {
			parent.find(".days_custom_values").hide();
		}
		$(window).resize();
	}).change();
	$(".changeBeforeDays").change(function(){
		var parent = $(this).parents(".line");
		if($(this).val() == 1){
			parent.find(".days_custom_values").show();
		} else {
			parent.find(".days_custom_values").hide();
		}
		$(window).resize();
	}).change();
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
				data[obj.name] = obj.value;
			});

			$("#popup-validate-message").html("").hide();
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: data,
				success: function (data) {
					fw_loading_end();
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append(value);
						});
						$("#popup-validate-message").show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else  if(data.redirect_url !== undefined)
					{
						// $('#popupeditboxcontent').html(data.html);
						out_popup.addClass("close-reload");
						out_popup.close();
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
	$(".specifyHereDays").off("change").on("change", function(){
		if($(this).val() == 1){
			$(".daysAfterDueDateWrapper").show();
		} else {
			$(".daysAfterDueDateWrapper").hide();
		}
	}).change();
	</script>
<?php } else {
	echo $formText_MissingCreditor_output;
}?>
