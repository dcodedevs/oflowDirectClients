<?php
use Mika56\SPFCheck\SPFCheck;
use Mika56\SPFCheck\DNSRecordGetter;

require_once __DIR__ . '/../../output/includes/creditor_functions.php';

$get_collecting_company_info = $v_data['params']['get_collecting_company_info'];
$filters_new = $v_data['params']['filters'];
// Process filter data
$list_filter = $filters_new['list_filter'] ? $filters_new['list_filter'] : 'active';
$customer_filter = $filters_new['customer_filter'] ? $filters_new['customer_filter'] : 0;
$search_filter = $filters_new['search_filter'] ? $filters_new['search_filter'] : '';
$page = $filters_new['page'] ? $filters_new['page'] : 1;
$perPage = $filters_new['perPage'] ? $filters_new['perPage'] : 500;
$mainlist_filter = $filters_new['mainlist_filter'] ? $filters_new['mainlist_filter'] : 'reminderLevel';
$order_field = $filters_new['order_field'] ? $filters_new['order_field'] : '';
$order_direction = $filters_new['order_direction'] ? $filters_new['order_direction'] : '0';
$sublist_filter = $filters_new['sublist_filter'] ? $filters_new['sublist_filter'] : '';

// Return data array
$return_data = array(
    'list' => array()
);
$countArray = array();

$filters = array();
$filters['order_field'] = $order_field;
$filters['order_direction'] = $order_direction;
$filters['search_filter'] = $search_filter;

$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
$o_query = $o_main->db->query($s_sql, array($customer_filter));
$creditor = ($o_query ? $o_query->row_array() : array());

