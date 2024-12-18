<?php
include(__DIR__."/../languagesOutput/default.php");
$_POST = $v_data['params']['post'];
$username = $v_data['params']['username'];

$creditor_id = $_POST['creditor_id'];
$s_sql = "INSERT INTO sys_log SET post = '".json_encode($v_data['params'])."'";
$o_query = $o_main->db->query($s_sql);

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = $o_query ? $o_query->row_array() : array();

$onboarding_edition = $_POST['onboarding_edition'];
$onboarding_process_progress = $_POST['onboarding_process_progress'];
$onboarding_collect_move = $_POST['onboarding_collect_move'];
$onboarding_company_process = $_POST['onboarding_company_process'];
$onboarding_person_process = $_POST['onboarding_person_process'];
$use_local_email_phone_for_reminder = $_POST['use_local_email_phone_for_reminder'];
$read_agreement = $_POST['read_agreement'];
$process_oflow_step_type_id = $_POST['process_oflow_step_type_id'];

$onboarding_process_start_from_step = $_POST['onboarding_process_start_from_step'];

$onboarding_process_start_from_step_order = 0;

$emailCorrect = false;
if(trim($creditor['companyEmail']) != ''){
	$emailCorrect = true;
} else {
	$use_local_email_phone_for_reminder = 1;
}
$local_phone = $_POST['local_phone'];
$local_email = $_POST['local_email'];
$processing_selection = $_POST['onboarding_process_type'];
$contactperson_name = $_POST['contactperson_name'];
$contactperson_email = $_POST['contactperson_email'];

if(intval($use_local_email_phone_for_reminder) == 1) {
	if($local_email != ''){
		$emailCorrect = true;
	}
}

$send_invoice_from = $_POST['invoice_from'];
$vat_deduction = intval($_POST['vat_deduction'])-1;
if($vat_deduction < 0){
	$vat_deduction = 0;
}
if($creditor){
	if($emailCorrect){
		// if($processing_selection == 1){
		// 	$onboarding_company_process = $process_oflow_step_type_id;
		// 	$onboarding_person_process = $process_oflow_step_type_id;
		// }
		if(($onboarding_company_process > 0 && $onboarding_person_process > 0) || $processing_selection) {
			$profileForPersonId = 0;
			$profileForCompanyId = 0;
			if(!$processing_selection){
				$o_query = $o_main->db->query("SELECT * FROM collecting_cases_process WHERE id = ?", array($onboarding_person_process));
				$process_for_person = $o_query ? $o_query->row_array() : array();

				$o_query = $o_main->db->query("SELECT * FROM collecting_cases_process WHERE id = ?", array($onboarding_company_process));
				$process_for_company = $o_query ? $o_query->row_array() : array();
				if($process_for_person){
					$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
					created = NOW(),
					createdBy = '".$o_main->db->escape_str("onboarding")."',
					name = '".$o_main->db->escape_str($process_for_person['name'])."',
					creditor_id = '".$o_main->db->escape_str($creditor['id'])."',
					reminder_process_id = '".$o_main->db->escape_str($process_for_person['id'])."'";
					$o_query = $o_main->db->query($s_sql);
					if($o_query){
						$profileForPersonId = $o_main->db->insert_id();
					}
				}

				if($process_for_company){
					$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
					created = NOW(),
					createdBy = '".$o_main->db->escape_str("onboarding")."',
					name = '".$o_main->db->escape_str($process_for_company['name'])."',
					creditor_id = '".$o_main->db->escape_str($creditor['id'])."',
					reminder_process_id = '".$o_main->db->escape_str($process_for_company['id'])."'";
					$o_query = $o_main->db->query($s_sql);
					if($o_query){
						$profileForCompanyId = $o_main->db->insert_id();
					}
				}
			}
			if(($profileForPersonId > 0 && $profileForCompanyId) || $processing_selection) {
				$next_process_date = date("Y-m-d 11:00:00");
				if($_POST['next_automatic_reminder_process'] == 1) {
					$next_process_date = date("Y-m-d 11:00:00", time()+86400);
				}
				$read_agreement_sql = "";
				if($read_agreement) {
					$filename = "";
					$create_agreement_file = __DIR__ . '/../../api/protected/fnc_create_agreement_file.php';
					if (file_exists($create_agreement_file)) {
					  	include $create_agreement_file;
						$result = create_agreement_file($creditor['id']);
						$filename = $result['file'];
					}
					$read_agreement_sql = ", collecting_agreement_accepted_by = '".sanitize_escape($username)."', collecting_agreement_accepted_date = NOW(), collecting_agreement_file = '".sanitize_escape($filename)."'";
				}
				$oflow_process_sql = "";
				if($processing_selection) {
					$oflow_process_sql = ", skip_reminder_go_directly_to_collecting = 2, 
					choose_progress_of_reminderprocess = 1, 
					choose_move_to_collecting_process = 1, 
					collecting_process_to_move_from_reminder = '".$o_main->db->escape_str($process_oflow_step_type_id)."'";
				} else {
					$oflow_process_sql = ", skip_reminder_go_directly_to_collecting = 0, choose_progress_of_reminderprocess = '".$o_main->db->escape_str($onboarding_process_progress)."',
					choose_move_to_collecting_process = '".$o_main->db->escape_str($onboarding_collect_move)."', collecting_process_to_move_from_reminder = 0";
				}
				$s_sql = "UPDATE creditor SET
				updated = now(),
				updatedBy= ?,				
				reminder_system_edition = ?,
				onboarding_incomplete = 0,
				creditor_reminder_default_profile_for_company_id = ?,
				creditor_reminder_default_profile_id = ?,
				next_automatic_reminder_process_time = ?,
				vat_deduction = ?,
				reminder_only_from_invoice_nr = ?,
				use_local_email_phone_for_reminder = ?,
				local_email = ?,
				local_phone = ?,
				processing_selection = ?,
				contactperson_name= ?,
				contactperson_email = ?,
				onboarding_process_start_from_step_order = ?".$read_agreement_sql.$oflow_process_sql."
				WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($username, $onboarding_edition, $profileForCompanyId, $profileForPersonId, $next_process_date, $vat_deduction, $send_invoice_from,
				$use_local_email_phone_for_reminder, $local_email, $local_phone,$processing_selection,$contactperson_name,$contactperson_email, $onboarding_process_start_from_step_order, $creditor['id']));
				if($o_query){

					$s_sql = "INSERT INTO creditor_contact_person SET
					created = NOW(),
					createdBy = '".$o_main->db->escape_str("onboarding")."',
					creditor_id = '".$o_main->db->escape_str($creditor['id'])."',
					name = '".$o_main->db->escape_str($contactperson_name)."',
					email = '".$o_main->db->escape_str($contactperson_email)."',
					messages_regarding_cases = 1,
					contactperson_for_agreement = 1,
					receive_settlement_reports = 1";
					$o_query = $o_main->db->query($s_sql);
					
					include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");
					$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE creditor_id = ? AND open = 1";
					$o_query = $o_main->db->query($s_sql, array($creditor['id']));
					$source_id = 1;
					process_open_cases_for_tabs($creditor['id'], $source_id);

					$v_return['status'] = 1;

				} else {
					$v_return['error'] = $formText_ErrorSaving_output;
				}
			} else {
				$v_return['error'] = $formText_ErrorSavingProfiles_output;
			}
		} else {
			$v_return['error'] = $formText_MissingProcesses_output;
		}
	} else {
		$v_return['error'] = $formText_MissingEmail_output;
	}
} else {
	$v_return['error'] = $formText_MissingCreditor_output;
}

?>
