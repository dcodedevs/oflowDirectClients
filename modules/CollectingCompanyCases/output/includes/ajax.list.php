<?php
$default_list = "all";

$mainlist_filter = "collectingLevel";
$default_sublist_filter = "notStarted";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }
$s_sql = "SELECT * FROM employee WHERE email = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
if($o_query && $o_query->num_rows()>0){
    $currentEmployee = $o_query->row_array();
}

$filtersList = array("search_filter","amount_from_filter", "debitor_type_filter", "cases_without_fee_filter","amount_to_filter", "responsibleperson_filter", "projecttype_filter", "sub_status_filter", "sublist_filter", "closed_reason_filter", "show_not_zero_filter");

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
foreach($filtersList as $filterName){
	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
}

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
foreach($filtersList as $filterName){
	${$filterName} = isset($_GET[$filterName]) ? ($_GET[$filterName]) : '';
	if($list_filter == "collecting" || $list_filter == "warning") {
		if($filterName == "sublist_filter") {
			if($_GET[$filterName] == ""){
				${$filterName} = "notStarted";
			}
		}
	}
}

if($responsibleperson_filter == ''){
    $responsibleperson_filter = $currentEmployee['id'];
}

$_SESSION['list_filter'] = $list_filter;
$_SESSION['mainlist_filter'] = $mainlist_filter;
foreach($filtersList as $filterName){
	$_SESSION[$filterName] = ${$filterName};
}

$filters = array();
foreach($filtersList as $filterName){
	$filters[$filterName] = ${$filterName};
}
if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = '';}
if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { $order_field = '';}
$filters['order_field'] = $order_field;
$filters['order_direction'] = $order_direction;
if(isset($_GET['page'])){ $_POST['page'] = $_GET['page'];}

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $orders_module_id_find = $o_query->row_array();
    $orders_module_id = $orders_module_id_find["uniqueID"];
}

// $s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY sortnr ASC";
// $o_query = $o_main->db->query($s_sql, array());
// $main_statuses = ($o_query ? $o_query->result_array() : array());

// $case_count = array();
// foreach($main_statuses as $main_status) {
//     $case_count[$main_status['id']] = get_customer_list_count($o_main, $main_status['id'], $filters);
// }

// $all_case_count = get_customer_list_count($o_main, "all", $filters);
// $warning_case_count = get_customer_list_count($o_main, "warning", $filters);
// $collecting_case_count = get_customer_list_count($o_main, "collecting", $filters);
// $warning_closed_case_count = get_customer_list_count($o_main, "warning_closed", $filters);
// $collecting_closed_case_count = get_customer_list_count($o_main, "collecting_closed", $filters);
// $company_fee_paid_count = get_customer_list_count($o_main, "company_fee_paid", $filters);
// $company_fee_notpaid_count = get_customer_list_count($o_main, "company_fee_notpaid", $filters);
// $without_fee_paid_count = get_customer_list_count($o_main, "without_fee_paid", $filters);
// $without_fee_notpaid_count = get_customer_list_count($o_main, "without_fee_notpaid", $filters);
// $due_date_issue_count = get_customer_list_count($o_main, "due_date_issue", $filters);
// $consider_count = get_customer_list_count($o_main, "consider", $filters);
// $cases_to_check_count = get_customer_list_count($o_main, "cases_to_check", $filters);
// $deleted_count = get_customer_list_count($o_main, "deleted", $filters);
// $currencyNewCaseCount = get_customer_list_count($o_main, "currency_new_case", $filters);
// $currencyRecalculatedCount = get_customer_list_count($o_main, "currency_recalculated", $filters);


// $objection_count = get_customer_list_count($o_main, "cases_objection", $filters);
//
// $invoicesOnReminderLevelCount = get_customer_list_count($o_main, "cases_on_reminderlevel", $filters);
// $casesOnCollectingLevelCount = get_customer_list_count($o_main, "cases_on_collectinglevel", $filters);
if($list_filter == 'collecting' || $list_filter == 'warning') {
	if($variables->loggID == "byamba@dcode.no"){
		var_dump(microtime()." start");
	}
	$countFilters = $filters;
	$countFilters['sublist_filter'] = "canSendNow";
	$canSendNowCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "notStarted";
	$notStartedCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "dueDateNotExpired";
	$dueDateNotExpiredCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "stoppedWithObjection";
	$stoppedWithObjectionCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "paused";
	$pausedCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "manualProcess";
	$manualProcessCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "surveillance";
	$surveillanceCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "disputed";
	$disputedCount = get_customer_list_count2($o_main, $list_filter, $countFilters);

	$countFilters['sublist_filter'] = "completed";
	$completedCount = get_customer_list_count2($o_main, $list_filter, $countFilters);


	$itemCount = get_customer_list_count2($o_main, $list_filter, $filters);
	if($variables->loggID == "byamba@dcode.no"){
		var_dump(microtime()." end count");
	}
} else {
	
	if($variables->loggID == "byamba@dcode.no"){
		var_dump(microtime()." counting start");
	}
	$itemCount = get_customer_list_count($o_main, $list_filter, $filters);
	
	if($variables->loggID == "byamba@dcode.no"){
		var_dump(microtime()." counting start");
	}
}
$initialCall = true;
if($list_filter != "all"){
	$initialCall = false;
}

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