$processes = array();
$cid = $creditor['id'];
if($creditor) {

	$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE content_status < 2 ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$collectingProcesses = ($o_query ? $o_query->result_array() : array());

    $list_filter_fil = "transactions";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['transactionsCount'] = $suggested_count;

    $list_filter_fil = "suggestedCases";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['suggestedCasesCount'] = $suggested_count;

    $list_filter_fil = "reminderLevel";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['invoicesOnReminderLevelCount'] = $suggested_count;

    $filters['list_filter'] = "canSendReminderNow";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['canSendReminderNowCount'] = $suggested_count;

    $filters['list_filter'] = "dueDateNotExpired";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['dueDateNotExpiredCount'] = $suggested_count;

    $filters['list_filter'] = "doNotSend";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['doNotSendCount'] = $suggested_count;


    $filters['list_filter'] = "stoppedWithObjection";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['stoppedWithObjectionCount'] = $suggested_count;

    $filters['list_filter'] = "notPayedConsiderCollectingProcess";
    $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    $countArray['notPayedConsiderCollectingProcessCount'] = $suggested_count;

    // $filters['list_filter'] = "finishedOnReminderLevel";
    // $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    // $countArray['finishedOnReminderLevelCount'] = $suggested_count;

    // $filters['list_filter'] = "movedToCollectingLevel";
    // $suggested_count = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
    // $countArray['movedToCollectingLevelCount'] = $suggested_count;

    // $list_filter_fil = "collectingLevel";
    // $filters['list_filter'] = "";
    // $suggested_count = get_collecting_company_case_count2($o_main, $cid, $list_filter_fil, $filters);
    // $countArray['casesOnCollectingLevelCount'] = $suggested_count;
	//
    // $filters['list_filter'] = "activeOnCollectingLevel";
    // $suggested_count = get_collecting_company_case_count2($o_main, $cid, $list_filter_fil, $filters);
    // $countArray['activeOnCollectingLevelCount'] = $suggested_count;
	//
    // $filters['list_filter'] = "readyToStartInCollectingLevel";
    // $suggested_count = get_collecting_company_case_count2($o_main, $cid, $list_filter_fil, $filters);
    // $countArray['readyToStartInCollectingLevelCount'] = $suggested_count;
	//
    // $filters['list_filter'] = "finishedOnCollectingLevel";
    // $suggested_count = get_collecting_company_case_count2($o_main, $cid, $list_filter_fil, $filters);
    // $countArray['finishedOnCollectingLevelCount'] = $suggested_count;
	//
    // $filters['list_filter'] = "stoppedWithObjection";
    // $suggested_count = get_collecting_company_case_count2($o_main, $cid, $list_filter_fil, $filters);
    // $countArray['stoppedWithObjectionCollectingCount'] = $suggested_count;
	if($get_collecting_company_info){
		$casesOnCollectingLevelCount = get_collecting_company_case_count2($o_main, $cid, "all", $filters);
		$warning_case_count = get_collecting_company_case_count2($o_main, $cid,"warning", $filters);
		$collecting_case_count = get_collecting_company_case_count2($o_main, $cid,"collecting", $filters);
		$warning_closed_case_count = get_collecting_company_case_count2($o_main,$cid, "warning_closed", $filters);
		$collecting_closed_case_count = get_collecting_company_case_count2($o_main, $cid,"collecting_closed", $filters);

	    $countArray['casesOnCollectingLevelCount'] = $casesOnCollectingLevelCount;
	    $countArray['warning_case_count'] = $warning_case_count;
	    $countArray['collecting_case_count'] = $collecting_case_count;
	    $countArray['warning_closed_case_count'] = $warning_closed_case_count;
	    $countArray['collecting_closed_case_count'] = $collecting_closed_case_count;

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

	}

    $groupedTransactions = array();
    if($mainlist_filter == "reminderLevel" || $mainlist_filter == "collectingLevel") {
        $filters['list_filter'] = $list_filter;
        $filters['sublist_filter'] = $sublist_filter;
		if($mainlist_filter == "collectingLevel"){
	        $itemCount = get_collecting_company_case_count2($o_main, $cid, $list_filter, $filters);
		} else {
	        $itemCount = get_transaction_count2($o_main, $cid, $mainlist_filter, $filters);
		}

        $rowOnly = $_POST['rowOnly'];
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $itemCount;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);
        $customerList = array();
		if($mainlist_filter == "collectingLevel"){
			$customerListNonProcessed = get_collecting_company_case_list($o_main, $cid, $list_filter, $filters, $page, $perPage);
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
				LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
				WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
				ORDER BY cccl.claim_type ASC, cccl.created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$claims = ($o_query ? $o_query->result_array() : array());
				$v_row['claims'] = $claims;

				$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$payments = ($o_query ? $o_query->result_array() : array());
				$v_row['payments'] = $payments;

				array_push($customerList, $v_row);
			}
		} else {
	        $customerListNonProcessed = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);

			$collectingcase_ids = array();
			foreach($customerListNonProcessed as $v_row) {
				if($v_row['id'] > 0){
					$collectingcase_ids[] = $v_row['id'];
				}
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

			$s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
			$o_query = $o_main->db->query($s_sql);
			$all_processes_un = ($o_query ? $o_query->row_array() : array());
			$all_processes = array();
			foreach($all_processes_un as $all_process){
				$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? ORDER BY sortnr ASC";
				$o_query = $o_main->db->query($s_sql, array($all_process['id']));
				$old_steps = ($o_query ? $o_query->result_array() : array());
				$all_process['steps'] = $old_steps;
				$all_processes[] = $all_process;
			}

			$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND system_type='InvoiceCustomer' AND (collectingcase_id > 0 OR open = 1)";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_transactions = $o_query ? $o_query->result_array() : array();
			$all_link_ids = array();
			foreach($all_transactions as $all_transaction){
				$all_link_ids[] = $all_transaction['link_id'];
			}
			$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND creditor_id = ? AND link_id IN (".implode(',', $all_link_ids).")";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_transaction_payments = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_transaction_fees = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND content_status < 2";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_debitorCustomers = $o_query ? $o_query->result_array() : array();



			$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name
			FROM creditor_reminder_custom_profiles crcp
			LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
			LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
			WHERE crcp.creditor_id = ? AND crcp.content_status < 2  ORDER BY ccp.sortnr ASC";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$creditor_reminder_custom_profiles_un = ($o_query ? $o_query->result_array() : array());

			$creditor_reminder_custom_profiles = array();
			foreach($creditor_reminder_custom_profiles_un as $creditor_reminder_custom_profile){
				$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['id']));
				$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();
				$creditor_reminder_custom_profile['unprocessed_profile_values'] = $unprocessed_profile_values;
				$creditor_reminder_custom_profiles[] = $creditor_reminder_custom_profile;
			}

			$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id > 0 AND creditor_id = ? AND open = 1 ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($cid));
			$all_casesOnReminder = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT collecting_company_cases.* FROM collecting_company_cases
			LEFT OUTER JOIN customer c ON c.id = collecting_company_cases.debitor_id
			WHERE collecting_company_cases.creditor_id = ? AND collecting_company_cases.case_closed_date = '0000-00-00 00:00:00'";
			$o_query = $o_main->db->query($s_sql, array($v_row['creditorCreditorId']));
			$all_casesOnCollecting = ($o_query ? $o_query->result_array() : array());

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


				$transaction_payments = array();
				foreach($all_transaction_payments as $all_transaction_payment) {
					if($all_transaction_payment['link_id'] == $v_row['link_id']){
						$transaction_payments[] = $all_transaction_payment;
					}
				}

				$total_transaction_payments = $transaction_payments;
				//
				$transaction_fees = array();
				foreach($all_transaction_fees as $all_transaction_fee) {
					if($all_transaction_fee['link_id'] == $v_row['link_id']){
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

				$debitorCustomer = array();
				foreach($all_debitorCustomers as $all_debitorCustomer){
					if($all_debitorCustomer['creditor_customer_id'] == $v_row['external_customer_id']){
						$debitorCustomer = $all_debitorCustomer;
					}
				}
				$v_row['debitorCustomer'] = $debitorCustomer;

				$transaction = array();
				foreach($all_transactions as $all_transaction){
					if($all_transaction['id'] == $v_row['internalTransactionId']){
						$transaction = $all_transaction;
					}
				}
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
					// $s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
					// $o_query = $o_main->db->query($s_sql, array($v_row['reminder_profile_id']));
					// $profile = $o_query ? $o_query->row_array() : array();
				}
				if(!$profile) {
					if($debitorCustomer['creditor_reminder_profile_id'] > 0){
						foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile){
							if($creditor_reminder_custom_profile['id'] == $debitorCustomer['creditor_reminder_profile_id']) {
								$profile = $creditor_reminder_custom_profile;
							}
						}
						// $s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
						// $o_query = $o_main->db->query($s_sql, array($debitorCustomer['creditor_reminder_profile_id']));
						// $profile = $o_query ? $o_query->row_array() : array();
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
							// $s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
							// $o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
							// $profile = $o_query ? $o_query->row_array() : array();
						} else {
							foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile){
								if($creditor_reminder_custom_profile['id'] == $creditor['creditor_reminder_default_profile_id']) {
									$profile = $creditor_reminder_custom_profile;
								}
							}
							// $s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
							// $o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
							// $profile = $o_query ? $o_query->row_array() : array();
						}
					}
				}

				// $s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
				// $o_query = $o_main->db->query($s_sql, array($profile['id']));
				// $unprocessed_profile_values = $o_query ? $o_query->result_array() : array();
				$unprocessed_profile_values = $profile['unprocessed_profile_values'];
				$profile_values = array();
				foreach($unprocessed_profile_values as $unprocessed_profile_value) {
					$profile_values[$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
				}
				$v_row['profile_values'] = $profile_values;
	            array_push($customerList, $v_row);
	        }
		}
    } else if($mainlist_filter == "suggestedCases"){
        $itemCount = get_transaction_count($o_main, $cid, $mainlist_filter, $filters);

        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $perPage = 1000;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $itemCount;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $customerList = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);
    } else if($mainlist_filter == "transactions"){
        $itemCount = get_transaction_count($o_main, $cid, $mainlist_filter, $filters);

        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $perPage = 1000;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $itemCount;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $invoicesTransactions = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);
        $groupedTransactions = array();
        $totalSum = 0;
        foreach($invoicesTransactions as $invoicesTransaction) {
            $totalSum+=$invoicesTransaction['amount'];
            $customerId = $invoicesTransaction['external_customer_id'];
            $groupedTransactions[$customerId][] = $invoicesTransaction;
        }
        ksort($groupedTransactions);
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
	LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
	LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE crcp.id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
	$default_creditor_profile_person = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name FROM creditor_reminder_custom_profiles crcp
	LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
	LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
	WHERE crcp.id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
	$default_creditor_profile_company = ($o_query ? $o_query->row_array() : array());

	$v_return['default_creditor_profile_company'] = $default_creditor_profile_company;
	$v_return['default_creditor_profile_person'] = $default_creditor_profile_person;

	$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.published = 1 AND (ccp.available_for = 2 OR ccp.available_for = 3) ORDER BY ccp.sortnr ASC";
    $o_query = $o_main->db->query($s_sql);
    $company_processes = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id  WHERE ccp.content_status < 2 AND ccp.published = 1 AND (ccp.available_for = 1 OR ccp.available_for = 3) ORDER BY ccp.sortnr ASC";
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


	$s_sql = "SELECT * FROM process_step_types WHERE content_status < 2 ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql);
	$process_step_types = $o_query ? $o_query->result_array() : array();
    $v_return['process_step_types'] = $process_step_types;
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
?>
