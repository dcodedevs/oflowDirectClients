<?php
use Mika56\SPFCheck\SPFCheck;
use Mika56\SPFCheck\DNSRecordGetter;
//if($username == "david@dcode.no") define('SYS_LOG_QUERIES', TRUE);

$get_collecting_company_info = $v_data['params']['get_collecting_company_info'];
$filters_new = $v_data['params']['filters'];
// Process filter data
$list_filter = $filters_new['list_filter'] ? $filters_new['list_filter'] : 'active';
$customer_filter = $filters_new['customer_filter'] ? $filters_new['customer_filter'] : 0;
$creditor_id = $filters_new['creditor_id'] ? $filters_new['creditor_id'] : 0;
$search_filter = $filters_new['search_filter'] ? $filters_new['search_filter'] : '';
$page = $filters_new['page'] ? $filters_new['page'] : 1;
$perPage = $filters_new['perPage'] ? $filters_new['perPage'] : 500;
$mainlist_filter = $filters_new['mainlist_filter'] ? $filters_new['mainlist_filter'] : 'reminderLevel';
$order_field = $filters_new['order_field'] ? $filters_new['order_field'] : '';
$order_direction = $filters_new['order_direction'] ? $filters_new['order_direction'] : '0';
$sublist_filter = $filters_new['sublist_filter'] ? $filters_new['sublist_filter'] : '';
$username = $v_data['params']['email'];

// Return data array
$return_data = array(
    'list' => array()
);
$countArray = array();

$filters = array();
$filters['order_direction'] = $order_direction;
$filters['order_field'] = $order_field;
$filters['search_filter'] = $search_filter;

