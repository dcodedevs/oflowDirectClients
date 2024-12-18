<?php
if(!function_exists("create_case_from_transaction")){
    function create_case_from_transaction($transactionId, $creditorId, $languageID, $launch_process = false) {
        global $o_main;
        global $extradomaindirroot;
        global $accountname;
        global $username;

        include_once(__DIR__."/../../output/languagesOutput/default.php");
        include_once(__DIR__."/../../output/languagesOutput/no.php");
        $result = false;
    	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditorId));
        $creditor = ($o_query ? $o_query->row_array() : array());

        $s_sql = "SELECT creditor_transactions.* FROM creditor_transactions WHERE creditor_transactions.id = ?";
        $o_query = $o_main->db->query($s_sql, array($transactionId));
        $ready_transaction = ($o_query ? $o_query->row_array() : array());
		if($ready_transaction['open'] == 1){
			if($ready_transaction['invoice_nr'] != '') {
				$connectedTransactionsHasCase = false;
				$connected_transactions = array();
				$all_connected_transaction_ids = array($ready_transaction['id']);
				if($ready_transaction['link_id'] > 0 && ($creditor['checkbox_1'])) {
					$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
					$o_query = $o_main->db->query($s_sql, array($ready_transaction['link_id'], $ready_transaction['id']));
					$connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
					foreach($connected_transactions_raw as $connected_transaction_raw){
						if(strpos($connected_transaction_raw['comment'], '_') === false){
							$connected_transactions[] = $connected_transaction_raw;
						}
					}
					foreach($connected_transactions as $connected_transaction){
						$all_connected_transaction_ids[] = $connected_transaction['id'];
						if($connected_transaction['collectingcase_id'] > 0) {
							$connectedTransactionsHasCase = true;
							$s_sql = "UPDATE creditor_transactions SET collectingcase_id = 0 WHERE creditor_transactions.id = ?";
							$o_query = $o_main->db->query($s_sql, array($connected_transaction['id']));
							if($o_query){
								$s_sql = "UPDATE collecting_cases SET updated = NOW(), updatedBy = 'script', status = 2, sub_status = 15 WHERE collecting_cases.id = ?";
								$o_query = $o_main->db->query($s_sql, array($connected_transaction['collectingcase_id']));
								if($o_query) {
									$connectedTransactionsHasCase = false;
								}
							}
						}
					}
				}
				if(!$connectedTransactionsHasCase){
					$s_sql = "SELECT * FROM creditor_transactions  WHERE system_type='Payment' AND link_id = ? AND creditor_id = ?";
					$o_query = $o_main->db->query($s_sql, array($ready_transaction['link_id'], $ready_transaction['creditor_id']));
					$invoice_payments = ($o_query ? $o_query->result_array() : array());
					$totalSumDue = $ready_transaction['amount'];
					foreach($connected_transactions as $connected_transaction){
						$totalSumDue+=$connected_transaction['amount'];
					}
					foreach($invoice_payments as $invoice_payment) {
						$totalSumDue+= $invoice_payment['amount'];
					}
					if($totalSumDue > 0){
						$sql = "SELECT customer.* FROM customer
						WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
						$o_query = $o_main->db->query($sql, array($ready_transaction['external_customer_id'], $creditor['id']));
						$debitorData = $o_query ? $o_query->row_array() : array();

						if(($creditor['creditor_reminder_default_profile_id'] > 0 && $creditor['creditor_reminder_default_profile_for_company_id'] > 0) || $creditor['reminder_system_edition'] == 1){

							$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
							$person_profile = ($o_query ? $o_query->row_array() : array());

							$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
							$company_profile = ($o_query ? $o_query->row_array() : array());

							$profile = $company_profile;
							$customer_type_collect_debitor = $debitorData['customer_type_collect'];
							if($debitorData['customer_type_collect_addition'] > 0){
								$customer_type_collect_debitor = $debitorData['customer_type_collect_addition'] - 1;
							}
							// if($customer_type_collect_debitor == 0){
							// 	if($debitorData['organization_type'] == "ENK") {
							// 		$customer_type_collect_debitor = 1;
							// 	}
							// }
							if($customer_type_collect_debitor) {
								$profile = $person_profile;
							}
							if($debitorData['creditor_reminder_profile_id'] > 0) {
								$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.id = ?";
								$o_query = $o_main->db->query($s_sql, array($debitorData['creditor_reminder_profile_id']));
								$profile = ($o_query ? $o_query->row_array() : array());
							}
							if($ready_transaction['reminder_profile_id'] > 0){
								$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.id = ?";
								$o_query = $o_main->db->query($s_sql, array($ready_transaction['reminder_profile_id']));
								$profile = ($o_query ? $o_query->row_array() : array());
							}

							if($creditor['reminder_system_edition'] == 1){
								$s_sql = "SELECT collecting_system_settings.* FROM collecting_system_settings WHERE content_status < 2";
								$o_query = $o_main->db->query($s_sql);
								$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

								$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.light_edition = 1 AND creditor_reminder_custom_profiles.reminder_process_id = ?";
								$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_person']));
								$person_profile = ($o_query ? $o_query->row_array() : array());

								$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.light_edition = 1 AND creditor_reminder_custom_profiles.reminder_process_id = ?";
								$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_company']));
								$company_profile = ($o_query ? $o_query->row_array() : array());

								if(!$person_profile) {
									$s_sql = "SELECT collecting_cases_process.* FROM collecting_cases_process WHERE collecting_cases_process.id = ? ";
									$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_person']));
									$process = ($o_query ? $o_query->row_array() : array());

									$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
									created = NOW(),
									createdBy = '".$o_main->db->escape_str($username)."',
									name = '".$o_main->db->escape_str($process['name'])."',
									reminder_process_id = '".$o_main->db->escape_str($process['id'])."'";
									$o_query = $o_main->db->query($s_sql);

									$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE AND creditor_reminder_custom_profiles.light_edition = 1 AND creditor_reminder_custom_profiles.reminder_process_id = ?";
									$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_person']));
									$person_profile = ($o_query ? $o_query->row_array() : array());
								}
								if(!$company_profile) {
									$s_sql = "SELECT collecting_cases_process.* FROM collecting_cases_process WHERE collecting_cases_process.id = ? ";
									$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_company']));
									$process = ($o_query ? $o_query->row_array() : array());

									$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
									created = NOW(),
									createdBy = '".$o_main->db->escape_str($username)."',
									name = '".$o_main->db->escape_str($process['name'])."',
									reminder_process_id = '".$o_main->db->escape_str($process['id'])."'";
									$o_query = $o_main->db->query($s_sql);

									$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.light_edition = 1 AND creditor_reminder_custom_profiles.reminder_process_id = ?";
									$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_company']));
									$company_profile = ($o_query ? $o_query->row_array() : array());
								}

								$profile = $company_profile;
								$customer_type_collect_debitor = $debitorData['customer_type_collect'];
								if($debitorData['customer_type_collect_addition'] > 0){
									$customer_type_collect_debitor = $debitorData['customer_type_collect_addition'] - 1;
								}
								// if($customer_type_collect_debitor == 0){
								// 	if($debitorData['organization_type'] == "ENK") {
								// 		$customer_type_collect_debitor = 1;
								// 	}
								// }
								if($customer_type_collect_debitor){
								$profile = $person_profile;
								}
							}
							$choose_move_to_collecting_process = 0;
							$choose_progress_of_reminderprocess = 0;

							if($debitorData['choose_move_to_collecting_process'] > 0){
								$choose_move_to_collecting_process = $debitorData['choose_move_to_collecting_process'];
							}
							if($debitorData['choose_progress_of_reminderprocess'] > 0){
								$choose_progress_of_reminderprocess = $debitorData['choose_progress_of_reminderprocess'];
							}

							if($ready_transaction['choose_move_to_collecting_process'] > 0){
								$choose_move_to_collecting_process = $ready_transaction['choose_move_to_collecting_process'];
							}
							if($ready_transaction['choose_progress_of_reminderprocess'] > 0){
								$choose_progress_of_reminderprocess = $ready_transaction['choose_progress_of_reminderprocess'];
							}
							if($profile){
								if($ready_transaction && $creditor) {
									$sql = "INSERT INTO collecting_cases SET creditor_id = ?, debitor_id = ?, status = ?, collecting_cases_process_step_id = ?, reminder_profile_id = ?, createdBy = '".$o_main->db->escape_str($username)."', created=NOW(), due_date = ?, choose_move_to_collecting_process = ?, choose_progress_of_reminderprocess = ?";
									$o_query = $o_main->db->query($sql, array($ready_transaction['creditor_id'], $debitorData['id'],  0, 0, $profile['id'], $ready_transaction['due_date'], $choose_move_to_collecting_process, $choose_progress_of_reminderprocess));
									if($o_query) {
										$collecting_case_id = $o_main->db->insert_id();
										$result = $collecting_case_id;


										$claimAmount = $ready_transaction['amount'];

										// $s_sql = "SELECT * FROM creditor_transactions  WHERE  open = 1 AND system_type='Payment' AND link_id = ? AND creditor_id = ?";
										// $o_query = $o_main->db->query($s_sql, array($ready_transaction['link_id'], $ready_transaction['creditor_id']));
										// $paymentsBefore = ($o_query ? $o_query->result_array() : array());
										//
										// foreach($paymentsBefore as $paymentBefore) {
										//     $claimAmount += $paymentBefore['amount'];
										// }

										$sql = "UPDATE creditor_transactions SET collectingcase_id = ?, collecting_case_original_claim = ?, to_be_reordered = 1 WHERE id = ?";
										$o_query = $o_main->db->query($sql, array($collecting_case_id, $claimAmount, $ready_transaction['id']));

										if($launch_process){
											$reminderLevelOnly = 1;
											$manualProcessing = 1;
											$creditorId = $creditor['id'];
											include(__DIR__."/process_scripts/handle_cases.php");
										}
									}
								}
							}
						}
					}
				}
			}
		}
        return $result;
    }
}
?>
