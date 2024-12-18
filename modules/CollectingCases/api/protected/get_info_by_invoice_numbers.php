<?php
$invoice_nrs = $v_data['params']['invoice_nrs'];
$client_id = $v_data['params']['client_id'];

include(__DIR__."/functions/func_get_available_commands.php");
include(__DIR__."/../languagesOutput/default.php");
if($v_data['params']['languageID'] != "" && $v_data['params']['languageID'] != "en"){
    include(__DIR__."/../languagesOutput/".$v_data['params']['languageID'].".php");
} else {
    include(__DIR__."/../languagesOutput/no.php");
}
if(count($invoice_nrs) > 0) {
	if($client_id != "") {
		$s_sql = "select * from creditor where 24sevenoffice_client_id = '".$o_main->db->escape_str($client_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$creditor = $o_query ? $o_query->row_array() : array();
		if($creditor) {
			$invoices = array();
			foreach($invoice_nrs as $invoice_nr) {
				$s_sql = "select * from creditor_transactions where invoice_nr = '".$o_main->db->escape_str($invoice_nr)."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND collectingcase_id > 0";
				$o_query = $o_main->db->query($s_sql);
				$creditor_transactions = $o_query ? $o_query->result_array() : array();
				foreach($creditor_transactions as $creditor_transaction){
					$lastSentLetter = array();
					$s_sql = "select * from collecting_cases where id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor_transaction['collectingcase_id']));
					$case = $o_query ? $o_query->row_array() : array();
					$status_text = "";
					if($creditor_transaction['collecting_company_case_id'] > 0){
						$s_sql = "select * from collecting_company_cases where id = ?";
						$o_query = $o_main->db->query($s_sql, array($creditor_transaction['collecting_company_case_id']));
						$collecting_company_case = $o_query ? $o_query->row_array() : array();
						if($collecting_company_case){
							$status_text = $formText_CollectingCaseStartedDate_output." ";
							if($collecting_company_case['collecting_case_created_date'] != "0000-00-00" && $collecting_company_case['collecting_case_created_date'] != "") {
								$status_text .= date("d.m.Y", strtotime($collecting_company_case['collecting_case_created_date']));
							} else if($collecting_company_case['warning_case_created_date'] != "0000-00-00" && $collecting_company_case['warning_case_created_date'] != "") {
								$status_text .= date("d.m.Y", strtotime($collecting_company_case['warning_case_created_date']));
							} else {
								$status_text = $formText_CollectingCompanyCaseNotStarted_output;
							}
							if($collecting_company_case['case_closed_date'] != "" && $collecting_company_case['case_closed_date'] != "0000-00-00") {
								$status_text .= PHP_EOL.$formText_CollectingCaseClosed_output;								
								$status_text .= " ".date("d.m.Y", strtotime($collecting_company_case['case_closed_date']));
							}
						}
					} else {
						if($creditor_transaction['open']){						
							$s_sql = "select * from collecting_cases_claim_letter where case_id = '".$o_main->db->escape_str($case['id'])."' ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql);
							$lastSentLetter = $o_query ? $o_query->row_array() : array();
							if($lastSentLetter) {
								$status_text = $formText_LastSentReminder_output." ".date("d.m.Y", strtotime($lastSentLetter['created']));
								
							} else {
								$status_text = $formText_ReminderNotSent_output;
							}
						} else {
							$status_text = $formText_CaseClosed_output;
							if($case['stopped_date'] != "" && $case['stopped_date'] != "0000-00-00" && $case['stopped_date'] != "0000-00-00 00:00:00") {
								$status_text .= " ".date("d.m.Y", strtotime($case['stopped_date']));
							}
						}
					}

					$v_item = array("invoice_nr"=>$invoice_nr, "status"=>$status_text);
					if(isset($lastSentLetter['id']) && 0 < $lastSentLetter['id'] && !empty($lastSentLetter['pdf']))
					{
						$v_item['letter_id'] = $lastSentLetter['id'];
					}

					$available_commands = get_available_commands($creditor_transaction);
					$v_item['available_commands'] = $available_commands;
					$invoices[] = $v_item;
				}
			}
			$v_return['status'] = 1;
			$v_return['invoices'] = $invoices;
		} else {
		    $v_return['message'] = "Client not registered";
		}
	} else {
	    $v_return['message'] = "Missing creditor id";
	}
} else {
    $v_return['message'] = "Missing invoice nr";
}
?>