if($creditor_id > 0) {
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_id));
	$creditor = ($o_query ? $o_query->row_array() : array());
} else {
	$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_filter));
	$creditor = ($o_query ? $o_query->row_array() : array());
}
if($creditor['id'] == "1041" && 1 != 1){
	require_once __DIR__ . '/../../output/includes/creditor_functions.dev.php';
} else {
	require_once __DIR__ . '/../../output/includes/creditor_functions.php';
}
if($username == "david@dcode.no"){
	$v_return['log'] .= "start-".time()."<br/>";
}
$processes = array();
$cid = $creditor['id'];
if($creditor) {

	$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE content_status < 2 ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$collectingProcesses = ($o_query ? $o_query->result_array() : array());

	if($get_collecting_company_info){
		if($username == "david@dcode.no"){
			$v_return['log'] .= "tab number start-".time()."<br/>";
		}

		// $casesOnCollectingLevelCount = get_collecting_company_case_count2($o_main, $cid, "all", $filters);
		// $warning_case_count = get_collecting_company_case_count2($o_main, $cid,"warning", $filters);
		// $collecting_case_count = get_collecting_company_case_count2($o_main, $cid,"collecting", $filters);
		// $warning_closed_case_count = get_collecting_company_case_count2($o_main,$cid, "warning_closed", $filters);
		// $collecting_closed_case_count = get_collecting_company_case_count2($o_main, $cid,"collecting_closed", $filters);
		
	    // $countArray['casesOnCollectingLevelCount'] = $casesOnCollectingLevelCount;
	    // $countArray['warning_case_count'] = $warning_case_count;
	    // $countArray['collecting_case_count'] = $collecting_case_count;
	    // $countArray['warning_closed_case_count'] = $warning_closed_case_count;
	    // $countArray['collecting_closed_case_count'] = $collecting_closed_case_count;



		$collecting_not_started_count = get_collecting_company_case_count2($o_main, $cid,"all_not_started", $filters);
		$collecting_active_count = get_collecting_company_case_count2($o_main, $cid,"all_active", $filters);
		$collecting_closed_count = get_collecting_company_case_count2($o_main, $cid,"all_closed", $filters);
	    $countArray['collecting_not_started_count'] = $collecting_not_started_count;
	    $countArray['collecting_active_count'] = $collecting_active_count;
	    $countArray['collecting_closed_count'] = $collecting_closed_count;
		if($username == "david@dcode.no"){
			$v_return['log'] .= "tab number end-".time()."<br/>";
		}
		if($list_filter == 'collecting' || $list_filter == 'warning') {
			$countFilters = $filters;
			$countFilters['sublist_filter'] = "canSendNow";
			$canSendNowCount = get_collecting_company_case_count2($o_main, $cid,$list_filter, $countFilters);

			$countFilters['sublist_filter'] = "notStarted";
			$notStartedCount = get_collecting_company_case_count2($o_main, $cid,$list_filter, $countFilters);

			$countFilters['sublist_filter'] = "dueDateNotExpired";
			$dueDateNotExpiredCount = get_collecting_company_case_count2($o_main,$cid, $list_filter, $countFilters);

			$countFilters['sublist_filter'] = "stoppedWithObjection";
			$stoppedWithObjectionCount = get_collecting_company_case_count2($o_main, $cid,$list_filter, $countFilters);

		    $countArray['canSendNowCountCollecting'] = $canSendNowCount;
		    $countArray['notStartedCountCollecting'] = $notStartedCount;
		    $countArray['dueDateNotExpiredCountCollecting'] = $dueDateNotExpiredCount;
		    $countArray['stoppedWithObjectionCountCollecting'] = $stoppedWithObjectionCount;
		}

	} else {
		
		if($username == "david@dcode.no"){
			$v_return['log'] .= "tab number start-".time()."<br/>";
		}
		if($cid != "1031"){
			// $list_filter_fil = "reminderLevel";
			// $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			// $countArray['invoicesOnReminderLevelCount'] = $suggested_count;

			// $filters['list_filter'] = "canSendReminderNow";
			// $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			// $countArray['canSendReminderNowCount'] = $suggested_count;

			// $filters['list_filter'] = "dueDateNotExpired";
			// $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			// $countArray['dueDateNotExpiredCount'] = $suggested_count;

			// $filters['list_filter'] = "doNotSend";
			// $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			// $countArray['doNotSendCount'] = $suggested_count;


			// $filters['list_filter'] = "stoppedWithObjection";
			// $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			// $countArray['stoppedWithObjectionCount'] = $suggested_count;

			
		}
		if($list_filter == "canSendReminderNow" || $list_filter == "notPayedConsiderCollectingProcess"){
			$list_filter_fil = "reminderLevel";
			$filters['list_filter'] = $list_filter;
			$filters['sublist_filter'] = 'manual_move';
			$suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			$countArray['manualCount'] = $suggested_count;

			$filters['list_filter'] = $list_filter;
			$filters['sublist_filter'] = 'automatic_move';
			$suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			$countArray['automaticCount'] = $suggested_count;

			$filters['list_filter'] = $list_filter;
			$filters['sublist_filter'] = 'missing_address';
			$suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
			$countArray['missingAddressCount'] = $suggested_count;
		}
		if($username == "david@dcode.no"){
			$v_return['log'] .= "tab number end-".time()."<br/>";
		}
	}

    $groupedTransactions = array();
    if($mainlist_filter == "reminderLevel" || $mainlist_filter == "collectingLevel") {
        $filters['list_filter'] = $list_filter;
        $filters['sublist_filter'] = $sublist_filter;
		if($mainlist_filter == "collectingLevel") {
	        $itemCount = get_collecting_company_case_count2($o_main, $cid, $list_filter, $filters);
		} else {
			if($username == "david@dcode.no"){
				$v_return['log'] .= "current tab number start-".time()."<br/>";
			}
	        $itemCount = get_transaction_count2($o_main, $cid, $mainlist_filter, $filters);
			/*if($username == "david@dcode.no"){ $itemCount = 1234;
			} else {
				$itemCount = get_transaction_count2($o_main, $cid, $mainlist_filter, $filters);
			}*/
			
			if($username == "david@dcode.no"){
				$v_return['log'] .= "current tab number end-".time()."<br/>";
			}
		}

        $rowOnly = $_POST['rowOnly'];
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $itemCount;
		$countArray['currentCount'] = $currentCount;
        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);
        $customerList = array();
		if($mainlist_filter == "collectingLevel"){
			$customerListNonProcessed = get_collecting_company_case_list($o_main, $cid, $list_filter, $filters, $page, $perPage);
			
		if($username == "david@dcode.no"){
			$v_return['log'] .= "tab number end-".time()."<br/>";
			$v_return['log'] .= count($customerListNonProcessed)."<br/>";
		}
			foreach($customerListNonProcessed as $v_row) {
                $s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_steps.collecting_cases_collecting_process_id = ? ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql, array($v_row['collecting_process_id']));
                $old_steps = ($o_query ? $o_query->result_array() : array());

	            $steps = array();
	            foreach($old_steps as $step) {
	                array_push($steps, $step);
	            }
	            $next_step = array();
	            $stepTrigger = false;
	            $currentStep = array();
	            foreach($steps as $step) {
	                if(!$next_step){
	                    $next_step = $step;
	                }
	                if($stepTrigger){
	                    $next_step = $step;
	                    $stepTrigger = false;
	                }
	                if($step['id'] == $v_row['collecting_cases_process_step_id']) {
	                    $currentStep = $step;
	                    $stepTrigger = true;
	                }
	            }


	            $v_row['steps'] = $steps;
	            $v_row['next_step'] = $next_step;

				$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
				WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ?  ORDER BY cccl.created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$v_claim_letters = ($o_query ? $o_query->result_array() : array());

	            $v_row['letters'] = $v_claim_letters;

	            $s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_company_case_id = ? ORDER BY created DESC";
	            $o_query = $o_main->db->query($s_sql, array($v_row['id']));
	            $objections = ($o_query ? $o_query->result_array() : array());
				$v_row['objections'] = $objections;

				$s_sql = "SELECT * FROM customer WHERE id = ? AND creditor_id = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['debitor_id'], $v_row['creditor_id']));
				$debitorCustomer = $o_query ? $o_query->row_array() : array();
				$v_row['debitorCustomer'] = $debitorCustomer;

				$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
				JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
				WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
				ORDER BY cccl.claim_type ASC, cccl.created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$claims = ($o_query ? $o_query->result_array() : array());
				$v_row['claims'] = $claims;
				$totalSumOriginalClaim = 0;
				foreach($claims as $claim){
					if($claim['claim_type'] == 1){
						$totalSumOriginalClaim+= $claim['amount'];
					}
				}
				$v_row['totalSumOriginalClaim'] = $totalSumOriginalClaim;
				$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$payments = ($o_query ? $o_query->result_array() : array());
				$v_row['payments'] = $payments;


				$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
				JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
				WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
				ORDER BY cccl.claim_type ASC, cccl.created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$claims = ($o_query ? $o_query->result_array() : array());
				$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created ASC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$payments = ($o_query ? $o_query->result_array() : array());
				$totalSumPaid = 0;
				foreach($payments as $payment) {
					$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
					$o_query = $o_main->db->query($s_sql);
					$transactions = ($o_query ? $o_query->result_array() : array());
					foreach($transactions as $transaction) {
						$totalSumPaid += $transaction['amount'];
					}
				}
				$totalSumDue = 0;
				foreach($claims as $claim) {
					if(!$claim['payment_after_closed']) {
						$totalSumDue += $claim['amount'];
					}
				}

				$v_row['balance'] = $totalSumDue - $totalSumPaid;
				array_push($customerList, $v_row);
			}
		} else {
			
			if($username == "david@dcode.no"){
				$v_return['log'] .= "current tab list start-".time()."<br/>";
			}
	        $customerListNonProcessed = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);
			
			if($username == "david@dcode.no"){
				$v_return['log'] .= "current tab list end-".time()."<br/>";
			}
			$collectingcase_ids = array();
			$transaction_ids = array();
			$all_link_ids = array();
			foreach($customerListNonProcessed as $v_row) {
				if($v_row['id'] > 0){
					$collectingcase_ids[] = $v_row['id'];
				}
				$transaction_ids[] = $v_row['internalTransactionId'];

				if($v_row['link_id'] > 0 && $v_row['system_type'] == 'InvoiceCustomer' && $v_row['open']){
					if(!in_array($v_row['link_id'], $all_link_ids)){
						$all_link_ids[$v_row['link_id']] = $v_row['link_id'];
					}
				}
			}
			if($username == "david@dcode.no"){
				$v_return['log'] .= "link ids-".count($all_link_ids)." ".time()."<br/>";
			}
			$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id IN (".implode(',', $collectingcase_ids).") ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql);
			$all_invoices = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
			WHERE cccl.content_status < 2 AND cccl.case_id IN (".implode(',', $collectingcase_ids).")  ORDER BY cccl.created DESC";
			$o_query = $o_main->db->query($s_sql);
			$all_claim_letters = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id IN (".implode(',', $collectingcase_ids).") ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql);
			$all_objections = ($o_query ? $o_query->result_array() : array());


			$s_sql = "SELECT * FROM collecting_cases_comments WHERE transaction_id IN (".implode(',', $transaction_ids).") ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql);
			$all_comments = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
			$o_query = $o_main->db->query($s_sql);
			$all_processes_un = ($o_query ? $o_query->result_array() : array());
			$all_processes = array();
			foreach($all_processes_un as $all_process){
				$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? ORDER BY sortnr ASC";
				$o_query = $o_main->db->query($s_sql, array($all_process['id']));
				$old_steps = ($o_query ? $o_query->result_array() : array());
				$all_process['steps'] = $old_steps;
				$all_processes[] = $all_process;
			}

			$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND system_type='InvoiceCustomer' AND open = 1";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_transactions = $o_query ? $o_query->result_array() : array();
			$all_transactions_with_keyid = array();
			foreach($all_transactions as $all_transaction) {
				if($all_transaction['link_id'] > 0){
					$all_link_ids[$all_transaction['link_id']] = $all_transaction['link_id'];
				}
				$all_transactions_with_keyid[$all_transaction['id']] = $all_transaction;
			}
			$all_transaction_payments = array();
			if($username == "david@dcode.no"){
				$v_return['log'] .= "link ids-".count($all_link_ids)." ".time()."<br/>";
			}
			if(count($all_link_ids) > 0) {
				$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND creditor_id = ? AND link_id IN (".implode(',', $all_link_ids).")";
				$o_query = $o_main->db->query($s_sql, array($cid));
				$all_transaction_payments = ($o_query ? $o_query->result_array() : array());
			}
			if($username == "david@dcode.no"){
				$v_return['log'] .= "link ids-".count($all_link_ids)." ".time()."<br/>";
			}

			$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') AND link_id IN (".implode(',', $all_link_ids).")";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_transaction_fees = ($o_query ? $o_query->result_array() : array());
			$all_transaction_fees_with_keyid = array();
			foreach($all_transaction_fees as $all_transaction_fee) {
				$all_transaction_fees_with_keyid[$all_transaction_fee['link_id']] = $all_transaction_fee;
			}
			if($username == "david@dcode.no"){
				$v_return['log'] .= "fee-".time()."<br/>";
			}
			//ALI - Optimization - coded where it's needed
			/*$s_sql = "SELECT * FROM customer WHERE creditor_id = ?";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_debitorCustomers = $o_query ? $o_query->result_array() : array();
			$all_debitorCustomers_with_keyid = array();
			foreach($all_debitorCustomers as $all_debitorCustomer) {
				$all_debitorCustomers_with_keyid[$all_debitorCustomer['creditor_customer_id']] = $all_debitorCustomer;
			}*/

			$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name
			FROM creditor_reminder_custom_profiles crcp
			JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
			JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
			WHERE crcp.creditor_id = ? ORDER BY ccp.sortnr ASC";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$creditor_reminder_custom_profiles_un = ($o_query ? $o_query->result_array() : array());

			$creditor_reminder_custom_profiles = array();
			$creditor_reminder_custom_profiles_deleted = array();
			foreach($creditor_reminder_custom_profiles_un as $creditor_reminder_custom_profile) {
				$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['id']));
				$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();
				$creditor_reminder_custom_profile['unprocessed_profile_values'] = $unprocessed_profile_values;
				if($creditor_reminder_custom_profile['content_status'] < 2){
					$creditor_reminder_custom_profiles[] = $creditor_reminder_custom_profile;
				} else {
					$creditor_reminder_custom_profiles_deleted[] = $creditor_reminder_custom_profile;
				}
			}

			$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND open = 1 AND collectingcase_id > 0 ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_casesOnReminder = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT collecting_company_cases.* FROM collecting_company_cases
			JOIN customer c ON c.id = collecting_company_cases.debitor_id
			WHERE collecting_company_cases.creditor_id = ? AND collecting_company_cases.case_closed_date = '0000-00-00 00:00:00'";
			$o_query = $o_main->db->query($s_sql, array($v_row['creditorCreditorId']));
			$all_casesOnCollecting = ($o_query ? $o_query->result_array() : array());

			if($username == "david@dcode.no"){
				$v_return['log'] .= "current tab processing start-".time()."<br/>";
			}
			

	        foreach($customerListNonProcessed as $v_row) {
				$process = array();
				foreach($all_processes as $all_process){
					if($all_process['id'] == $v_row['reminder_process_id']){
						$process = $all_process;
					}
				}
				$steps = $process['steps'];

				$next_step = array();
				$stepTrigger = false;
				$currentStep = array();
				foreach($steps as $step) {
					if(!$next_step){
						$next_step = $step;
					}
					if($stepTrigger){
						$next_step = $step;
						$stepTrigger = false;
					}
					if($step['id'] == $v_row['collecting_cases_process_step_id']) {
						$currentStep = $step;
						$stepTrigger = true;
					}
				}


	            $v_row['steps'] = $steps;
	            $v_row['next_step'] = $next_step;
				$v_claim_letters = array();
				foreach($all_claim_letters as $all_claim_letter) {
					if($all_claim_letter['case_id'] == $v_row['id']){
						$v_claim_letters[] = $all_claim_letter;
					}
				}
	            $v_row['letters'] = $v_claim_letters;


				$connected_transactions = array();
				$all_connected_transaction_ids = array($v_row['internalTransactionId']);
				if($v_row['link_id'] > 0 && ($creditor['checkbox_1'])) {
					$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND ct.open = 1 AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
					$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['internalTransactionId']));
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

				$v_row['connected_transactions'] = $connected_transactions;

				$transaction_payments = array();
				foreach($all_transaction_payments as $all_transaction_payment) {
					if($v_row['link_id'] > 0 && $all_transaction_payment['link_id'] == $v_row['link_id']){
						if(!in_array($all_transaction_payment['id'], $all_connected_transaction_ids)){
							$transaction_payments[] = $all_transaction_payment;
						}
					}
				}

				$total_transaction_payments = $transaction_payments;
				//
				// $transaction_fees = $v_row['link_id'] > 0 ? $all_transaction_fees_with_keyid[$v_row['link_id']] : array();
				$transaction_fees = array();
				foreach($all_transaction_fees as $all_transaction_fee) {
					if($v_row['link_id'] > 0 && $all_transaction_fee['link_id'] == $v_row['link_id']){
						$transaction_fees[] = $all_transaction_fee;
					}
				}
	            $v_row['transaction_fees'] = $transaction_fees;
	            $v_row['transaction_payments'] = $total_transaction_payments;

				$objections = array();
				foreach($all_objections as $all_objection) {
					if($all_objection['collecting_case_id'] == $v_row['id']) {
						$objections[] = $all_objection;
					}
				}
	            $v_row['objections'] = $objections;

				$comments = array();
				foreach($all_comments as $all_comment) {
					if($all_comment['transaction_id'] == $v_row['internalTransactionId']) {
						$comments[] = $all_comment;
					}
				}
	            $v_row['comments'] = $comments;

				$casesOnReminderCount = 0;
				foreach($all_casesOnReminder as $caseOnreminder){
					if($caseOnreminder['external_customer_id'] == $v_row['external_customer_id']){
						$casesOnReminderCount++;
					}
				}
				$casesOnCollectingCount = 0;
				foreach($all_casesOnCollecting as $caseOnCollecting){
					if($caseOnCollecting['creditor_customer_id'] == $v_row['external_customer_id']){
						$casesOnCollectingCount++;
					}
				}

				$all_debitorCustomers_with_keyid = array();
				if(!isset($all_debitorCustomers_with_keyid[$v_row['external_customer_id']]))
				{
					$s_sql = "SELECT * FROM customer WHERE creditor_id = '".$o_main->db->escape_str($cid)."' AND creditor_customer_id = '".$o_main->db->escape_str($v_row['external_customer_id'])."'";
					$o_query = $o_main->db->query($s_sql);
					$all_debitorCustomers_with_keyid[$v_row['external_customer_id']] = $o_query ? $o_query->row_array() : array();
				}
				$debitorCustomer = $all_debitorCustomers_with_keyid[$v_row['external_customer_id']];
				
				// foreach($all_debitorCustomers as $all_debitorCustomer){
				// 	if($all_debitorCustomer['creditor_customer_id'] == $v_row['external_customer_id']){
				// 		$debitorCustomer = $all_debitorCustomer;
				// 	}
				// }
				$v_row['debitorCustomer'] = $debitorCustomer;

				$transaction = $all_transactions_with_keyid[$v_row['internalTransactionId']];
				// foreach($all_transactions as $all_transaction){
				// 	if($all_transaction['id'] == $v_row['internalTransactionId']){
				// 		$transaction = $all_transaction;
				// 	}
				// }
				$v_row['transaction'] = $transaction;


				$v_row['creditor_profiles'] = $creditor_reminder_custom_profiles;

				$v_row['casesOnReminderCount'] = $casesOnReminderCount;
				$v_row['casesOnCollectingCount'] = $casesOnCollectingCount;

				$profile = array();
				if($v_row['reminder_profile_id'] > 0){
					foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile){
						if($creditor_reminder_custom_profile['id'] == $v_row['reminder_profile_id']) {
							$profile = $creditor_reminder_custom_profile;
						}
					}
					if(!$profile){
						foreach($creditor_reminder_custom_profiles_deleted as $creditor_reminder_custom_profile){
							if($creditor_reminder_custom_profile['id'] == $v_row['reminder_profile_id']) {
								$profile = $creditor_reminder_custom_profile;
							}
						}
					}
				} else {
					if(!$profile) {
						if($debitorCustomer['creditor_reminder_profile_id'] > 0){
							foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile){
								if($creditor_reminder_custom_profile['id'] == $debitorCustomer['creditor_reminder_profile_id']) {
									$profile = $creditor_reminder_custom_profile;
								}
							}
						}
						if(!$profile){
							$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
							if($debitorCustomer['customer_type_collect_addition'] > 0){
								$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
							}
							if($customer_type_collect_debitor == 0) {
								foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile){
									if($creditor_reminder_custom_profile['id'] == $creditor['creditor_reminder_default_profile_for_company_id']) {
										$profile = $creditor_reminder_custom_profile;
									}
								}
							} else {
								foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile){
									if($creditor_reminder_custom_profile['id'] == $creditor['creditor_reminder_default_profile_id']) {
										$profile = $creditor_reminder_custom_profile;
									}
								}
							}
						}
					}
				}
				$unprocessed_profile_values = $profile['unprocessed_profile_values'];
				$profile_values = array();
				foreach($unprocessed_profile_values as $unprocessed_profile_value) {
					$profile_values[$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
				}
				$v_row['profile'] = $profile;
				$v_row['profile_values'] = $profile_values;
	            array_push($customerList, $v_row);
	        }
			
			if($username == "david@dcode.no"){
				$v_return['log'] .= "current tab list processing end-".time()."<br/>";
			}
		}
    } else if($mainlist_filter == "suggestedCases"){
		$customerList = array();
        // $itemCount = get_transaction_count($o_main, $cid, $mainlist_filter, $filters);
		//
        // if(isset($_POST['page'])) {
        //     $page = $_POST['page'];
        // }
        // if(intval($page) == 0){
        //     $page = 1;
        // }
        // $perPage = 1000;
        // $showing = $page * $perPage;
        // $showMore = false;
        // $currentCount = $itemCount;
		//
        // if($showing < $currentCount){
        //     $showMore = true;
        // }
        // $totalPages = ceil($currentCount/$perPage);
		//
        // $customerList = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);
    } else if($mainlist_filter == "transactions"){
		$groupedTransactions = array();
        // $itemCount = get_transaction_count($o_main, $cid, $mainlist_filter, $filters);
		//
        // if(isset($_POST['page'])) {
        //     $page = $_POST['page'];
        // }
        // if(intval($page) == 0){
        //     $page = 1;
        // }
        // $perPage = 1000;
        // $showing = $page * $perPage;
        // $showMore = false;
        // $currentCount = $itemCount;
		//
        // if($showing < $currentCount){
        //     $showMore = true;
        // }
        // $totalPages = ceil($currentCount/$perPage);
		//
        // $invoicesTransactions = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);

        // $totalSum = 0;
        // foreach($invoicesTransactions as $invoicesTransaction) {
        //     $totalSum+=$invoicesTransaction['amount'];
        //     $customerId = $invoicesTransaction['external_customer_id'];
        //     $groupedTransactions[$customerId][] = $invoicesTransaction;
        // }
        // ksort($groupedTransactions);
    }
    $spf_check = false;
    if($creditor['sender_email'] != "") {
        require(__DIR__.'/../../output/includes/SPFCheck/Exception/DNSLookupLimitReachedException.php');
        require(__DIR__.'/../../output/includes/SPFCheck/Exception/DNSLookupException.php');
        require(__DIR__.'/../../output/includes/SPFCheck/DNSRecordGetterInterface.php');
        require(__DIR__.'/../../output/includes/SPFCheck/DNSRecordGetter.php');
        require(__DIR__.'/../../output/includes/SPFCheck/IpUtils.php');
        require(__DIR__.'/../../output/includes/SPFCheck/SPFCheck.php');

        $v_all = array(SPFCheck::RESULT_PASS=>'RESULT_PASS', SPFCheck::RESULT_FAIL=>'RESULT_FAIL', SPFCheck::RESULT_SOFTFAIL=>'RESULT_SOFTFAIL', SPFCheck::RESULT_NEUTRAL=>'RESULT_NEUTRAL', SPFCheck::RESULT_NONE=>'RESULT_NONE', SPFCheck::RESULT_PERMERROR=>'RESULT_PERMERROR', SPFCheck::RESULT_TEMPERROR=>'RESULT_TEMPERROR');
        $v_pass = array(SPFCheck::RESULT_PASS/*, SPFCheck::RESULT_SOFTFAIL*/, SPFCheck::RESULT_NEUTRAL, SPFCheck::RESULT_NONE);

        $o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig ORDER BY default_server DESC");
        $v_email_server_config = $o_query ? $o_query->row_array() : array();

        $s_mailserver_ip = gethostbyname($v_email_server_config['host']);

        $v_email_sender = explode("@", $creditor['sender_email']);
        $s_email_sender_domain = $v_email_sender[1];
        $s_email_sender_ip = gethostbyname($s_email_sender_domain);
        $checker = new SPFCheck(new DNSRecordGetter()); // Uses php's dns_get_record method for lookup.
        $s_result = $checker->isIPAllowed($s_mailserver_ip, $s_email_sender_domain);
        if(in_array($s_result, $v_pass))
        {
            $spf_check = true;
        } else {
            $spf_check = true;
        }
    }

    $creditor['sender_email_spf_error'] = $spf_check;

    $v_return['collectingProcesses'] = $collectingProcesses;
    $v_return['items'] = $customerList;
    $v_return['transactions'] = $groupedTransactions;
    $v_return['itemCount'] = $itemCount;
    $v_return['totalPages'] = $totalPages;
    $v_return['showMore'] = $showMore;
    $v_return['page'] = $page;
    $v_return['showing'] = $showing;
    $v_return['countArray'] = $countArray;
    $v_return['creditor'] = $creditor;

	$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name FROM creditor_reminder_custom_profiles crcp
	JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
	JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE crcp.id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
	$default_creditor_profile_person = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name FROM creditor_reminder_custom_profiles crcp
	JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
	JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE crcp.id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
	$default_creditor_profile_company = ($o_query ? $o_query->row_array() : array());

	$v_return['default_creditor_profile_company'] = $default_creditor_profile_company;
	$v_return['default_creditor_profile_person'] = $default_creditor_profile_person;

	$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.published = 1 AND (ccp.available_for = 2 OR ccp.available_for = 3) ORDER BY ccp.sortnr ASC";
    $o_query = $o_main->db->query($s_sql);
    $company_processes = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp JOIN process_step_types pst ON pst.id = ccp.process_step_type_id  WHERE ccp.content_status < 2 AND ccp.published = 1 AND (ccp.available_for = 1 OR ccp.available_for = 3) ORDER BY ccp.sortnr ASC";
    $o_query = $o_main->db->query($s_sql);
    $person_processes = $o_query ? $o_query->result_array() : array();
	$company_processes_processed = array();
	foreach($company_processes as $company_process) {
		$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		$o_query = $o_main->db->query($s_sql, array($company_process['id']));
		$steps = ($o_query ? $o_query->result_array() : array());

		$company_process['steps'] = $steps;
		$company_processes_processed[] = $company_process;
	}
	$person_processes_processed = array();
	foreach($person_processes as $person_process) {
		$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		$o_query = $o_main->db->query($s_sql, array($person_process['id']));
		$steps = ($o_query ? $o_query->result_array() : array());

		$person_process['steps'] = $steps;
		$person_processes_processed[] = $person_process;
	}

    $v_return['company_processes'] = $company_processes_processed;
    $v_return['person_processes'] = $person_processes_processed;

	$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$system_settings = ($o_query ? $o_query->row_array() : array());

	$v_return['global_locked'] = $system_settings['locked'];
	$v_return['global_locked_message'] = $system_settings['locked_message'];
	$v_return['minimum_amount_move_to_collecting_company_case'] = $system_settings['minimum_amount_move_to_collecting_company_case'];
	$v_return['default_collecting_process_to_move_from_reminder'] = $system_settings['default_collecting_process_to_move_from_reminder'];
	$v_return['default_collecting_process_to_move_from_reminder_not_last_step'] = $system_settings['default_collecting_process_to_move_from_reminder_not_last_step'];
	
	$s_sql = "SELECT * FROM process_step_types WHERE content_status < 2 ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$process_step_types = $o_query ? $o_query->result_array() : array();
    $v_return['process_step_types'] = $process_step_types;

	$s_sql = "SELECT * FROM creditor_processing_batch WHERE creditor_id = ? AND IFNULL(processing_status, 0) <> 1";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_active_batch = $o_query ? $o_query->row_array() : array();
	$v_return['creditor_active_batch'] = $creditor_active_batch;
	if($creditor_active_batch) {
		$s_sql = "SELECT * FROM creditor_processing_batch_line WHERE creditor_processing_batch_id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_active_batch['id']));
		$creditor_active_batch_lines = $o_query ? $o_query->result_array() : array();
		$v_return['creditor_active_batch_lines'] = $creditor_active_batch_lines;
	}
	$process_id_to_move_to = 0;

	if($creditor['collecting_process_to_move_from_reminder'] > 0) {
		$process_id_to_move_to = $creditor['collecting_process_to_move_from_reminder'];
	} else {
		$process_id_to_move_to = $system_settings['default_collecting_process_to_move_from_reminder'];
	}

	$s_sql = "SELECT collecting_cases_collecting_process_steps.* FROM collecting_cases_collecting_process_steps 
	WHERE collecting_cases_collecting_process_id = ? ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($process_id_to_move_to));
	$collecting_process_step_one = ($o_query ? $o_query->row_array() : array());
	$v_return['collecting_process_step_one'] = $collecting_process_step_one;
	
	$s_sql = "SELECT * FROM reminder_minimum_amount ORDER BY currency ASC";
	$o_query = $o_main->db->query($s_sql);
	$reminder_minimum_amounts = $o_query ? $o_query->result_array() : array();
	$v_return['reminder_minimum_amounts'] = $reminder_minimum_amounts;
	$v_return['default_reminder_minimum_amount_noncurrency'] = intval($system_settings['default_reminder_minimum_amount_noncurrency']);
    
	// $list_filter_fil = "reminderLevel";
    // $invoicesOnReminderLevel = get_case_list($o_main, $cid, $list_filter_fil, $filters);
    // foreach($invoicesOnReminderLevel as $invoiceOnReminderLevel) {
    //     $totalSumOriginalClaim = 0;
    //     $s_sql = "SELECT * FROM creditor_invoice WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
    //     $o_query = $o_main->db->query($s_sql, array($invoiceOnReminderLevel['id']));
    //     $invoices = ($o_query ? $o_query->result_array() : array());
    //     foreach($invoices as $invoice) {
    //         $totalSumOriginalClaim += $invoice['collecting_case_original_claim'];
    //     }
    //     $totalOpenAmount+=$totalSumOriginalClaim + $invoiceOnReminderLevel['paid_amount'] + $invoiceOnReminderLevel['credited_amount'];
    // }
    // $list_filter_fil = "collectingLevel";
    // $invoicesOnReminderLevel = get_case_list($o_main, $cid, $list_filter_fil, $filters);
    // foreach($invoicesOnReminderLevel as $invoiceOnReminderLevel) {
    //     $totalSumOriginalClaim = 0;
    //     $s_sql = "SELECT * FROM creditor_invoice WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
    //     $o_query = $o_main->db->query($s_sql, array($invoiceOnReminderLevel['id']));
    //     $invoices = ($o_query ? $o_query->result_array() : array());
    //     foreach($invoices as $invoice) {
    //         $totalSumOriginalClaim += $invoice['collecting_case_original_claim'];
    //     }
    //     $totalOpenAmount += $totalSumOriginalClaim + $invoiceOnReminderLevel['paid_amount'] + $invoiceOnReminderLevel['credited_amount'];
    // }
    //
    // $v_return['totalOpenAmount'] = $totalOpenAmount;

    $v_return['status'] = 1;
}
if($username == "david@dcode.no"){
	$v_return['log'] .= "end-".time()."<br/>";
}
?>