if($variables->loggID == "byamba@dcode.no"){
	var_dump(microtime()." query start");
}
$customerList = get_customer_list($o_main, $list_filter, $filters, $page, $perPage);

if($variables->loggID == "byamba@dcode.no"){
	var_dump(microtime()." query end");
}

// if($debitor_type_filter == 2){
// 	$customerListNoLimit = get_customer_list($o_main, $list_filter, $filters, 1, 1000);
// 	foreach($customerListNoLimit as $single){
// 		if(!$single['without_fee_notpaid']){
// 			$s_sql = "UPDATE collecting_company_cases SET company_fee_notpaid = 1 WHERE id = '".$o_main->db->escape_str($single['id'])."'";
// 			$o_query = $o_main->db->query($s_sql);
// 		}
// 	}
// }

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;
$_SESSION['sublist_filter'] = $sublist_filter;
$_SESSION['closed_reason_filter'] = $closed_reason_filter;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$statusArray = array($formText_Active_output, $formText_Finished_output, $formText_Objection_output, $formText_Canceled_output, $formText_Inactive_output);
$closed_reasons = array($formText_FullyPaid_output, $formText_PayedWithLessAmountForgiven_output, $formText_ClosedWithoutAnyPayment_output,$formText_ClosedWithPartlyPayment_output,$formText_CreditedByCreditor_output,$formText_DrawnByCreditorToDeleteFees_output);


