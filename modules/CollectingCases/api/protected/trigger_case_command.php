<?php
$client_id = $v_data['params']['client_id'];
$command_id = $v_data['params']['command_id'];
$invoice_nr = $v_data['params']['invoice_nr'];

include(__DIR__."/functions/func_get_available_commands.php");
if($command_id > 0) {
	if($client_id != "") {
		$s_sql = "select * from creditor where 24sevenoffice_client_id = '".$o_main->db->escape_str($client_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$creditor = $o_query ? $o_query->row_array() : array();
		if($creditor) {
			$s_sql = "select * from creditor_transactions where invoice_nr = '".$o_main->db->escape_str($invoice_nr)."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND collectingcase_id > 0";
			$o_query = $o_main->db->query($s_sql);
			$creditor_transaction = $o_query ? $o_query->row_array() : array();
			if($creditor_transaction) {
				$available_commands = get_available_commands($creditor_transaction);
				if(in_array($command_id, $available_commands)) {
					$s_sql = "select * from collecting_cases where id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor_transaction['collectingcase_id']));
					$case = $o_query ? $o_query->row_array() : array();
					if($case){
						if($command_id == 1) {
							$s_sql = "UPDATE collecting_cases SET choose_progress_of_reminderprocess = 3 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($case['id']));
						} else if($command_id == 2){
							$s_sql = "UPDATE collecting_cases SET choose_progress_of_reminderprocess = 0 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($case['id']));
						}
						if($o_query){
							$v_return['status'] = 1;
						} else {
							$v_return['message'] = "Failed to update";
						}
					} else {
						if($command_id == 1) {
							$s_sql = "UPDATE creditor_transactions SET choose_progress_of_reminderprocess = 3 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditor_transaction['id']));
						} else if($command_id == 2){
							$s_sql = "UPDATE creditor_transactions SET choose_progress_of_reminderprocess = 0 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditor_transaction['id']));
						}
						if($o_query){
							$v_return['status'] = 1;
						} else {
							$v_return['message'] = "Failed to update";
						}
					}
					if($v_return['status'] == 1){
						$available_commands = get_available_commands($creditor_transaction);
						$v_return['available_commands'] = $available_commands;
					}
				} else {
				    $v_return['message'] = "Command not available for this case";
				}
			} else {
			    $v_return['message'] = "Transaction not found";
			}
		} else {
		    $v_return['message'] = "Client not registered";	
		}
	} else {
	    $v_return['message'] = "Missing client";
	}
} else {
    $v_return['message'] = "Missing command";
}
?>
