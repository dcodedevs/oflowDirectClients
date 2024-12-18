<?php
$username = $v_data['params']['username'];
$creditor_id = $v_data['params']['creditor_id'];
$sublist_filter = $v_data['params']['sublist_filter'];
$search_filter = $v_data['params']['search_filter'];
$page = $v_data['params']['page'];
require_once __DIR__ . '/../languagesOutput/no.php';
require_once __DIR__ . '/../../output/includes/creditor_functions_v2.php';

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	$per_page = 100;
	
	// if($search_filter != ""){
	// 	$s_search_sql = " AND (c.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%' 
	// 	OR c.middlename LIKE '%".$o_main->db->escape_like_str($search_filter)."%' 
	// 	OR c.lastname LIKE '%".$o_main->db->escape_like_str($search_filter)."%' 
	// 	OR ct.invoice_nr = '".$o_main->db->escape_str($search_filter)."')";
	// }
	
	$filters['search_filter'] = $search_filter;
	
	$filters['list_filter'] = "transactions_with_fees_only";	
	$filters['sublist_filter'] = "restclaim_older";	
	$suggested_old_count = get_transaction_count2($o_main, $creditor['id'], "reminderLevel", $filters);
	
	$filters['list_filter'] = "transactions_with_fees_only";	
	$filters['sublist_filter'] = "resclaim_younger";	
	$suggested_young_count = get_transaction_count2($o_main, $creditor['id'], "reminderLevel", $filters);
	
	$filters['list_filter'] = "transactions_with_fees_only";	
	$filters['sublist_filter'] = "resclaim_need_date_fix";	
	$transactions_needs_date_fix = get_transaction_count2($o_main, $creditor['id'], "reminderLevel", $filters);
	if($username=="david@dcode.no"){
		$filters['list_filter'] = "transactions_with_fees_only";	
		$filters['sublist_filter'] = "sent_by_mistake";	
		$suggested_mistake_count = get_transaction_count2($o_main, $creditor['id'], "reminderLevel", $filters);
	}
	// $filters['list_filter'] = "transactions_with_fees_only";	
	// $filters['sublist_filter'] = $sublist_filter;	
	// $suggested_count = get_transaction_count2($o_main, $creditor['id'], "reminderLevel", $filters);
	
	if($sublist_filter == "restclaim_older") {
		$suggested_count = $suggested_old_count;
	}
	if($sublist_filter == "resclaim_younger") {
		$suggested_count = $suggested_young_count;
	}
	if($sublist_filter == "resclaim_need_date_fix") {
		$suggested_count = $suggested_young_count;
	}
	$filters['sublist_filter'] = $sublist_filter;	
	$customerListNonProcessed = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, $page, $per_page);
	foreach($customerListNonProcessed as &$v_row) {
		$s_sql = "SELECT cccl.*, c.invoiceEmail FROM collecting_cases_claim_letter cccl
		JOIN collecting_cases cc ON cc.id = cccl.case_id
		JOIN customer c ON c.id = cc.debitor_id
		WHERE cccl.content_status < 2 AND cccl.case_id = ?  ORDER BY cccl.created DESC";
		$o_query = $o_main->db->query($s_sql, array($v_row['collectingcase_id']));
		$v_claim_letters = ($o_query ? $o_query->result_array() : array());

		$v_row['letters'] = $v_claim_letters;

		$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($v_row['collectingcase_id']));
		$objections = ($o_query ? $o_query->result_array() : array());
		$v_row['objections'] = $objections;
		
		$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND open = 1 AND collectingcase_id > 0 AND external_customer_id = ?
		 ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($v_row['creditorCreditorId'], $v_row['external_customer_id']));
		$casesOnReminderCount = ($o_query ? $o_query->num_rows() : 0);

		$s_sql = "SELECT collecting_company_cases.*, c.creditor_customer_id FROM collecting_company_cases
		JOIN customer c ON c.id = collecting_company_cases.debitor_id
		WHERE collecting_company_cases.creditor_id = ? AND collecting_company_cases.case_closed_date = '0000-00-00 00:00:00' AND c.creditor_customer_id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_row['creditorCreditorId'], $v_row['external_customer_id']));
		$casesOnCollectingCount = ($o_query ? $o_query->num_rows() : 0);

		$v_row['casesOnReminderCount'] = $casesOnReminderCount;
		$v_row['casesOnCollectingCount'] = $casesOnCollectingCount;

		$transaction_fees = array();
		$transaction_payments = array();
		$connected_transactions = array();
		if($v_row['link_id'] > 0){
			$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND creditor_id = ? 
			AND (collectingcase_id is null OR collectingcase_id = 0) 
			AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') AND link_id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id'], $v_row['link_id']));
			$transaction_fees = ($o_query ? $o_query->result_array() : array());
			
			$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') 
			AND creditor_id = ? AND link_id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id'], $v_row['link_id']));
			$transaction_payments = ($o_query ? $o_query->result_array() : array());

			if($creditor['checkbox_1']){
				$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND ct.open = 1 AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['id']));
				$connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
				foreach($connected_transactions_raw as $connected_transaction_raw){
					if(strpos($connected_transaction_raw['comment'], '_') === false){
						$connected_transactions[] = $connected_transaction_raw;
					}
				}
			}
		}
		$v_row['transaction_fees'] = $transaction_fees;
		$v_row['transaction_payments'] = $transaction_payments;
		$v_row['connected_transactions'] = $connected_transactions;

	}
	$v_return['creditor'] = $creditor;
	$v_return['transactions'] = $customerListNonProcessed;
	$v_return['transactions_all_count'] = $suggested_count;
	$v_return['transactions_old_count'] = $suggested_old_count;
	$v_return['transactions_young_count'] = $suggested_young_count;
	$v_return['transactions_mistake_count'] = $suggested_mistake_count;
	$v_return['total_pages'] = ceil($suggested_count/$per_page);
	$v_return['transactions_needs_date_fix'] = $transactions_needs_date_fix;
	
	$v_return['status'] = 1;
}
?>
