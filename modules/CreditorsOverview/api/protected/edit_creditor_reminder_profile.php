<?php
$_POST = $v_data['params']['post'];
$creditor_id = $_POST['creditor_id'];
$profile_id = $_POST['profile_id'];
$profile_name = $_POST['profile_name'];
$profile_process_id = $_POST['process_id'];
$action = $_POST['action'];
$username = $v_data['params']['username'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");
if($creditor){
	$updated = false;
	$profile = array();
	if($profile_id){
		$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($profile_id));
		$profile = ($o_query ? $o_query->row_array() : array());
	}
	if($action == "changeProfileForPerson" || $action == "changeProfileForCompany"){
		if($profile) {
			if($action == "changeProfileForPerson"){
				$s_sql = "UPDATE creditor SET
				updated = NOW(),
				updatedBy = '".$o_main->db->escape_str($username)."',
				creditor_reminder_default_profile_id = '".$o_main->db->escape_str($profile['id'])."'
				WHERE id = '".$o_main->db->escape_str($creditor['id'])."'";
				$o_query = $o_main->db->query($s_sql);
			} else if($action == "changeProfileForCompany"){
				$s_sql = "UPDATE creditor SET
				updated = NOW(),
				updatedBy = '".$o_main->db->escape_str($username)."',
				creditor_reminder_default_profile_for_company_id = '".$o_main->db->escape_str($profile['id'])."'
				WHERE id = '".$o_main->db->escape_str($creditor['id'])."'";
				$o_query = $o_main->db->query($s_sql);
			}
		}
	} else if($action == "changeVat") {
		$s_sql = "UPDATE creditor SET
		updated = NOW(),
		updatedBy = '".$o_main->db->escape_str($username)."',
		vat_deduction = '".$o_main->db->escape_str($_POST['vat_deduction'])."'
		WHERE id = '".$o_main->db->escape_str($creditor['id'])."'";
		$o_query = $o_main->db->query($s_sql);
	}  else if($action == "deleteProfile") {
		if($profile['id'] == $creditor['creditor_reminder_default_profile_id'] || $profile['id'] == $creditor['creditor_reminder_default_profile_for_company_id']){
			$fw_error_msg[] = $formText_CanNotDeleteDefaultProcessChangeDefault_output;
		} else {
			$s_sql = "UPDATE creditor_reminder_custom_profiles SET
			updated = NOW(),
			updatedBy = '".$o_main->db->escape_str($username)."',
			content_status = 2
			WHERE id = '".$o_main->db->escape_str($profile['id'])."'";
			$o_query = $o_main->db->query($s_sql);
		}
	} else {
		$cases_connected_count = 0;
		if($profile){
			$s_sql = "SELECT * FROM collecting_cases WHERE reminder_profile_id = ?";
			$o_query = $o_main->db->query($s_sql, array($profile['id']));
			$cases_connected = ($o_query ? $o_query->num_rows() : 0);
		}
		if($cases_connected_count == 0){
			$profile_sql = "";
			if($profile_name != ""){
				$profile_sql = ", name = '".$o_main->db->escape_str($profile_name)."'";
			}
			if($profile){
				$profile_process_id = $profile['reminder_process_id'];
				$s_sql = "UPDATE creditor_reminder_custom_profiles SET
				updated = NOW(),
				updatedBy = '".$o_main->db->escape_str($username)."'".$profile_sql.",
				creditor_id = '".$o_main->db->escape_str($creditor['id'])."',
				reminder_process_id = '".$o_main->db->escape_str($profile_process_id)."'
				WHERE id = '".$o_main->db->escape_str($profile['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$profileId = $profile['id'];
			} else {
				$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
				created = NOW(),
				createdBy = '".$o_main->db->escape_str($username)."'".$profile_sql.",
				creditor_id = '".$o_main->db->escape_str($creditor['id'])."',
				reminder_process_id = '".$o_main->db->escape_str($profile_process_id)."'";
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
										$v_return['error'] = 'DuplicateAmounts';
									}
								}
							} else {
								$v_return['error'] = 'InterestError2';
							}
						} else {
							$v_return['error'] = 'InterestError';
						}
					}
				}

				// if(count($step_ids) > 0){
				// 	$s_sql = "DELETE FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ? AND collecting_cases_process_step_id NOT IN (".implode(',', $step_ids).")";
				// 	$o_query = $o_main->db->query($s_sql, array($profileId));
				// }
			}
		} else {
			$v_return['error'] = 'cases_already_started';
		}
		//
		// $customized_processes = array();
		// foreach($customized_processes_un as $customized_process) {
		// 	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		// 	$o_query = $o_main->db->query($s_sql, array($customized_process['id']));
		// 	$steps = ($o_query ? $o_query->result_array() : array());
		//
		// 	$customized_process['steps'] = $steps;
		// 	$customized_processes[] = $customized_process;
		// }
		// $default_processes = array();
		// foreach($default_processes_un as $default_process) {
		// 	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		// 	$o_query = $o_main->db->query($s_sql, array($customized_process['id']));
		// 	$steps = ($o_query ? $o_query->result_array() : array());
		//
		// 	$default_processes[] = $default_process;
		// }
		//
		// $s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE creditor_id = ?";
		// $o_query = $o_main->db->query($s_sql, array($creditor['id']));
		// $creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());
		// $processed_profiles = array();
		// foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {
		// 	$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
		// 	$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['id']));
		// 	$values = ($o_query ? $o_query->result_array() : array());
		//
		// 	$creditor_reminder_custom_profile['values'] = $values;
		// 	$processed_profiles[] = $creditor_reminder_custom_profile;
		// }
		//
		// $v_return['customized_processes'] = $customized_processes;
		// $v_return['default_processes'] = $default_processes;
		//
		// $v_return['profile'] = $profile;
	}

	$v_return['creditor'] = $creditor;
	$v_return['status'] = 1;
	
	$s_sql = "UPDATE creditor SET trigger_full_reorder = 1
	WHERE id = '".$o_main->db->escape_str($creditor['id'])."'";
	$o_query = $o_main->db->query($s_sql);
}
?>