?>
<?php if (!$rowOnly) { ?>
	<?php if(!$_POST['updateOnlyList']){?>
		<?php include(__DIR__."/list_filter.php"); ?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){?>
	<div class="resultTableWrapper">
	<?php } ?>
	<div class="gtable" id="gtable_search">
	    <div class="gtable_row">
            <?php if($list_filter == 2 || $list_filter == 4) { ?>
                <div class="gtable_cell gtable_cell_head orderBy" data-orderfield="stopped_date" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
                    <?php echo $formText_StoppedDate_output;?>
                    <div class="ordering">
                        <div class="fas fa-caret-up" <?php if($order_field == "stopped_date" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
                        <div class="fas fa-caret-down" <?php if($order_field == "stopped_date" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
                    </div>
                </div>
            <?php } ?>
			<?php if(($list_filter == "collecting" || $list_filter == "warning" || $list_filter == "company_fee_notpaid") && ($sublist_filter == "notStarted" || $sublist_filter == "canSendNow")) { ?>
	        	<div class="gtable_cell gtable_cell_head c1"><input id="select_all_cases" type="checkbox" class="select_all" value="1" autocomplete=""/> <label for="select_all_cases"><?php echo $formText_SelectAll_output;?></label></div>
				<div class="gtable_cell gtable_cell_head c1"><input id="select_all_confirm" type="checkbox" class="select_all_confirm" value="1" autocomplete=""/> <label for="select_all_confirm"><?php echo $formText_SelectAll_output;?></label></div>
			<?php } ?>
			
			<?php if($sublist_filter == "completed") { ?>
				<div class="gtable_cell gtable_cell_head c1"><input id="select_all_cases" type="checkbox" class="select_all" value="1" autocomplete=""/> <label for="select_all_cases"><?php echo $formText_SelectAll_output;?></label></div>
				
			<?php } ?>
	        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_CaseNumber_output;?></div>
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_output;?></div>
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_output;?></div>
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_DueDate_output;?></div>
	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_MainClaim_output;?></div>
			<?php if($list_filter != "all") { ?>
	        	<div class="gtable_cell gtable_cell_head"><?php echo $formText_Balance_output;?></div>
			<?php } ?>

            <?php if($list_filter == "warning_closed" || $list_filter == "collecting_closed") { ?>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Checksum_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Status_output;?></div>
			<?php } else { ?>
				<?php if($list_filter != "all") { ?>
		        	<div class="gtable_cell gtable_cell_head"><?php echo $formText_WillBeSentNow_output;?></div>
				<?php } ?>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Status_output;?></div>
			<?php } ?>
	    </div>
<?php } ?>
    <?php
	$processedCustomerList = array();
	if($variables->loggID == "byamba@dcode.no"){
		var_dump(microtime()." list start");
	}
	foreach($customerList as $v_row) {
		if(!$initialCall){
			$s_sql = "SELECT collecting_company_cases_claim_lines.*, collecting_cases_claim_line_type_basisconfig.make_to_appear_in_consider_tab FROM collecting_company_cases_claim_lines
			JOIN collecting_cases_claim_line_type_basisconfig ON collecting_cases_claim_line_type_basisconfig.id = collecting_company_cases_claim_lines.claim_type
			WHERE collecting_company_cases_claim_lines.content_status < 2
			AND collecting_company_cases_claim_lines.collecting_company_case_id = ?  AND IFNULL(collecting_cases_claim_line_type_basisconfig.not_include_in_claim, 0) = 0
			ORDER BY collecting_company_cases_claim_lines.claim_type ASC, collecting_company_cases_claim_lines.created DESC";
			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
			$claims = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
			$payments = ($o_query ? $o_query->result_array() : array());

			$balance = 0;
			$checksum = 0;
			$ledgerChecksum = 0;
			$forgivenChecksum = 0;

			$hasConsideration = false;
			foreach($claims as $claim) {
				if(!$claim['payment_after_closed']) {
					$balance += $claim['amount'];
					$checksum += $claim['amount'];
				}
				if($claim['claim_type'] == 1 || $claim['claim_type'] == 16 || ($claim['claim_type'] == 15 && !$claim['payment_after_closed'])){
					$forgivenChecksum += $claim['amount'];
				}
				if($claim['make_to_appear_in_consider_tab']) {
					$hasConsideration = true;
				}
			}

			foreach($payments as $payment) {
				$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE cmt.bookaccount_id = '1' AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$transactions = ($o_query ? $o_query->result_array() : array());
				foreach($transactions as $transaction){
					$balance -= $transaction['amount'];
					$checksum -= $transaction['amount'];
					$ledgerChecksum+= $transaction['amount'];
				}
				$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '15' OR cmt.bookaccount_id = '16' OR cmt.bookaccount_id = '22') AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$ledger_transactions = ($o_query ? $o_query->result_array() : array());
				foreach($ledger_transactions as $transaction){
					$ledgerChecksum+= $transaction['amount'];
				}
				$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '20' OR cmt.bookaccount_id = 19) AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$ledger_transactions = ($o_query ? $o_query->result_array() : array());
				foreach($ledger_transactions as $transaction){
					$forgivenChecksum+= $transaction['amount'];
				}
			}
			$forgivenChecksum -= $v_row['forgivenAmountOnMainClaim'];
			$checksum -= $v_row['forgivenAmountOnMainClaim'];
			$checksum -= $v_row['forgivenAmountExceptMainClaim'];
			$checksum += $v_row['overpaidAmount'];
		}
		$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? ORDER BY created_date DESC";
		$o_query = $o_main->db->query($s_sql, array($v_row['id']));
		$stops = ($o_query ? $o_query->result_array() : array());

		$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($v_row['id']));
		$notes = ($o_query ? $o_query->result_array() : array());

		$v_row['notes'] = $notes;
		$v_row['stops'] = $stops;
		$v_row['hasConsideration'] = $hasConsideration;
		$v_row['claims'] = $claims;
		$v_row['payments'] = $payments;
		$v_row['balance'] = $balance;
		$v_row['checksum'] = $checksum;
		$v_row['ledgerChecksum'] = $ledgerChecksum;
		$v_row['forgivenChecksum'] = $forgivenChecksum;
		if(!$show_not_zero_filter || ($show_not_zero_filter AND (number_format($checksum, 2, ".", "") != "0.00" || number_format($ledgerChecksum, 2, ".", "")  != "0.00" || number_format($forgivenChecksum, 2, ".", "")  != "0.00"))){
			$processedCustomerList[] = $v_row;
		}
	}
	if($variables->loggID == "byamba@dcode.no"){
		var_dump(microtime()." list end");
	}
	// if($variables->loggID=="byamba@dcode.no"){
	// 	$casesCount = 0;
	// 	$s_sql = "SELECT * FROM collecting_company_cases  WHERE IFNULL(case_closed_date, '0000-00-00') <> '0000-00-00'";
	// 	$o_query = $o_main->db->query($s_sql);
	// 	$cases = ($o_query ? $o_query->result_array() : array());
	// 	foreach($cases as $case) {
	// 		$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? AND payment_after_closed = 1 ORDER BY claim_type ASC, created DESC";
	// 		$o_query = $o_main->db->query($s_sql, array($case['id']));
	// 		$claims = ($o_query ? $o_query->result_array() : array());
	// 		if(count($claims) == 1) {
	// 			$amount = 0;
	// 			foreach($claims as $claim){
	// 				$amount = $claim['amount'];
	// 			}
	// 			$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
	// 			$o_query = $o_main->db->query($s_sql, array($case['id']));
	// 			$payments = ($o_query ? $o_query->result_array() : array());
	// 			if(count($payments) == 1){
	// 				foreach($payments as $payment) {
	// 					$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id NOT IN (15,16,22)";
	// 					$o_query = $o_main->db->query($s_sql);
	// 					$cs_transactions = ($o_query ? $o_query->result_array() : array());
	// 					$missingAmount = 0;
	// 					foreach($cs_transactions as $cs_transaction) {
	// 						$missingAmount += $cs_transaction['amount'];
	// 					}
	// 					if(abs($missingAmount) == abs($amount)){
	// 						$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$o_main->db->escape_str(20)."'";
	// 						$o_query = $o_main->db->query($s_sql);
	// 						$cs_transaction = ($o_query ? $o_query->row_array() : array());
	// 						if(!$cs_transaction){
	// 							$casesCount++;
	// 							$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = 'script',
	// 							cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."',
	// 							amount = '".$o_main->db->escape_str(str_replace(",",".",$amount))."',
	// 							bookaccount_id = '".$o_main->db->escape_str(20)."'";
	// 							$o_query = $o_main->db->query($s_sql);
	// 						}
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// 	var_dump($casesCount);
	// }
	// if($variables->loggID=="byamba@dcode.no"){
	// 	$customerIds = array();
	// 	foreach($processedCustomerList as $customer) {
	// 		$claims = $customer['claims'];
	// 		$hasDirectPayment = false;
	// 		if($customer['id'] == '100603' || 1==1){
	// 			$amount = 0;
	// 			foreach($claims as $claim){
	// 				if($claim['claim_type'] == 15) {
	// 					$s_sql = "UPDATE collecting_company_cases_claim_lines SET payment_after_closed = 1, updatedBy='script' WHERE id = '".$o_main->db->escape_str($claim['id'])."'";
	// 					$o_query = $o_main->db->query($s_sql);
	// 					if($o_query){
	// 						$amount = $claim['amount'];
	// 						$hasDirectPayment = true;
	// 					}
	// 				}
	// 			}
	// 			if($hasDirectPayment && $amount < 0) {
	// 				$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY claim_type ASC, created DESC";
	// 				$o_query = $o_main->db->query($s_sql, array($customer['id']));
	// 				$claims = ($o_query ? $o_query->result_array() : array());
	//
	// 				$totalSumPaid = 0;
	// 				$totalSumDue = 0;
	//
	// 				$forgivenAmountOnMainClaim = 0;
	// 				$forgivenAmountExceptMainClaim = 0;
	// 				$totalMainClaim = 0;
	// 				$totalClaim = 0;
	// 				foreach($claims as $claim) {
	// 					if(!$claim['payment_after_closed'] || $claim['claim_type'] != 15) {
	// 						if($claim['claim_type'] == 1 || $claim['claim_type'] == 15 || $claim['claim_type'] == 16){
	// 							$totalMainClaim += $claim['amount'];
	// 						}
	// 						$totalClaim += $claim['amount'];
	// 					}
	// 				}
	// 				if($totalMainClaim < 0){
	// 					$totalMainClaim = 0;
	// 				}
	// 				$totalPaymentForMain = 0;
	// 				$totalPayment = 0;
	// 				$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt
	// 				LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
	// 				WHERE cmv.case_id = ? AND cmt.bookaccount_id = 1 ORDER BY cmv.created DESC";
	// 				$o_query = $o_main->db->query($s_sql, array($customer['id']));
	// 				$transactions = ($o_query ? $o_query->result_array() : array());
	// 				foreach($transactions as $transaction) {
	// 					$totalPayment += $transaction['amount'];
	// 				}
	//
	// 				$overpaidAmount = $totalClaim - $totalPayment;
	// 				if($overpaidAmount < 0){
	// 					$overpaidAmount = abs($overpaidAmount);
	// 				} else {
	// 					$overpaidAmount = 0;
	// 				}
	// 				if($totalClaim > $totalPayment) {
	// 					if($totalMainClaim > $totalPayment) {
	// 						$forgivenAmountOnMainClaim = $totalMainClaim - $totalPayment;
	// 						$forgivenAmountExceptMainClaim = $totalClaim - $totalMainClaim;
	// 					} else {
	// 						$forgivenAmountOnMainClaim = 0;
	// 						$forgivenAmountExceptMainClaim = $totalClaim - $totalPayment;
	// 					}
	// 					if($forgivenAmountExceptMainClaim < 0) {
	// 						$forgivenAmountExceptMainClaim = 0;
	// 					}
	// 					if($forgivenAmountOnMainClaim < 0) {
	// 						$forgivenAmountOnMainClaim = 0;
	// 					}
	// 				}
	// 				$sql = "UPDATE collecting_company_cases SET
	// 				updated = now(),
	// 				updatedBy='script',
	// 				forgivenAmountOnMainClaim = ?,
	// 				forgivenAmountExceptMainClaim = ?,
	// 				overpaidAmount = ?
	// 				WHERE id = ?";
	// 				$o_query = $o_main->db->query($sql, array($forgivenAmountOnMainClaim, $forgivenAmountExceptMainClaim, $overpaidAmount, $customer['id']));
	//
	// 				$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
	// 				$o_query = $o_main->db->query($s_sql, array($customer['id']));
	// 				$payments = ($o_query ? $o_query->result_array() : array());
	// 				foreach($payments as $payment) {
	// 					$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id NOT IN (15,16,22)";
	// 					$o_query = $o_main->db->query($s_sql);
	// 					$cs_transactions = ($o_query ? $o_query->result_array() : array());
	//
	// 					$missingAmount = 0;
	// 					foreach($cs_transactions as $cs_transaction) {
	// 						$missingAmount += $cs_transaction['amount'];
	// 					}
	// 					if(abs($missingAmount) == abs($amount)) {
	// 						$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = '".$o_main->db->escape_str(20)."'";
	// 						$o_query = $o_main->db->query($s_sql);
	// 						$cs_transaction = ($o_query ? $o_query->row_array() : array());
	// 						if(!$cs_transaction){
	// 							$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = 'script',
	// 							cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."',
	// 							amount = '".$o_main->db->escape_str(str_replace(",",".",$amount))."',
	// 							collecting_claim_line_type = '0',
	// 							bookaccount_id = '".$o_main->db->escape_str(20)."'";
	// 							$o_query = $o_main->db->query($s_sql);
	// 						}
	// 					}
	// 				}
	//
	// 				$customerIds[] = $customer['id'];
	// 			}
	// 		}
	// 	}
	// 	var_dump($customerIds);
	// }
	$type_messages = array($formText_ReturnedLetters_Output, $formText_PausedByCollectingCompany_output, $formText_PausedByCreditor_output, $formText_HasAnObjection_output,$formText_WantsInvoiceCopy_output,$formText_WantsInstallmentPayment_output,$formText_WantsDefermentOfPayment_output);

    foreach($processedCustomerList as $v_row) {
        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];

		$mainClaim = $v_row['original_main_claim'];
		$checksum = $v_row['checksum'];
		$balance = $v_row['balance'];
		$ledgerChecksum = $v_row['ledgerChecksum'];
		$forgivenChecksum = $v_row['forgivenChecksum'];
		$claims = $v_row['claims'];
		$payments = $v_row['payments'];
		$notes = $v_row['notes'];
		$stops = $v_row['stops'];
		$hasConsideration = $v_row['hasConsideration'];

		$is_company = false;
		$s_sql = "SELECT * FROM customer WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_row['debitor_id']));
		$debitor = ($o_query ? $o_query->row_array() : array());
		if($debitor['customer_type_for_collecting_cases'] == 0) {
		   $customer_type_collect_debitor = $debitor['customer_type_collect'];
		   if($debitor['customer_type_collect_addition'] > 0) {
			   $customer_type_collect_debitor = $debitor['customer_type_collect_addition'] - 1;
		   }
		   if($customer_type_collect_debitor == 0) {
			   $is_company = true;
		   }
		} else if($debitor['customer_type_for_collecting_cases'] == 1) {
			$is_company = true;
		}
		$isRedHover = false;
		if(count($payments) > 0) {
			$isRedHover = true;
		}
		foreach($claims as $claim){
			if($claim['claim_type'] == 15){
				$isRedHover = true;
			}
		}
		$errorWithCase = false;
		$case_error_msg = "";
		if($sublist_filter == "notStarted" || $sublist_filter == "canSendNow"){
			foreach($claims as $claim){
				if($claim['claim_type'] == 1 && !$claim['invoice_closed_allow_processing_anyway']) {
					$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND collecting_company_case_id = ?";
					$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id'], $v_row['id']));
					$invoice = ($o_query ? $o_query->row_array() : array());
					if(!$invoice['open']) {
						$case_error_msg = $formText_InvoiceIsClosedInAccountingSystem_output;
						$errorWithCase = true;
					}
				}
			}
			if($v_row['continuing_process_step_id'] > 0) {
				if($v_row['appear_in_legal_step_handling']) {
					$case_error_msg = $formText_InLegalHandlingStep_output;
					$errorWithCase = true;
				}
				if($v_row['appear_in_call_debitor_step_handling']) {
					$case_error_msg = $formText_InDebitorCallHandlingStep_output;
					$errorWithCase = true;
				}
			}
		}
		?>
        <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
        <?php
      	// Show default columns
      	 ?>
             <?php if($list_filter == 2 || $list_filter == 4) { ?>
                 <div class="gtable_cell">
                     <?php
                     echo date("d.m.Y H:i:s", strtotime($v_row['stopped_date']));
                     ?>
                 </div>
             <?php } ?>
			 <?php if(($list_filter == "collecting" || $list_filter == "warning" || $list_filter == "company_fee_notpaid") && ($sublist_filter == "notStarted" || $sublist_filter == "canSendNow")) { ?>
 	        	<div class="gtable_cell c1"><?php if(!$errorWithCase) {?><input class="cases_to_process" type="checkbox" name="process_case[]" value="<?php echo $v_row['id'];?>" autocomplete=""/><?php } else {
					echo $case_error_msg;
				} ?></div>
				<div class="gtable_cell c1"><?php if($is_company && ($debitor['confirmed_as_company'] == "0000-00-00" || $debitor['confirmed_as_company'] == "")) { ?><input class="cases_to_confirm" type="checkbox" name="process_case_confirm[]" value="<?php echo $v_row['id'];?>" autocomplete=""/><?php } ?></div>
			<?php } ?>
			<?php if($sublist_filter == "completed") { ?>
				<div class="gtable_cell c1"><?php if(!$errorWithCase) {?><input class="cases_to_process" type="checkbox" name="process_case[]" value="<?php echo $v_row['id'];?>" autocomplete=""/><?php } else {
					echo $case_error_msg;
				} ?></div>
			<?php } ?>
	        <div class="gtable_cell c1">
				<?php echo $v_row['id']; ?>
				<div class="additional_note_info">
				<?php
				if(count($notes) > 0){
					?>
					<span class="hoverEye inherit">
						<?php echo $formText_Notes_output." (".count($notes).")<br/>";?>
						<div class="hoverInfo hoverInfo2 hoverInfoFull ">
							<table class="table smallTable">
								<?php foreach($notes as $note) { ?>
									<tr>
										<td><?php echo date("d.m.Y H:i", strtotime($note['created']));?> | <?php echo $note['createdBy'];?></td>
										<td><?php echo $note['text'];?></td>
										<td><?php
										$files = json_decode($note['files']);

										foreach($files as $file) {
											$fileParts = explode('/',$file[1][0]);
											$fileName = array_pop($fileParts);
											$fileParts[] = rawurlencode($fileName);
											$filePath = implode('/',$fileParts);
											$fileUrl = $extradomaindirroot."/../".$file[1][0];
											$fileName = $file[0];
											if(strpos($file[1][0],'uploads/protected/')!==false)
											{
												$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_notesandfiles&field=files&ID='.$note['id'];
											}
										}
										?></td>
									</tr>
								<?php } ?>
							</table>
						</div>
					</span>
					<?php
				}
				if(count($stops) > 0){
					?>
					<span class="hoverEye inherit">
						<?php echo $formText_Stops_output." (".count($stops).")<br/>";?>
						<div class="hoverInfo hoverInfo2 hoverInfoFull ">
							<table class="table smallTable">
								<?php foreach($stops as $stop) { ?>
									<tr>
										<td><?php echo date("d.m.Y H:i", strtotime($stop['created']));?> | <?php echo $stop['createdBy'];?></td>
										<td><?php echo $stop['pause_reason_comment'];?></td>
										<td><?php echo $type_messages[$stop['pause_reason']];?></td>
										<td><?php
										if($stop['closed_date'] != "0000-00-00 00:00:00" && $stop['closed_date'] != "") {
											echo date("d.m.Y", strtotime($stop['closed_date']));
										}
										?></td>
										<td><?php
										if($stop['closed_date'] != "0000-00-00 00:00:00" && $stop['closed_date'] != "") {
											echo nl2br($stop['closed_comment']);
										}
										?></td>
									</tr>
								<?php } ?>
							</table>
						</div>
					</span>
					<?php
				}
				?></div>
				<?php
				if($debitor['customer_type_for_collecting_cases'] == 0) {
				   echo $formText_UseCrmCustomerType_output;
				   if($is_company) {
					   echo " (".$formText_Company_output.")";
				   } else {
					   echo " (".$formText_Person_output.")";
				   }
			   	} else {
					if($is_company) {
				   		echo $formText_Company_output;
				   	} else {
						echo $formText_Person_output;
				   	}
			   	}

				if($v_row['without_fee_paid']) {
					echo "<br/>".$formText_WithoutFeePaid_output;
				}
				if($v_row['without_fee_notpaid']) {
					echo "<br/>".$formText_WithoutFeeNotPaid_output;
				}
				if($hasConsideration){
					echo "<br/><span style='color: red;'>".$formText_HasClaimlineToConsider_output."</span>";
				}
				?>
			</div>
	        <div class="gtable_cell"><?php echo $v_row['debitorName'];?><br/><?php echo $v_row['debitorCountry'];?>
				<br/><span style="color: red;"><?php echo $v_row['currency_name'];?></span>
				<?php if($v_row['extra_language'] == 1) {?>
					<div style="color: red;"><?php echo $formText_English_Output;?></div>
				<?php } ?>
			</div>
	        <div class="gtable_cell"><?php echo $v_row['creditorName'];?></div>
	        <div class="gtable_cell"><?php if($v_row['due_date'] != "0000-00-00" && $v_row['due_date'] != ""){ echo date("d.m.Y", strtotime($v_row['due_date'])); }?></div>
	        <div class="gtable_cell rightAlign"><?php echo number_format($mainClaim, 2, ",", " ");?></div>
	        
			<?php if($list_filter != "all") { ?>
				<div class="gtable_cell rightAlign">
					<?php echo number_format($balance, 2, ",", " ");?>

					<span class="glyphicon glyphicon-info-sign hoverEye <?php if($isRedHover) echo 'red';?>">
						<div class="hoverInfo hoverInfo2 hoverInfoFull">
							<table class="table smallTable">
								<?php
								if(count($claims) > 0) { ?>
									<?php
									foreach($claims as $claim) {
										?>
										<tr>
											<td><b><?php echo $claim['name'];?></b></td>
											<td><?php if($claim['date'] != "0000-00-00" && $claim['date'] != "") echo date("d.m.Y", strtotime($claim['date']));?></td>
											<td><?php echo number_format($claim['amount'], 2, ",", " ");?></td>
										</tr>
										<?php
									}
								}
								if(count($payments) > 0){
								?>
									<?php
									foreach($payments as $payment) {
										?>
										<tr>
											<td><b><?php echo $formText_Payment_output;?></b></td>
											<td><?php if($payment['date'] != "0000-00-00" && $payment['date'] != "") echo date("d.m.Y", strtotime($payment['date']));?></td>
											<td><?php echo number_format($payment['amount'], 2, ",", " ");?></td>
										</tr>
										<?php
									}
								}
								?>
								<tr class="balance_row">
									<td><b><?php echo $formText_Balance_output;?></b></td>
									<td></td>
									<td><?php echo number_format($balance, 2, ",", " ");?></td>
								</tr>
							</table>
							<?php if(count($transaction_fees) > 0){ ?>
								<div class="resetTheCase" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_ResetFees_output;?></div>
							<?php } ?>
							<?php
							if(count($transaction_payments) > 0){
								?>
								<div class="createRestNote" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_CreateRestNote_output;?></div>
								<?php
							}
							?>
						</div>
					</span>
				</div>
			<?php } ?>
			<?php if($list_filter == "warning_closed" || $list_filter == "collecting_closed") { ?>
				<div class="gtable_cell"><?php
				echo number_format($checksum, 2, ",","");
				echo "<br/>".number_format($ledgerChecksum, 2, ",", "");
				echo "<br/>".number_format($forgivenChecksum, 2, ",", "");
				?></div>
				
			<?php } else if($list_filter!="all"){ ?>
		        <div class="gtable_cell">
					<?php
					
					if($v_row['continuing_process_step_id'] > 0) {
						if($v_row['nextContinuingStepDate'] != "") echo date("d.m.Y", strtotime($v_row['nextContinuingStepDate']))."<br/>";

						echo $v_row['nextContinuingStepName'];
					} else {
						if($v_row['nextStepDate'] != "") echo date("d.m.Y", strtotime($v_row['nextStepDate']))."<br/>";

						echo $v_row['nextStepName'];
					}
					?>
				</div>
			<?php } ?>
				<div class="gtable_cell">
					<?php
					
					if($v_row['continuing_process_step_id'] > 0) {						
						if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
							echo $formText_InContinuingProcess_output;
						} else {							
							echo $formText_ClosedInCollectingLevel_output;
						}
					} else {
						if($v_row['collecting_case_surveillance_date'] != '0000-00-00' && $v_row['collecting_case_surveillance_date'] != ''){
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_Surveillance_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_surveillance_date'])).")";
							} else {
								echo $formText_ClosedInSurveillance_output;
							}
						} else if($v_row['collecting_case_manual_process_date'] != '0000-00-00' && $v_row['collecting_case_manual_process_date'] != ''){
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_ManualProcess_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_manual_process_date'])).")";
							} else {
								echo $formText_ClosedInManualProcess_output;
							}
						} else if($v_row['collecting_case_created_date'] != '0000-00-00' && $v_row['collecting_case_created_date'] != ''){
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_CollectingLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_created_date'])).")";
							} else {
								echo $formText_ClosedInCollectingLevel_output;
							}
						} else if($v_row['warning_case_created_date'] != '0000-00-00' && $v_row['warning_case_created_date'] != '') {
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_WarningLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['warning_case_created_date'])).")";
							} else {
								echo $formText_ClosedInWarningLevel_output;
							}
						}						

						if(($v_row['case_closed_date'] != "0000-00-00" AND $v_row['case_closed_date'] != "")){
							if($v_row['case_closed_reason'] >= 0){
								echo "<br/>".$closed_reasons[$v_row['case_closed_reason']];
							}
						}
						if(!$initialCall){
							if($v_row['forgivenAmountOnMainClaim'] != 0) {
								echo "<br/>".$formText_ForgivenAmountOnMainClaim_output." ".number_format($v_row['forgivenAmountOnMainClaim'], 2, ",", "");
							}
							if($v_row['forgivenAmountExceptMainClaim'] != 0) {
								echo "<br/>".$formText_ForgivenAmountExceptMainClaim_output." ".number_format($v_row['forgivenAmountExceptMainClaim'], 2, ",", "");
							}
							if($v_row['overpaidAmount'] != 0) {
								echo "<br/>".$formText_OverpaidAmount_output." ".number_format($v_row['overpaidAmount'], 2, ",", "");
							}
						}
					}
					
					?>
				</div>
        </div>
    <?php } ?>
	<?php if (!$rowOnly) { ?>
		</div>
		<?php if($totalPages > 1) {
			$currentPage = $page;
			$pages = array();
			array_push($pages, 1);
			if(!in_array($currentPage, $pages)){
				array_push($pages, $currentPage);
			}
			if(!in_array($totalPages, $pages)){
				array_push($pages, $totalPages);
			}
			for ($y = 10; $y <= $totalPages; $y+=10){
				if(!in_array($y, $pages)){
					array_push($pages, $y);
				}
			}
			for($x = 1; $x <= 3;$x++){
				$prevPage = $page - $x;
				$nextPage = $page + $x;
				if($prevPage > 0){
					if(!in_array($prevPage, $pages)){
						array_push($pages, $prevPage);
					}
				}
				if($nextPage <= $totalPages){
					if(!in_array($nextPage, $pages)){
						array_push($pages, $nextPage);
					}
				}
			}echo '<!--section-->';
			asort($pages);
			?>
			<?php foreach($pages as $page) {?>
				<a href="#" data-page="<?php echo $page?>" class="page-link <?php if($page == $_GET['page']) echo 'active';?>"><?php echo $page;?></a>
			<?php } ?>
			<?php /*
		    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){ ?>
		<?php if($sublist_filter != "completed") { ?>
		<div class="process_cases"><?php echo $formText_Process_output;?> (<span class="selected_case_count">0</span>)</div>
		<div class="confirm_cases"><?php echo $formText_ConfirmCompanies_output;?></div>
		<?php } else { ?>
			<div class="continuing_process_cases"><?php echo $formText_StartContinuingProcess_output;?> (<span class="selected_case_count">0</span>)</div>
		<?php } ?>
		<?php /*?>
		<div class="process_cases_reset"><?php echo $formText_ProcessWithResettingCaseToFirstCollectingStep_output;?></div>*/?>
	</div>
	<?php } ?>
<script type="text/javascript">
	function update_counter(){
		$(".selected_case_count").html($(".cases_to_process:checked").length);
	}
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
	});
	$("#select_all_cases").off("click").on("click", function(){
		if($(this).is(":checked")){
			$(".cases_to_process").prop("checked", true);
		} else {
			$(".cases_to_process").prop("checked", false);
		}
		update_counter();
	})
	$(".cases_to_process").off("change").on("change", function(){		
		update_counter();
	})
	$(".select_all_confirm").off("click").on("click", function(){
		if($(this).is(":checked")){
			$(".cases_to_confirm").prop("checked", true);
		} else {
			$(".cases_to_confirm").prop("checked", false);
		}
	})
	$(".continuing_process_cases").off("click").on("click", function(e){		
		e.preventDefault();
		var formdata = $(".cases_to_process").serializeArray();
		var data = {};
		$(formdata).each(function(index, obj){
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
		ajaxCall('start_continuing_process_multi', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".process_cases").off("click").on("click", function(e){
		e.preventDefault();
			var formdata = $(".cases_to_process").serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
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
			ajaxCall('process_cases', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload");
				$("#popupeditbox:not(.opened)").remove();
			});
		// } else {
		// 	$('#popupeditboxcontent').html('');
		// 	$('#popupeditboxcontent').html('<?php echo $formText_SelectCasesToBeProcessed_output;?>');
		// 	out_popup = $('#popupeditbox').bPopup(out_popup_options);
		// 	$("#popupeditbox:not(.opened)").remove();
		// }
	})
	$(".confirm_cases").off("click").on("click", function(e){
		e.preventDefault();
			var formdata = $(".cases_to_confirm").serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
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
			ajaxCall('confirm_cases', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload");
				$("#popupeditbox:not(.opened)").remove();
			});
		// } else {
		// 	$('#popupeditboxcontent').html('');
		// 	$('#popupeditboxcontent').html('<?php echo $formText_SelectCasesToBeProcessed_output;?>');
		// 	out_popup = $('#popupeditbox').bPopup(out_popup_options);
		// 	$("#popupeditbox:not(.opened)").remove();
		// }
	})

	$(".process_cases_reset").off("click").on("click", function(e){
		e.preventDefault();
		var formdata = $(".cases_to_process").serializeArray();
		var data = {};
		$(formdata).each(function(index, obj){
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
		bootbox.confirm('<?php echo $formText_ProcessingCasesWillResetCurrentProcessStepAndSetItToFirstCollectingStep_output; ?>', function(result) {
			if (result) {
				ajaxCall('reprocess_cases_from_collecting_step', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					out_popup.addClass("close-reload");
					$("#popupeditbox:not(.opened)").remove();
				});
			}
		});

	})
    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            debitor_type_filter: '<?php echo $debitor_type_filter;?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        page: page
	    };
        loadView("list", data);
	    // ajaxCall('list', data, function(json) {
	    //     $('.p_pageContent').html(json.html);
	    //     if(json.html.replace(" ", "") == ""){
	    //         $(".showMoreCustomersBtn").hide();
	    //     }
		//
	    // });
    });
    $('.showMoreCustomersBtn').on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            page: page,
            rowOnly: 1
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == ""){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });
    $(".orderBy").off("click").on("click", function(){
        var order_field = $(this).data("orderfield");
        var order_direction = $(this).data("orderdirection");

        var data = {
            list_filter: '<?php echo $list_filter; ?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            order_field: order_field,
            order_direction: order_direction
        }
        loadView("list", data);
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
	 );
</script>
<style>
.process_cases {
	position: fixed;
	bottom: 20px;
	right: 20%;
	z-index: 10;
	float: right;
	color: #fff;
	padding: 10px 35px;
	font-size: 14px;
	background: #0b9b32;
	border-radius: 3px;
	text-align: center;
	cursor: pointer;
	font-weight: bold;
	margin-top: 10px;
}
.process_cases_reset {
	position: fixed;
	bottom: 70px;
	right: 20%;
	z-index: 10;
	float: right;
	color: #fff;
	padding: 10px 35px;
	font-size: 14px;
	background: #0b9b32;
	border-radius: 3px;
	text-align: center;
	cursor: pointer;
	font-weight: bold;
	margin-top: 10px;
}
.continuing_process_cases {
	position: fixed;
	bottom: 20px;
	right: 20%;
	z-index: 10;
	float: right;
	color: #fff;
	padding: 10px 35px;
	font-size: 14px;
	background: #0b9b32;
	border-radius: 3px;
	text-align: center;
	cursor: pointer;
	font-weight: bold;
	margin-top: 10px;

}
.confirm_cases {
	position: fixed;
	bottom: 70px;
	right: 20%;
	z-index: 10;
	float: right;
	color: #fff;
	padding: 10px 35px;
	font-size: 14px;
	background: #0b9b32;
	border-radius: 3px;
	text-align: center;
	cursor: pointer;
	font-weight: bold;
	margin-top: 10px;
}
.gtable_cell {
	position: relative;
}
.hoverEye {
	position: relative;
	color: #0284C9;
	margin-top: 2px;
}
.hoverEye.red {
	color: red;
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
	width: 400px;
}
.hoverEye .hoverInfo3 {
	width: 300px;
}
.hoverEye .hoverInfoSmall {
	width: 200px;
}
.hoverEye.hover .hoverInfo {
	display: block;
}
.hoverEye.inherit {
	color: inherit;
}
.additional_note_info {
	color: orange;
}
.additional_note_info .hoverEye {
	display: inline-block;
	width: 100%;
}
.page-link.active {
	font-weight: bold;
	text-decoration: underline;
}
</style>
<?php } ?>
