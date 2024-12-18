<?php
if ($_POST['cid']) $_GET['cid'] = $_POST['cid'];
$cid = $_GET['cid'];
if($cid > 0) {
	// if($variables->loggID =="byamba@dcode.no"){
	// 	$s_sql = "SELECT ccc.* FROM collecting_company_cases ccc
	// 	LEFT JOIN creditor_transactions ct ON ct.collecting_company_case_id = ccc.id
	// 	WHERE ct.id is null";

	// }
    $action = $_GET['action'] ? $_GET['action'] : '';

    $page = $_GET['page'] ? $_GET['page'] : 1;
    $perPage = $_GET['perPage'] ? $_GET['perPage'] : 200;
    $default_list = "";
    $default_mainlist = "";

    // if(count($customer_listtabs_basisconfig) > 0) {
    // 	$default_list = $customer_listtabs_basisconfig[0]['id'];
    // }

	$s_sql = "SELECT creditor.*, DATE_FORMAT(lastImportedDate, '%Y-%m-%d %H:%i:%s.%f') FROM creditor WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($cid));
    $creditor = ($o_query ? $o_query->row_array() : array());
    $s_sql = "SELECT * FROM employee WHERE email = ?";
    $o_query = $o_main->db->query($s_sql, array($variables->loggID));
    if($o_query && $o_query->num_rows()>0){
        $currentEmployee = $o_query->row_array();
    }

    $filtersList = array("search_filter", "responsibleperson_filter", "projecttype_filter", "sublist_filter");

    if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
    if ($_POST['mainlist_filter']) $_GET['mainlist_filter'] = $_POST['mainlist_filter'];
    foreach($filtersList as $filterName){
    	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
    }
    $mainlist_filter = $_GET['mainlist_filter'] ? ($_GET['mainlist_filter']) : $default_mainlist;
	$default_sublist = "notStarted";
    if($mainlist_filter == "collectingLevel"){
        $default_list = "warning";
    }
    if($mainlist_filter == "reminderLevel"){
        $default_list = "canSendReminderNow";
    }
    if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = '0';}
    if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { $order_field = 'debitor';}

    if($mainlist_filter == "invoice") {
        $default_list = "open";
        if($creditor['create_cases']) {
            $default_list = "suggested";
        }
    }
    $list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
    foreach($filtersList as $filterName){
    	${$filterName} = $_GET[$filterName] ? ($_GET[$filterName]) : '';
    }
    if($responsibleperson_filter == ''){
        $responsibleperson_filter = $currentEmployee['id'];
    }

    $_SESSION['list_filter'] = $list_filter;
    foreach($filtersList as $filterName){
    	$_SESSION[$filterName] = ${$filterName};
    }

    $filters = array();
    foreach($filtersList as $filterName){
    	$filters[$filterName] = ${$filterName};
    }
	// if($variables->loggID == "byamba@dcode.no"){
	// 	require_once __DIR__ . '/creditor_functions.dev.php';
	// } else {
	// }

	if($creditor['id'] == 1041 && 1==1) {
	    require_once __DIR__ . '/creditor_functions_v2.php';
	} else {
	    require_once __DIR__ . '/creditor_functions.php';
	}

    if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

    $s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && $o_query->num_rows()>0){
        $orders_module_id_find = $o_query->row_array();
        $orders_module_id = $orders_module_id_find["uniqueID"];
    }

    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
    $o_query = $o_main->db->query($s_sql, array($creditor['reminder_process_for_company']));
    $reminder_level_case_company = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
    $o_query = $o_main->db->query($s_sql, array($creditor['reminder_process_for_person']));
    $reminder_level_case_person = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ? ORDER BY sortnr ASC";
    $o_query = $o_main->db->query($s_sql, array($creditor['collecting_process_for_company']));
    $collecting_level_case_company = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ? ORDER BY sortnr ASC";
    $o_query = $o_main->db->query($s_sql, array($creditor['collecting_process_for_person']));
    $collecting_level_case_person = ($o_query ? $o_query->row_array() : array());

    //manual case marking
    // if($action == "markInvoiceReady") {
    //     include(__DIR__."/fnc_mark_invoice_ready_for_case.php");
    //     $invoice_id = $_GET['invoice_id'] ? $_GET['invoice_id'] : '';
    //     $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.id = ?
    //     AND (creditor_invoice.collecting_case_id is null  OR creditor_invoice.collecting_case_id = 0)
    //     AND (creditor_invoice.closed is null OR creditor_invoice.closed = '0000-00-00') AND (ready_for_create_case = 0 OR ready_for_create_case is null)";
    //     $o_query = $o_main->db->query($s_sql, array($invoice_id));
    //     $open_invoices = ($o_query ? $o_query->result_array() : array());
    //     foreach($open_invoices as $open_invoice){
    //         $invoice_due_date = $open_invoice['due_date'];
    //         if(time() > strtotime("+ ".$creditor['days_overdue_startcase']." days", strtotime($invoice_due_date))) {
    //             mark_invoice_ready_for_case($open_invoice['id'], $creditor['id'], $variables->loggID);
    //         }
    //     }
    // }
    if($action == "markInvoiceNotCreateCase"){
        $invoice_id = $_GET['invoice_id'] ? $_GET['invoice_id'] : '';
        $checked = isset($_GET['checked']) ? $_GET['checked'] : 0;
        $sql = "UPDATE creditor_invoice SET do_not_create_case = ? WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($checked, $invoice_id));
    }
    // if($action == "create_single_case") {
    //     $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.id = ?
    //     AND (creditor_invoice.collecting_case_id is null  OR creditor_invoice.collecting_case_id = 0)
    //     AND (creditor_invoice.closed is null OR creditor_invoice.closed = '0000-00-00') AND (ready_for_create_case = 1) AND (do_not_create_case = 0 OR do_not_create_case is null)";
    //     $o_query = $o_main->db->query($s_sql, array($_GET['invoice_id']));
    //     $ready_invoices = ($o_query ? $o_query->result_array() : array());
    //     foreach($ready_invoices AS $ready_invoice) {
    //         $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
    //         $o_query = $o_main->db->query($s_sql, array($ready_invoice['collecting_cases_process_id']));
    //         $process_for_handling_cases = ($o_query ? $o_query->row_array() : array());
    //         if($process_for_handling_cases){
    //             $s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.creditor_id = ?
    //             AND (creditor_invoice.collecting_case_id is null  OR creditor_invoice.collecting_case_id = 0)
    //             AND (creditor_invoice.closed is null OR creditor_invoice.closed = '0000-00-00') AND (ready_for_create_case = 1) AND (do_not_create_case = 0 OR do_not_create_case is null)";
    //             $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    //             $ready_invoices = ($o_query ? $o_query->result_array() : array());
    //
    //             foreach($ready_invoices as $ready_invoice) {
    //                 $status = 0;
    //                 $sql = "INSERT INTO collecting_cases SET creditor_id = ?, debitor_id = ?, status = ?, collectinglevel = ?, collecting_cases_process_id = ?, createdBy = 'process', created=NOW(), last_change_date_for_process = NOW()";
    //                 $o_query = $o_main->db->query($sql, array($ready_invoice['creditor_id'], $ready_invoice['debitor_id'],  $status, 0, $process_for_handling_cases['id']));
    //                 if($o_query) {
    //
    //                     $collecting_case_id = $o_main->db->insert_id();
    //
    //                     $claimAmount = $ready_invoice['amount'];
    //
    //                     $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
    //                     WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 0 OR before_or_after_case is null)";
    //                     $o_query = $o_main->db->query($sql, array($ready_invoice['invoice_number'], $ready_invoice['creditor_id']));
    //                     $paymentsBefore = $o_query ? $o_query->result_array() : array();
    //
    //                     foreach($paymentsBefore as $paymentBefore) {
    //                         $claimAmount += $paymentBefore['amount'];
    //                     }
    //
    //                     $sql = "UPDATE creditor_invoice SET collecting_case_id = ?, collecting_case_original_claim = ? WHERE id = ?";
    //                     $o_query = $o_main->db->query($sql, array($collecting_case_id, $claimAmount, $ready_invoice['id']));
    //
    //                 }
    //             }
    //         }
    //     }
    //
    //     $s_sql = "UPDATE creditor SET last_create_case_date = NOW() WHERE creditor.id = ?";
    //     $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    // }

    //create the base cases
    // if($action == "createCases") {
    //     include(__DIR__."/fnc_mark_invoice_ready_for_case.php");
    //
    // 	$s_sql = "SELECT creditor_invoice.* FROM creditor_invoice WHERE creditor_invoice.creditor_id = ?
    //     AND (creditor_invoice.collecting_case_id is null  OR creditor_invoice.collecting_case_id = 0)
    //     AND (creditor_invoice.closed is null OR creditor_invoice.closed = '0000-00-00') AND (ready_for_create_case = 0 OR ready_for_create_case is null) AND (onhold_by_creditor = 0 OR onhold_by_creditor is null)";
    //     $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    //     $open_invoices = ($o_query ? $o_query->result_array() : array());
    //
    //     $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
    //     $o_query = $o_main->db->query($s_sql, array($creditor['reminder_process_id']));
    //     $process_for_handling_cases = ($o_query ? $o_query->row_array() : array());
    //
    //     $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
    //     $o_query = $o_main->db->query($s_sql, array($process_for_handling_cases['id']));
    //     $firstStep = ($o_query ? $o_query->row_array() : array());
    //
    //     foreach($open_invoices as $open_invoice){
    //         $invoice_due_date = $open_invoice['due_date'];
    //         if(time() > strtotime("+ ".$creditor['days_overdue_startcase']." days", strtotime($invoice_due_date))) {
    //             if(intval($firstStep['needs_creditor_approval']) == 0) {
    //                 mark_invoice_ready_for_case($open_invoice['id'], $creditor['id']);
    //             }
    //         }
    //     }
    // }


	if($variables->loggID == "byamba@dcode.no"){

		require_once __DIR__ . '/../../../IntegrationAptic/internal_api/load.php';
		$api = new IntegrationAptic(array(
			'o_main' => $o_main,
		));
		// $case_data_return = $api->get_client("C6B64B5F-E087-41BC-9368-66EEE75B80AD");
		// var_dump($case_data_return);
		
		$case_data_return = $api->get_case("FA86A038-2DD0-47E6-80B4-42E0B5F71586");
		var_dump($case_data_return);

		// $debtor_data_return = $api->get_debtor("760D2EBE-F34A-474F-85BE-B5BCD05E4CF4");
		// var_dump($debtor_data_return);
		
		if($_GET['sync_to_aptic']){
			if($creditor['aptic_client_id'] == ""){
				$hook_file = __DIR__ . '/../../../IntegrationAptic/hooks/create_client.php';
				if (file_exists($hook_file)) {
					include $hook_file;
					if (is_callable($run_hook)) {
						$hook_result = $run_hook($creditor);
					}
				}
			} else {
				// $hook_file = __DIR__ . '/../../../IntegrationAptic/hooks/get_customer.php';
				// if (file_exists($hook_file)) {
				// 	include $hook_file;
				// 	if (is_callable($run_hook)) {
				// 		$hook_result = $run_hook($creditor['aptic_customer_id']);
				// 		var_dump($hook_result);
				// 	}
				// }
				$hook_file = __DIR__ . '/../../../IntegrationAptic/hooks/update_client.php';
				if (file_exists($hook_file)) {
					include $hook_file;
					if (is_callable($run_hook)) {
						$hook_result = $run_hook($creditor);
					}
				}
			}
		}
		// $s_sql = "SELECT cc.*,c.invoiceEmail FROM collecting_cases cc 
		// 	JOIN collecting_cases_claim_letter cccl ON cccl.case_id = cc.id
		// 	JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
		// 	JOIN customer c ON c.id = cc.debitor_id
		// 	WHERE cc.creditor_id=1031 AND cccl.created >= '2024-07-22' AND cccl.created <= '2024-07-28' AND ct.tab_status = 12 ORDER BY cccl.created ASC";
		// $o_query = $o_main->db->query($s_sql);
		// $collecting_cases_mistakenly_sent = ($o_query ? $o_query->result_array() : array());

		// foreach($collecting_cases_mistakenly_sent as $collecting_case_mistakenly_sent){

		// 	$s_sql = "UPDATE collecting_cases SET sent_by_mistake = 1, sent_by_mistake_customer_email = ?
		// 		WHERE id = ?";
		// 	$o_query = $o_main->db->query($s_sql, array($collecting_case_mistakenly_sent['invoiceEmail'], $collecting_case_mistakenly_sent['id']));
		// }

		// require_once __DIR__ . '/../../../'.$creditor['integration_module'].'/internal_api/load.php';
		// $api = new Integration24SevenOffice(array(
		// 	'ownercompany_id' => 1,
		// 	'identityId' => $creditor['entity_id'],
		// 	'creditorId' => $creditor['id'],
		// 	'o_main' => $o_main
		// ));
		// $data['changedAfter'] = date("Y-m-d", strtotime("01.02.2023"));
		
		// $transactionData = array();
		// $transactionData['DateSearchParameters'] = 'DateChangedUTC';
		// $transactionData['date_start'] = $data['changedAfter'];
		// $transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
		// // $transactionData['LinkId'] = 10272870;
		// // $transactionData['bookaccountStart'] = 7830;
		// // $transactionData['bookaccountEnd'] = 7830;
		// $transactionData['TransactionNoStart'] = 100260;
		// $transactionData['TransactionNoEnd'] = 100260;
		
		// // $invoicesTransactions = $api->get_transactions($transactionData, true);
		// $transactionTypes = $api->get_account_list();
		// echo "<pre>";
		// var_dump($transactionTypes);
		// var_dump($invoicesTransactions);
		// echo "</pre>";
		
		
		// $changedAfterDate = isset($creditor['lastImportedDateTimestamp']) ? $creditor['lastImportedDateTimestamp'] : "";
		// if($changedAfterDate != null && $changedAfterDate != "") {
		// 	$now = DateTime::createFromFormat('U.u', $changedAfterDate);
		// 	if($now){
		// 		$dataCustomer['changedAfter'] = $now->format("Y-m-d\TH:i:s.u");
		// 	}
		// }
		// $dataCustomer['changedAfter'] = "2010-01-01";
		// var_dump($dataCustomer['changedAfter']);
		// if(isset($dataCustomer['changedAfter'])) {
		// 	$connect_tries = 0;
		// 	do {
		// 		$connect_tries++;
		// 		$response_customer = $api->get_customer_list($dataCustomer);
		// 		if($response_customer !== null){
		// 			break;
		// 		}
		// 	} while($connect_tries < 11);
		// 	$connect_tries--;

		// 	$customer_list = $response_customer['GetCompaniesResult']['Company'];
		// 	if(isset($customer_list['Id'])){
		// 		$customer_list = array($customer_list);
		// 	}
		// 	var_dump($customer_list);
		// }

		// $s_sql = "SELECT ct.id, ct.collectingcase_id, ct.created, ct.due_date, ct.choose_progress_of_reminderprocess, ct.creditor_id, ct.external_customer_id FROM creditor_transactions ct WHERE collectingcase_id > 0 AND ct.open = 0";
		// $o_query = $o_main->db->query($s_sql);
		// $all_transactions_with_cases = ($o_query ? $o_query->result_array() : array());
		// $all_transactions = array();
		// foreach($all_transactions_with_cases as $all_transactions_with_case) {
		// 	$all_transactions[$all_transactions_with_case['collectingcase_id']] = $all_transactions_with_case;
		// }
		// $s_sql = "SELECT cc.id, cc.created FROM collecting_cases cc";
		// $o_query = $o_main->db->query($s_sql);
		// $all_cases = ($o_query ? $o_query->result_array() : array());

		// foreach($all_cases as $all_case){
		// 	$transaction = $all_transactions[$all_case['id']];
		// 	if($transaction){
		// 		$date_to_compare = $transaction['created'];
		// 		if(strtotime($transaction['due_date']) > strtotime($date_to_compare)) {
		// 			$date_to_compare = $transaction['due_date'];
		// 		}
		// 		if(strtotime($all_case['created']) > strtotime("+30 days", strtotime($date_to_compare))) {
		// 			$process_further = false;
		// 			$s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ?";
		// 			$o_query = $o_main->db->query($s_sql, array($transaction['external_customer_id'], $transaction['creditor_id']));
		// 			$debitorCustomer = $o_query ? $o_query->row_array() : array();
		// 			$s_sql = "SELECT * FROM creditor WHERE id = ? ";
		// 			$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
		// 			$creditor = $o_query ? $o_query->row_array() : array();

		// 			if($debitorCustomer['choose_progress_of_reminderprocess'] == 2 || $v_row['choose_progress_of_reminderprocess'] == 2) {
		// 				$process_further = true;
		// 			}
		// 			if($process_further || $creditor['choose_progress_of_reminderprocess'] == 1) {
		// 				echo $all_case['id']."<br/>";
		// 			}
					
		// 		}
		// 	}
		// }


		// $s_sql = "SELECT cc.* FROM creditor_transactions ct
		// LEFT JOIN collecting_cases cc ON cc.id = ct.collectingcase_id
		// WHERE ct.creditor_id = 1031 AND cc.id is not null AND IFNULL(cc.collecting_cases_process_step_id, 0) = 0 AND cc.reminder_profile_id=2796 ORDER BY ct.created ASC";
	   // $o_query = $o_main->db->query($s_sql);
	   // $collecting_cases_not_started = ($o_query ? $o_query->result_array() : array());
	   // $updated = 0;
	   // foreach($collecting_cases_not_started as $collecting_case){
		//    	$sql = "UPDATE collecting_cases SET reminder_profile_id = 2683, updatedBy='fix-script' WHERE id = ?";
		// 	$o_query = $o_main->db->query($sql, array($collecting_case['id']));
		// 	if($o_query){
		// 		$updated++;
		// 	}
	   // }
	   // echo $updated." updated form";
		// require(__DIR__."/../../output/includes/fnc_move_transaction_to_collecting.php");
		//
		// $kidNumber = generate_case_kidnumber("1041", "100856");
		// var_dump($kidNumber);
		// $v_return = move_transaction_to_collecting("1803", "1", "byamba@dcode.no");
		// $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_currency_rates.php';
		// $currencyName = "EUR";
		// if (file_exists($hook_file)) {
		//    include $hook_file;
		//    if (is_callable($run_hook)) {
		// 		$hook_result = $run_hook(array("creditor_id"=>$creditor['id']));
		// 		if(count($hook_result['currencyRates']) > 0){
		// 			$currencyRates = $hook_result['currencyRates'];
		// 			foreach($currencyRates as $currencyRate) {
		// 				if($currencyRate['symbol'] == $currencyName) {
		// 					$currency_rate = $currencyRate['rate'];
		// 					$error_with_currency = false;
		// 					break;
		// 				}
		// 			}
		// 		}
		//    }
	   // }
	   // var_dump($currency_rate);
		// include(__DIR__."/import_scripts/sync_currency.php");
		// if($creditor['id'] == "2080"){
		// 	$type_no = "";
		// 	$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_type_no.php';
		// 	if (file_exists($hook_file)) {
		// 		include $hook_file;
		// 		if (is_callable($run_hook)) {
		// 			$hook_params = array('creditor_id'=>$creditor['id'], 'username'=> $username);
		// 			$hook_result = $run_hook($hook_params);
		// 			var_dump($hook_result);
		// 			if($hook_result['result']){
		// 				$type_no = $hook_result['result'];
		// 			}
		// 		}
		// 	}
		// }

		// $hook_params = array(
		// 	'transaction_id' => '74647',
		// 	'amount'=> '137.65',
		// 	'text'=>'Rente',
		// 	'type'=>'interest',
        //     'date'=>date("c", strtotime("2022-08-25")),
        //     'dueDate'=>date("c", strtotime("2022-09-06")),
		// 	'type_no'=>$type_no,
		// 	'accountNo'=>'8050',
		// 	'username'=> $variables->loggID,
		// 	'caseId'=>468
		// );
		//
		// $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
		// if (file_exists($hook_file)) {
		// 	include $hook_file;
		// 	if (is_callable($run_hook)) {
		// 		$hook_result = $run_hook($hook_params);
		// 		var_dump($hook_result);
		// 	}
		// }
		// $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_departments_and_projects.php';
		// if (file_exists($hook_file)) {
		// 	include $hook_file;
		// 	if (is_callable($run_hook)) {
		// 		$hook_params = array('creditor_id'=>$creditor['id'], 'username'=> $username);
		// 	 	$hook_result = $run_hook($hook_params);
		// 		$integration_departments = $hook_result['departments'];
		// 		$integration_projects = $hook_result['projects'];
		// 	}
		// }
		// var_dump($hook_result);
		// require_once __DIR__ . '/../../../'.$creditor['integration_module'].'/internal_api/load.php';
		//
		// $v_config = array(
        //     'ownercompany_id' => 1,
        //     'identityId' => $creditor['entity_id'],
        //     'creditorId' => $creditor['id'],
        //     'o_main' => $o_main
        // );
        // $api = new Integration24SevenOffice($v_config);
		//
		// $transaction_types = $api->get_transaction_types();
		// var_dump($transaction_types);
		// $s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND open = 1 ORDER BY created ASC";
	    // $o_query = $o_main->db->query($s_sql, array($creditor['id']));
	    // $transactions_all_for_test = ($o_query ? $o_query->result_array() : array());
		// $invoiceIds = array();
		// foreach($transactions_all_for_test as $transaction_all_for_test){
		// 	$invoiceIds[] = intval($transaction_all_for_test['invoice_nr']);
		// }
		// $dataInvoice = array();
		// $dataInvoice['invoiceIds'] = $invoiceIds;
		// $connect_tries = 0;
		// do {
		// 	$connect_tries++;
		// 	$response_invoice = $api->get_invoice_list($dataInvoice);
		// 	// var_dump($response_invoice);
		// 	if($response_invoice !== null){
		// 		break;
		// 	}
		// } while($connect_tries < 11);
		// $connect_tries--;
		//
		// $invoice_list = $response_invoice['GetInvoicesResult']['InvoiceOrder'];
		// if(isset($invoice_list['InvoiceId'])){
		// 	$invoice_list = array($invoice_list);
		// }
		// $updatedCount = 0;
		// foreach($invoice_list as $single_invoice){
		// 	// $sql = "UPDATE creditor_transactions SET integration_project_id = ?, integration_department_id = ?  WHERE invoice_nr = ? AND creditor_id = ?";
		// 	// $o_query = $o_main->db->query($sql, array($single_invoice['ProjectId'], $single_invoice['DepartmentId'], $single_invoice['InvoiceId'], $creditor['id']));
		// 	// if($o_query){
		// 	// 	$updatedCount++;
		// 	// }
		// }
		// echo $updatedCount." transactions updated";
	}

    if($action == "createCases") {

        include(__DIR__."/fnc_create_case.php");

        $s_sql = "SELECT creditor_transactions.* FROM creditor_transactions WHERE creditor_transactions.creditor_id = ?
        AND (creditor_transactions.collectingcase_id is null  OR creditor_transactions.collectingcase_id = 0)
        AND (creditor_invoice.open = 1)";
        $o_query = $o_main->db->query($s_sql, array($creditor['id']));
        $ready_invoices = ($o_query ? $o_query->result_array() : array());

        $newCaseCount = 0;
        foreach($ready_invoices as $ready_invoice) {
            $caseCreated = create_case_from_transaction($ready_invoice['id'], $creditor['id'], $variables->languageID, true);
            if($caseCreated){
                $newCaseCount++;
            }
        }

        $s_sql = "UPDATE creditor SET last_create_case_date = NOW() WHERE creditor.id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    }

    $creditorId = $creditor['id'];
    if($action == "launchImport"){
        if(is_file(__DIR__."/import_scripts/import_cases2.php")){
            ob_start();
            include(__DIR__."/import_scripts/import_cases2.php");
            $result_output = ob_get_contents();
            $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
            ob_end_clean();
            ?>
            <script type="text/javascript">
                $(function(){
        			$('#popupeditboxcontent').html('');
        			$('#popupeditboxcontent').html(<?php echo json_encode($result_output)?>);
        			out_popup = $('#popupeditbox').bPopup(out_popup_options);
        			$("#popupeditbox:not(.opened)").remove();
                })
            </script>
            <?php
        }
    } else if ($action == "launchProcessingSteps") {
        $creditorId = $creditor['id'];

        if(intval($creditor['choose_progress_of_reminderprocess']) == 0) {
            $collectingLevelOnly = 1;
        } else if(intval($creditor['choose_progress_of_reminderprocess']) == 1){

        }
        include(__DIR__."/process_scripts/handle_cases.php");

    } else if ($action == "launchFile")  {
        if(is_file(__DIR__."/import_scripts/get_file.php")){
            include(__DIR__."/import_scripts/get_file.php");
        }
    } else if($action == "syncCustomer") {
        if(is_file(__DIR__."/import_scripts/sync_customers.php")){
            ob_start();
            include(__DIR__."/import_scripts/sync_customers.php");
            $result_output = ob_get_contents();
            $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
            ob_end_clean();
            ?>
            <script type="text/javascript">
                $(function(){
        			$('#popupeditboxcontent').html('');
        			$('#popupeditboxcontent').html(<?php echo json_encode($result_output)?>);
        			out_popup = $('#popupeditbox').bPopup(out_popup_options);
        			$("#popupeditbox:not(.opened)").remove();
                })
            </script>
            <?php
        }
    }
    if($_GET['group_by_debitor']) {
        $groupedbyDebitors = get_invoice_list_grouped($o_main, $cid, $list_filter, $filters);
    } else {
        $groupedbyDebitors[] = array("debitor_id"=>0);
    }

	$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE content_status < 2 ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$collectingProcesses = ($o_query ? $o_query->result_array() : array());
	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($cid));
    $creditor = ($o_query ? $o_query->row_array() : array());

    $_SESSION['listpagePerPage'] = $perPage;
    $_SESSION['listpagePage'] = $page;

    $s_sql = "SELECT * FROM accountinfo";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && $o_query->num_rows()>0){
        $v_accountinfo = $o_query->row_array();
    }
    $statusArray = array($formText_Active_output, $formText_Finished_output, $formText_Objection_output, $formText_Canceled_output);


    $selectedProcesses = array();

    if($creditor['create_cases'] && $list_filter == "suggested") {
        $s_sql = "SELECT creditor_manualprocess_connection.*, collecting_cases_process.name FROM creditor_manualprocess_connection
        LEFT OUTER JOIN collecting_cases_process ON collecting_cases_process.id = creditor_manualprocess_connection.process_id
        WHERE creditor_manualprocess_connection.creditor_id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditor['id']));
        $connections = ($o_query ? $o_query->result_array() : array());
        foreach($connections as $connection) {
            array_push($selectedProcesses, $connection);
        }
    }
    // $list_filter_fil = "transactions";
    // $transactionsCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

    // $list_filter_fil = "all_transactions";
    // $allTransactionsCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

    // $list_filter_fil = "suggestedCases";
    // $suggestedCasesCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

    // $list_filter_fil = "reminderLevel";
    // $invoicesOnReminderLevelCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

	if($mainlist_filter == "reminderLevel"){
		// $filters['list_filter'] = "canSendReminderNow";
		// $canSendReminderNowCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

		// $filters['list_filter'] = "dueDateNotExpired";
		// $dueDateNotExpiredCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

		// $filters['list_filter'] = "doNotSend";
		// $doNotSendCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);


		// $filters['list_filter'] = "stoppedWithObjection";
		// $stoppedWithObjectionCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

		// $filters['list_filter'] = "notPayedConsiderCollectingProcess";
		// $notPayedConsiderCollectingProcessCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);

		// $filters['list_filter'] = "allTransactionsWithoutCases";
		// $allTransactionsWithoutCasesCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);


		// $filters['list_filter'] = "finishedOnReminderLevel";
		// $finishedOnReminderLevelCount = get_transaction_count2($o_main, $cid, $list_filter_fil, $filters);
	}

	// $casesOnCollectingLevelCount = get_collecting_company_case_count2($o_main, $cid, "all", $filters);
	// $warning_case_count = get_collecting_company_case_count2($o_main, $cid,"warning", $filters);
	// $collecting_case_count = get_collecting_company_case_count2($o_main, $cid,"collecting", $filters);
	// $warning_closed_case_count = get_collecting_company_case_count2($o_main,$cid, "warning_closed", $filters);
	// $collecting_closed_case_count = get_collecting_company_case_count2($o_main, $cid,"collecting_closed", $filters);

	if($list_filter == 'collecting' || $list_filter == 'warning') {
		// $countFilters = $filters;
		// $countFilters['sublist_filter'] = "canSendNow";
		// $canSendNowCount = get_collecting_company_case_count2($o_main, $cid,$list_filter, $countFilters);

		// $countFilters['sublist_filter'] = "notStarted";
		// $notStartedCount = get_collecting_company_case_count2($o_main, $cid,$list_filter, $countFilters);

		// $countFilters['sublist_filter'] = "dueDateNotExpired";
		// $dueDateNotExpiredCount = get_collecting_company_case_count2($o_main,$cid, $list_filter, $countFilters);

		// $countFilters['sublist_filter'] = "stoppedWithObjection";
		// $stoppedWithObjectionCount = get_collecting_company_case_count2($o_main, $cid,$list_filter, $countFilters);
	}
    ?>
    <?php if (!$rowOnly) {?>
		<?php include(__DIR__."/creditor_list_filter.php"); ?>
    	<?php
        foreach($groupedbyDebitors as $groupedbyDebitor) {
            // $debitor_sql = "";
            // if($groupedbyDebitor['debitor_id'] > 0) {
            //     $debitor_sql = " AND p.debitor_id = ".$groupedbyDebitor['debitor_id'];
            // }
            // $s_sql = "SELECT p.*, c2.name as debitorName,  SUM(if(p.creditnote = 1,p.amount,0)) AS credited_amount, SUM(if(COALESCE(p.creditnote, 0) = 0,p.amount,0)) AS paid_amount
            // FROM creditor_invoice_payment p
            // JOIN customer c2 ON c2.creditor_customer_id = p.external_customer_id
            // WHERE p.creditor_id = ? AND p.invoice_number is null AND p.open = 1 ".$debitor_sql." GROUP BY c2.id";
            // $o_query = $o_main->db->query($s_sql, array($creditor['id']));
            // $paymentsWithoutInvoice = ($o_query ? $o_query->result_array() : array());

            // $s_sql = "SELECT p.*, c2.name as debitorName,  SUM(if(p.creditnote = 1,p.amount,0)) AS credited_amount, SUM(if(COALESCE(p.creditnote, 0) = 0,p.amount,0)) AS paid_amount
            // FROM creditor_invoice_payment p
            // JOIN creditor_transactions i ON i.invoice_nr = p.invoice_number
            // JOIN customer c2 ON c2.id = i.debitor_id
            // WHERE p.creditor_id = ? AND i.id is not null AND (i.open = 0 OR i.open is null) ".$debitor_sql." AND p.open = 1 GROUP BY i.id";
            // $o_query = $o_main->db->query($s_sql, array($creditor['id']));
            // $paymentsWithClosedInvoice = ($o_query ? $o_query->result_array() : array());

            $filters['order_field'] = $order_field;
            $filters['order_direction'] = $order_direction;
            $filters['search_filter'] = $search_filter;

            $filters['debitor_id'] = $groupedbyDebitor['debitor_id'];

            $filters['list_filter'] = $list_filter;
			if($mainlist_filter=="reminderLevel") {
				include("ajax.creditor_list_reminders.php");
			} else if ($mainlist_filter == "collectingLevel") {
				include("ajax.creditor_list_collecting.php");
			} else if ($mainlist_filter == "transactions" || $mainlist_filter == "all_transactions") {
				include("ajax.creditor_list_transactions.php");
			}
    	}
		?>
        <style>
        .edit_invoice,
        .delete_invoice {
            cursor: pointer;
            color: #46b2e2;
        }
        .putonhold {
            cursor: pointer;
            color: #eea86a;
        }

        .gtable_cell_head {
			font-weight: bold;
		}
		select.create_case_process {
			width: 180px;
		}
		select.create_case_process_step {
			width: 180px;
		}
		.create_case_process_step {
			display: none;
		}
		.process_actions {
			display: none;
			position: relative;
		}
		select.case_action {
		}
		.step_action {
			display: none;
			position: relative;
		}
		.override_action {
		}
		.override_action_wrapper {
			display: none;
			position: absolute;
			padding: 5px 5px;
			border: 1px solid #cecece;
			background: #fff;
			left:-35px;
			top:18px;
			color: #000;
		}
		.next_step {
			width: 350px;
		}
		.saldo_moreinfo_label {
			cursor: pointer;
			color: #0284C9;
		}
		.saldo_moreinfo_text {
			display: none;
			padding: 2px 5px;
			border: 1px solid #cecece;
			background: #fff;
			z-index: 10;
			position: absolute;
			width: 200px;
			max-height: 200px;
			overflow: auto;
			text-align: left;
		}
		.saldo_moreinfo:hover .saldo_moreinfo_text {
			display: block;
		}
		.processToNext {
			float: right;
			color: #fff;
			width: 80px;
			padding: 4px 10px;
			font-size: 11px;
			background: #0b9b32;
			border-radius: 3px;
			text-align: center;
			cursor: pointer;
			font-weight: bold;
		}
		.stopCase {
			float: right;
			color: #fff;
			width: 80px;
			padding: 4px 10px;
			font-size: 11px;
			background: #0b9b32;
			border-radius: 3px;
			text-align: center;
			cursor: pointer;
			font-weight: bold;
		}
		.case_step_wrapper {
			float: left;
			width: 200px;
		}
		.case_step_wrapper select {
			border: 1px solid #d6d8da;
			border-radius: 5px;
		}
		.case_step_wrapper .case_step {
			width:100%;
			height: 38px;
		}
		.action_icon_wrapper {
			display: inline-block;
			vertical-align: middle;
		}
		.action_icon_wrapper select {
			vertical-align: middle;
		}
		.email_wrapper {
			display: inline-block;
			vertical-align: middle;
		}
		.email_wrapper_text {
			color: #a7a7a7;
			margin-left: 10px;
		}
		.processToNextFromInvoice {
			float: right;
			color: #fff;
			width: 80px;
			padding: 4px 10px;
			font-size: 11px;
			background: #0b9b32;
			border-radius: 3px;
			text-align: center;
			cursor: pointer;
			font-weight: bold;
		}
		input[type="checkbox"].checkAll {
			margin-right: 0px;
		}
		.manualActionButton {
			float: right;
			color: #fff;
			padding: 10px 15px;
			font-size: 14px;
			background: #0b9b32;
			border-radius: 3px;
			text-align: center;
			cursor: pointer;
			font-weight: bold;
			margin-top: 10px;
		}



		.hoverEye {
			position: relative;
			color: #0284C9;
			margin-top: 2px;
		}
		.hoverEye .hoverInfo {
			font-family: 'PT Sans', sans-serif;
			width: 450px;
			display: none;
			color: #000;
			position: absolute;
			right: 0%;
			top: 100%;
			padding: 5px 10px;
			background: #fff;
			border: 1px solid #ccc;
			z-index: 1;
			max-height: 300px;
			overflow: auto;
		}
		.hoverEye .hoverInfo2 {
			width: 250px;
		}
		.hoverEye .hoverInfo3 {
			width: 300px;
		}
		.hoverEye .hoverInfoSmall {
			width: 200px;
		}
		.hoverEye .hoverInfoBig {
			width: 400px;
		}
		.hoverEye .hoverInfoAuto {
			width: auto;
		}
		.hoverEye .hoverInfo.hoverInfoLeft {
			right: auto;
			left: 0;
		}
		.hoverEye.hover .hoverInfo {
			display: block;
		}
		.hoverEye.menuHoverEye {
			float: right;
		}
		.hoverEye.arrowHoverEye {
			float: right;
		}
		.hoverEye.customerHoverEye {
			display: inline-block;
			vertical-align: middle;
			margin-top: 0px;
			color: #a7a7a7;
		}

		.edit_customer_settings {
			float: right;
			cursor: pointer;
			color: #2996e7;
		}
		.orderBy {
			cursor: pointer;
		}
		.gtable_cell_head .orderBy {
			padding: 3px 0px;
		}
		.ordering {
			display: inline-block;
			vertical-align: middle;
		}
		.ordering div {
			display: block;
			line-height: 8px;
			color: #46b2e2;
		}
		.editCaseStep {
			cursor: pointer;
		}
		.checkboxColumn {
			text-align: center;
		}
		.fixedTable {
			table-layout: fixed;
			width: 100%;
		}
        </style>
    <script type="text/javascript">
    	var out_popup;
    	var out_popup_options={
    		follow: [true, false],
    		modalClose: false,
    		escClose: false,
    		closeClass:'b-close',
    		onOpen: function(){
    			$(this).addClass('opened');
    			//$(this).find('.b-close').on('click', function(){out_popup.close();});
    		},
    		onClose: function(){
    			$(this).removeClass('opened');
    		}
    	};
    	$(function() {
    		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
    			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
    		});
            $("#checkAllSuggested").off("click").on("click", function(){
                if($(this).is(":checked")){
                    $(".suggestedToProcess").prop("checked", true);
                } else {
                    $(".suggestedToProcess").prop("checked", false);
                }
            })
			$(".suggestedToProcess").on("change", function(){
				var prints_counter = 0;
				var emails_counter = 0;
				$(".suggestedToProcess").each(function(index, element){
					if($(element).is(":checked")){
						var action_type = $(element).data("action_type");
						if(action_type == 0){
							prints_counter++;
						} else if(action_type == 1){
							emails_counter++;
						}
					}
				})

				$(".sendReminders .emails_counter").html(emails_counter);
				$(".sendReminders .prints_counter").html(prints_counter);
			})
            $(".edit_invoice").unbind("click").on('click', function(e){
        		e.preventDefault();
        		var data = {
        			creditor_id: '<?php echo $cid;?>',
                    invoice_id: $(this).data("invoice_id")
        		};
        		ajaxCall('edit_invoice', data, function(json) {
        			$('#popupeditboxcontent').html('');
        			$('#popupeditboxcontent').html(json.html);
        			out_popup = $('#popupeditbox').bPopup(out_popup_options);
        			$("#popupeditbox:not(.opened)").remove();
        		});
        	});
            $(".delete_invoice").unbind("click").on('click', function(e){
        		e.preventDefault();
        		var data = {
                    invoice_id: $(this).data("invoice_id"),
                    action: "deleteInvoice"
        		};
                bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
        			if (result) {
                		ajaxCall('edit_invoice', data, function(json) {
                            if(json.html != ""){
                    			$('#popupeditboxcontent').html('');
                    			$('#popupeditboxcontent').html(json.html);
                    			out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    			$("#popupeditbox:not(.opened)").remove();
                            } else {
                                var data = {
                                    building_filter:$(".buildingFilter").val(),
                        	        customergroup_filter: $(".customerGroupFilter").val(),
                                    mainlist_filter: '<?php echo $mainlist_filter; ?>',
                        	        list_filter: '<?php echo $list_filter; ?>',
                                    cid: '<?php echo $cid;?>',
                        	        search_filter: $('.searchFilter').val(),
                                    search_by: $(".searchBy").val(),
                                    order_field: '<?php echo $order_field;?>',
                                    order_direction: '<?php echo $order_direction;?>'
                                }
                                loadView("creditor_list", data);
                            }
                		});
                    }
                })
        	});

    	});

        $(".page-link").on('click', function(e) {
    	    page = $(this).data("page");
    	    e.preventDefault();
    	    var data = {
    	        building_filter:$(".buildingFilter").val(),
    	        customergroup_filter: $(".customerGroupFilter").val(),
                mainlist_filter: '<?php echo $mainlist_filter; ?>',
    	        list_filter: '<?php echo $list_filter; ?>',
                cid: '<?php echo $cid;?>',
    	        search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
    	        page: page
    	    };
    	    ajaxCall('creditor_list', data, function(json) {
    	        $('.p_pageContent').html(json.html);
    	        if(json.html.replace(" ", "") == ""){
    	            $(".showMoreCustomersBtn").hide();
    	        }

    	    });
        });
        $('.showMoreCustomersBtn').on('click', function(e) {
            page = parseInt(page)+1;
            e.preventDefault();
            var data = {
                building_filter: $(".buildingFilter").val(),
                customergroup_filter: $(".customerGroupFilter").val(),
                mainlist_filter: '<?php echo $mainlist_filter; ?>',
                list_filter: '<?php echo $list_filter; ?>',
                cid: '<?php echo $cid;?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                page: page,
                rowOnly: 1
            };
            ajaxCall('creditor_list', data, function(json) {
                $('.p_pageContent .gtable').append(json.html);
                $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
                if(json.html.replace(" ", "") == ""){
                    $(".showMoreCustomersBtn").hide();
                }
            });
        });
        <?php if(intval($_GET['group_by_debitor']) == 0) { ?>
        	$(".orderBy").off("click").on("click", function(){
                var order_field = $(this).data("orderfield");
                var order_direction = $(this).data("orderdirection");

                var data = {
                    building_filter:$(".buildingFilter").val(),
        	        customergroup_filter: $(".customerGroupFilter").val(),
                    mainlist_filter: '<?php echo $mainlist_filter; ?>',
        	        list_filter: '<?php echo $list_filter; ?>',
                    cid: '<?php echo $cid;?>',
        	        search_filter: $('.searchFilter').val(),
                    search_by: $(".searchBy").val(),
                    order_field: order_field,
                    order_direction: order_direction
                }
                loadView("creditor_list", data);
            })
        <?php } ?>
        $(".mark_ready").on("click", function(e){
            e.preventDefault();

            var data = {
                mainlist_filter: '<?php echo $mainlist_filter;?>',
                list_filter: '<?php echo $list_filter;?>',
                department_filter: $('.filterDepartment').val(),
                search_filter: $('.searchFilter').val(),
                cid: '<?php echo $cid;?>',
                action: 'markInvoiceReady',
                invoice_id: $(this).data("invoiceid"),
                process_id: $(this).data("processid")
            };
            loadView("creditor_list", data);
        })
        $(".create_case").on("click", function(e){
            e.preventDefault();

            var data = {
                mainlist_filter: '<?php echo $mainlist_filter;?>',
                list_filter: '<?php echo $list_filter;?>',
                department_filter: $('.filterDepartment').val(),
                search_filter: $('.searchFilter').val(),
                cid: '<?php echo $cid;?>',
                action: 'create_single_case',
                invoice_id: $(this).data("invoiceid")
            };
            loadView("creditor_list", data);
        })

        $(".force_mark_ready").on("click", function(e){
            e.preventDefault();

            var data = {
                mainlist_filter: '<?php echo $mainlist_filter;?>',
                list_filter: '<?php echo $list_filter;?>',
                department_filter: $('.filterDepartment').val(),
                search_filter: $('.searchFilter').val(),
                cid: '<?php echo $cid;?>',
                action: 'forceMarkReady',
                invoice_id: $(this).data("invoiceid")
            };
            loadView("creditor_list", data);
        })

        $(".mark_to_not_create_case").on("click", function(e){
            e.preventDefault();
            var checked = 0;
            if($(this).is(":checked")){
                checked = 1;
            }
            var data = {
                mainlist_filter: '<?php echo $mainlist_filter;?>',
                list_filter: '<?php echo $list_filter;?>',
                department_filter: $('.filterDepartment').val(),
                search_filter: $('.searchFilter').val(),
                cid: '<?php echo $cid;?>',
                action: 'markInvoiceNotCreateCase',
                invoice_id: $(this).val(),
                checked: checked,
            };
            loadView("creditor_list", data);
        })

		$(".putonhold").off("click").on("click", function (e){
			e.preventDefault();
			var data = {
				case_id: $(this).data("case-id"),
				creditorId: '<?php echo $cid;?>'
			};
			ajaxCall('putonhold_case', data, function(json) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
			});
		})
        $(".output-close-objection").unbind("click").on('click', function(e){
			e.preventDefault();
			var data = {
				collecting_case_id: $(this).data('case-id'),
				cid: $(this).data('objection-id')
			};
			ajaxCall('close_objection', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		});
        $(".sendReminders").off("click").on("click", function(e){
            e.preventDefault();
            var formdata = $(".checkCaseToProcessForm").serializeArray();
            var data = {};
            $(formdata ).each(function(index, obj){
                if(data[obj.name] != undefined) {
                    if(Array.isArray(data[obj.name])){
                        data[obj.name].push(obj.value);
                    } else {
                        data[obj.name] = [data[obj.name], obj.value];
                    }
                } else {
                    data[obj.name] = obj.value;
                }
            });
            ajaxCall('processCases', data, function(json) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                out_popup.addClass("close-reload-creditor");
                $("#popupeditbox:not(.opened)").remove();
            });
        })
		$(".hoverEye").hover(
			function(){$(this).addClass("hover");},
			function(){
				var item = $(this);
				setTimeout(function(){
					if(item.is(":hover")){

					} else {
						item.removeClass("hover");
					}
				}, 300)
			}
		)
		$(".edit_transaction_settings").off("click").on("click", function(){
			var data = {
				transaction_id: $(this).data("transaction-id")
			};
			 ajaxCall('choose_settings', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			 })
		})
		$(".edit_case_settings").off("click").on("click", function(){
			var data = {
				case_id: $(this).data("case-id")
			};
			 ajaxCall('choose_settings', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			 })
		})

		$(".edit_customer_settings").off("click").on("click", function(){
			var data = {
				customer_id: $(this).data("customer-id"),
				transaction_id: $(this).data("transaction-id")
			};
			 ajaxCall('choose_settings', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			 })
		})

		$(".change_customer_type").off("click").on("click", function (e){
			e.preventDefault();
			var data = {
				customer_id: $(this).data("customer-id"),
				creditor_id: $(this).data("creditor-id"),
				customer_type: $(this).data("customer-type")
			};
			ajaxCall('change_customer_type', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})
		<?php if($search_filter != "") { ?>
			$(".filteredCountRow .selectionCount").html("<?php echo count($customerList);?>");
			$(".filteredCountRow").show();
		<?php } ?>


		$(".resetTheCase").off("click").on("click", function(){
			var data = {
				case_id: $(this).data("caseid")
			};
			ajaxCall('reset_case', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload-creditor");
				$("#popupeditbox:not(.opened)").remove();
			});
		})
		$(".full_reset_case").off("click").on("click", function(){
			var data = {
				case_id: $(this).data("case-id")
			};
			ajaxCall('full_reset_case', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})
		$(".createRestNote").off("change").on("click", function(){
			var data = {
				case_id: $(this).data("caseid")
			};
			ajaxCall('create_rest_note', data, function(json) {
				if(json.data.success == 1){
					var data = {
						list_filter: '<?php echo $list_filter; ?>',
						mainlist_filter: '<?php echo $mainlist_filter; ?>',
						customer_filter:$(".customerId").val(),
					};
					loadView("list", data);
				} else {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					out_popup.addClass("close-reload-creditor");
					$("#popupeditbox:not(.opened)").remove();
				}
			});
		})

		$(".move_case_to_collecting").off("click").on("click", function(){
			var data = {
				transaction_id: $(this).data("transactionid"),
				process_id: $(this).data("processid")
			};
			bootbox.confirm('<?php echo $formText_MoveCaseToTheCollectingProcess_output; ?>', function(result) {
				if (result) {
					ajaxCall('move_case_to_collecting', data, function(json) {
						if(json.error || json.html != "") {
							if(json.html != ""){
								$('#popupeditboxcontent').html('');
								$('#popupeditboxcontent').html(json.html);
								out_popup = $('#popupeditbox').bPopup(out_popup_options);
								$("#popupeditbox:not(.opened)").remove();
							} else {
								$('#popupeditboxcontent').html('');
								$('#popupeditboxcontent').html(json.error);
								out_popup = $('#popupeditbox').bPopup(out_popup_options);
								$("#popupeditbox:not(.opened)").remove();
							}
						} else {
							var data = {
								list_filter: '<?php echo $list_filter; ?>',
								mainlist_filter: '<?php echo $mainlist_filter; ?>',
								customer_filter:$(".customerId").val(),
								search_filter: $('.searchFilter').val(),
								search_by: $(".searchBy").val()
							};
							loadView("list", data);
						}
					});
				}
			})
		})
    </script>
    <?php } ?>
<?php } else {
    echo $formText_MissingCreditorId_output;
} ?>
