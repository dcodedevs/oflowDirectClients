<?php
include(__DIR__."/../languagesOutput/default.php");

$case_id = $v_data['params']['case_id'];
$transaction_id = $v_data['params']['transaction_id'];
$customer_id = $v_data['params']['customer_id'];
$username = $v_data['params']['username'];
$choose_reminder_profile = $v_data['params']['choose_reminder_profile'];
$choose_move_to_collecting = $v_data['params']['choose_move_to_collecting'];
$choose_progress_of_reminderprocess = $v_data['params']['choose_progress_of_reminderprocess'];
$never_send_by_ehf = $v_data['params']['never_send_by_ehf'];
$prefer_email_before_print_or_ehf = $v_data['params']['prefer_email_before_print_or_ehf'];

include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");

if(isset($case_id) && $case_id > 0)
{
    $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($case_id));
    if($o_query && $o_query->num_rows() == 1) {
		$collecting_cases = $o_query ? $o_query->row_array() : array();
		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($collecting_cases['creditor_id']));
		$creditor = $o_query ? $o_query->row_array() : array();
		if($creditor['reminder_system_edition'] == 1){
	        $s_sql = "UPDATE collecting_cases SET
	        updated = now(),
	        updatedBy= ?,
	        choose_move_to_collecting_process = ?,
	        choose_progress_of_reminderprocess = ?
	        WHERE id = ?";
	        $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $case_id));
		} else {
			$s_sql = "UPDATE collecting_cases SET
		    updated = now(),
		    updatedBy= ?,
		    choose_move_to_collecting_process = ?,
		    choose_progress_of_reminderprocess = ?
		    WHERE id = ?";
		    $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $choose_progress_of_reminderprocess,  $case_id));
		}

		//trigger reordering 
		$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
		WHERE collectingcase_id = '".$o_main->db->escape_str($collecting_cases['id'])."' AND creditor_id = '".$o_main->db->escape_str($collecting_cases['creditor_id'])."'";
		$o_query = $o_main->db->query($s_sql);
		process_open_cases_for_tabs($creditor['id'], 4);
    }
    $v_return['status'] = 1;
} else if(isset($transaction_id) && $transaction_id > 0)
{
    $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($transaction_id));
    if($o_query && $o_query->num_rows() == 1) {
		$transaction = $o_query ? $o_query->row_array() : array();
		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
		$creditor = $o_query ? $o_query->row_array() : array();
		if($creditor['reminder_system_edition'] == 1){
			$s_sql = "UPDATE creditor_transactions SET
		   updated = now(),
		   updatedBy= ?,
		   choose_move_to_collecting_process = ?,
		   choose_progress_of_reminderprocess = ?,
			to_be_reordered = 1
		   WHERE id = ?";
		   $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $transaction_id));
		} else {
	        $s_sql = "UPDATE creditor_transactions SET
	        updated = now(),
	        updatedBy= ?,
	        reminder_profile_id = ?,
	        choose_move_to_collecting_process = ?,
	        choose_progress_of_reminderprocess = ?,
			to_be_reordered = 1
	        WHERE id = ?";
	        $o_main->db->query($s_sql, array($username, $choose_reminder_profile, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $transaction_id));
		}
		//trigger reordering 
		$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
		WHERE id = '".$o_main->db->escape_str($transaction['id'])."' AND creditor_id = '".$o_main->db->escape_str($collecting_cases['creditor_id'])."'";
		$o_query = $o_main->db->query($s_sql);
		process_open_cases_for_tabs($creditor['id'], 4);
    }
    $v_return['status'] = 1;
} else if(isset($customer_id) && $customer_id > 0)
{
    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($customer_id));
    if($o_query && $o_query->num_rows() == 1) {
		$customer = $o_query ? $o_query->row_array() : array();
		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($customer['creditor_id']));
		$creditor = $o_query ? $o_query->row_array() : array();
		if($creditor['reminder_system_edition'] == 1){
			$s_sql = "UPDATE customer SET
		   updated = now(),
		   updatedBy= ?,
		   choose_move_to_collecting_process = ?,
		   choose_progress_of_reminderprocess = ?,
		   never_send_by_ehf = ?,
		   prefer_email_before_print_or_ehf = ?
		   WHERE id = ?";
		   $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $never_send_by_ehf, $prefer_email_before_print_or_ehf, $customer_id));
		} else {
	        $s_sql = "UPDATE customer SET
	        updated = now(),
	        updatedBy= ?,
	        creditor_reminder_profile_id = ?,
	        choose_move_to_collecting_process = ?,
	        choose_progress_of_reminderprocess = ?,
			never_send_by_ehf = ?,
			prefer_email_before_print_or_ehf = ?
	        WHERE id = ?";
	        $o_main->db->query($s_sql, array($username, $choose_reminder_profile, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $never_send_by_ehf, $prefer_email_before_print_or_ehf, $customer_id));
		}
		$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
		WHERE external_customer_id = '".$o_main->db->escape_str($customer['creditor_customer_id'])."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
		$o_query = $o_main->db->query($s_sql);
		//trigger reordering 
		process_open_cases_for_tabs($creditor['id'], 5);
    }
    $v_return['status'] = 1;
} else {
    $v_return['error'] = $formText_MissingCase_output;
}


?>
