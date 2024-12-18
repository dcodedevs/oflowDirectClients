<?php
session_start();
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once BASEPATH . 'elementsGlobal/cMain.php';


ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);
include(__DIR__."/readOutputLanguage.php");


$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : "";
$mainlist_filter = $_GET['mainlist_filter'] ? ($_GET['mainlist_filter']) : "";
$sublist_filter = $_GET['sublist_filter'] ? ($_GET['sublist_filter']) : "";

$sql_join = " ";
$sql_init = "SELECT p.*, cred.companyname as creditorName, c2.name as debitorName,
	DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
		IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType, step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId,
		cwc.added_to_worklist_date, cwc.closed_date, cwc.reminder_date
		 FROM collecting_company_cases p
		 LEFT JOIN creditor cred ON cred.id = p.creditor_id
		 JOIN case_worklist_connection cwc ON cwc.collecting_company_case_id = p.id
		 LEFT JOIN customer c2 ON c2.id = p.debitor_id
		 LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = p.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = p.collecting_process_id
		 LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = p.collecting_process_id
		".$sql_join."
		WHERE p.content_status < 2 AND DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0) DAY) <= CURDATE() AND IFNULL(cwc.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";


$sql_init_2 = "SELECT p.*, cred.companyname as creditorName, c2.name as debitorName,
	DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
		IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType, step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId,
		cccp.closed_date, cccp.id as objectionId,  cccp.message_handled_by, cccp.message_handled
		 FROM collecting_company_cases p
		 LEFT JOIN creditor cred ON cred.id = p.creditor_id
		 JOIN collecting_company_case_paused cccp ON cccp.collecting_company_case_id = p.id
		 LEFT JOIN customer c2 ON c2.id = p.debitor_id
		 LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = p.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = p.collecting_process_id
		 LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = p.collecting_process_id
		WHERE p.content_status < 2";

if($mainlist_filter == "worklist"){
	$sql_where = " AND cwc.case_worklist_id = ".$o_main->db->escape($list_filter);
	$sql = $sql_init.$sql_where;
    $o_query = $o_main->db->query($sql);
	$customerList = $o_query ? $o_query->result_array() :array();
} else if($mainlist_filter == "paused") {	
	$group_by = "";
	$sql_where = " AND cccp.pause_reason = ".$o_main->db->escape($list_filter);
	if($objection_status == 0){
		$sql_where .= " AND IFNULL(cccp.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";
	} else if($objection_status == 1){
		$sql_where .= " AND IFNULL(cccp.closed_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
	} else {
		$sql_where .= " AND cccp.created_date >= '".date("Y-m-d", strtotime($dateFrom))."' AND cccp.created_date <= '".date("Y-m-d", strtotime($dateTo))."'";
		if($creditor_filter){
			$sql_where .= " AND cred.id = '".$o_main->db->escape_str($creditor_filter['id'])."'";
		}
	}
	$sql = $sql_init_2.$sql_where.$group_by;
    $o_query = $o_main->db->query($sql);
	$customerList = $o_query ? $o_query->result_array() :array();
} else if($mainlist_filter == "incoming_messages") {
	$sql_where = " AND cccp.incoming_from_portal = 1";
	$sql_order = " ORDER BY cccp.created DESC";
	if($list_filter == "handled"){
		$sql_where .= " AND IFNULL(cccp.message_handled, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
		$sql_order = " ORDER BY cccp.message_handled DESC";
	} else {
		$sql_where .= " AND IFNULL(cccp.message_handled, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";
	}
	$o_query = $o_main->db->query($sql_init_2.$sql_where.$sql_order);
	$customerList = $o_query ? $o_query->result_array() :array();
}

$s_sql = "SELECT cccl.id, cccl.payment_after_closed, cccl.amount, cccl.claim_type, cccl.collecting_company_case_id FROM collecting_company_cases_claim_lines cccl
JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
WHERE cccl.content_status < 2 AND IFNULL(bconfig.not_include_in_claim, 0) = 0
ORDER BY cccl.claim_type ASC, cccl.created DESC";
$o_query = $o_main->db->query($s_sql);
$all_claims = ($o_query ? $o_query->result_array() : array());
$filtered_all_claims = array();
foreach($all_claims as $all_claim){
	$filtered_all_claims[$all_claim['collecting_company_case_id']][] = $all_claim;
} 

$s_sql = "SELECT cmt.id, cmt.amount, cmv.case_id FROM cs_mainbook_transaction cmt
JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
WHERE cmt.bookaccount_id = 1 ORDER BY cmt.created DESC";
$o_query = $o_main->db->query($s_sql, array($v_row['id']));
$all_payments = ($o_query ? $o_query->result_array() : array());

$filtered_all_payments = array();
foreach($all_payments as $all_payment){
	$filtered_all_payments[$all_payment['case_id']][] = $all_payment;
} 

$processedCustomerList = array();
foreach($customerList as $v_row) {
	$claims = $filtered_all_claims[$v_row['id']];
	$payments = $filtered_all_payments[$v_row['id']];

	// $s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
	// LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
	// WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
	// ORDER BY cccl.claim_type ASC, cccl.created DESC";
	// $o_query = $o_main->db->query($s_sql, array($v_row['id']));
	// $claims = ($o_query ? $o_query->result_array() : array());

	// $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
	// $o_query = $o_main->db->query($s_sql, array($v_row['id']));
	// $payments = ($o_query ? $o_query->result_array() : array());

	$balance = 0;
	$checksum = 0;
	$ledgerChecksum = 0;
	$forgivenChecksum = 0;

	foreach($claims as $claim) {
		if(!$claim['payment_after_closed']) {
			$balance += $claim['amount'];
			$checksum += $claim['amount'];
		}
		if($claim['claim_type'] == 1 || $claim['claim_type'] == 16 || ($claim['claim_type'] == 15 && !$claim['payment_after_closed'])){
			$forgivenChecksum += $claim['amount'];
		}
	}

	foreach($payments as $transaction){
		$balance -= $transaction['amount'];
		$checksum -= $transaction['amount'];
		$ledgerChecksum+= $transaction['amount'];
	}

	// foreach($payments as $payment) {
	// 	$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE cmt.bookaccount_id = '1' AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
	// 	$o_query = $o_main->db->query($s_sql);
	// 	$transactions = ($o_query ? $o_query->result_array() : array());
	// 	foreach($transactions as $transaction){
	// 		$balance -= $transaction['amount'];
	// 		$checksum -= $transaction['amount'];
	// 		$ledgerChecksum+= $transaction['amount'];
	// 	}
	// 	$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '15' OR cmt.bookaccount_id = '16' OR cmt.bookaccount_id = '22') AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
	// 	$o_query = $o_main->db->query($s_sql);
	// 	$ledger_transactions = ($o_query ? $o_query->result_array() : array());
	// 	foreach($ledger_transactions as $transaction){
	// 		$ledgerChecksum+= $transaction['amount'];
	// 	}
	// 	$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '20' OR cmt.bookaccount_id = 19) AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
	// 	$o_query = $o_main->db->query($s_sql);
	// 	$ledger_transactions = ($o_query ? $o_query->result_array() : array());
	// 	foreach($ledger_transactions as $transaction){
	// 		$forgivenChecksum+= $transaction['amount'];
	// 	}
	// }
	// $forgivenChecksum -= $v_row['forgivenAmountOnMainClaim'];
	// $checksum -= $v_row['forgivenAmountOnMainClaim'];
	// $checksum -= $v_row['forgivenAmountExceptMainClaim'];
	// $checksum += $v_row['overpaidAmount'];

	$v_row['balance'] = $balance;

	$processedCustomerList[] = $v_row;

	// $v_row['claims'] = $claims;
	// if(!$show_not_zero_filter || ($show_not_zero_filter AND (number_format($checksum, 2, ".", "") != "0.00" || number_format($ledgerChecksum, 2, ".", "")  != "0.00" || number_format($forgivenChecksum, 2, ".", "")  != "0.00"))){
		
	// }
}

/** Include PHPExcel */
require_once __DIR__ . '/PHPExcel/PHPExcel.php';

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);


$objPHPExcel->setActiveSheetIndex(0);
$row = 1;
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $formText_CaseId_text);
$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $formText_DebitorName_text);
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $formText_CreditorName_text);
$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $formText_DueDate_text);
$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $formText_MainClaim_text);
$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $formText_Balance_text);
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $formText_WillBeSentNow_text);
$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $formText_Status_text);

foreach($processedCustomerList as $v_row) {
	//$sql = "SELECT * FROM customer WHERE id = ?";
//	$o_query = $o_main->db->query($sql, array($contactPerson['customerId']));
//	$customer = $o_query ? $o_query->row_array(): array();
	$first_claim = array();
	foreach($v_row['claims'] as $claim)
	{
		$first_claim = $claim;
		break;
	}
	$mainClaim = $v_row['original_main_claim'];
	$balance = $v_row['balance'];

	$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;	
	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $v_row['id']);
	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $v_row['debitorName']);
	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $v_row['creditorName']);
	$date = "";
	if($v_row['due_date'] != "0000-00-00" && $v_row['due_date'] != ""){ $date = date("d.m.Y", strtotime($v_row['due_date'])); }
	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $date);
	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, number_format($mainClaim, 2, ",", " "));
	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, number_format($balance, 2, ",", " "));

	$willBeSentRow = '';
	if($v_row['nextStepDate'] != "") $willBeSentRow .= date("d.m.Y", strtotime($v_row['nextStepDate']));
	$willBeSentRow = "\n".$v_row['nextStepName'];
	$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $willBeSentRow);
	$objPHPExcel->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setWrapText(true);
	$statusRow = "";
	if($v_row['collecting_case_surveillance_date'] != '0000-00-00' && $v_row['collecting_case_surveillance_date'] != ''){
		if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
			$statusRow = $formText_Surveillance_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_surveillance_date'])).")";
		} else {
			$statusRow =  $formText_ClosedInSurveillance_output;
		}
	} else if($v_row['collecting_case_manual_process_date'] != '0000-00-00' && $v_row['collecting_case_manual_process_date'] != ''){
		if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
			$statusRow =  $formText_ManualProcess_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_manual_process_date'])).")";
		} else {
			$statusRow =  $formText_ClosedInManualProcess_output;
		}
	} else if($v_row['collecting_case_created_date'] != '0000-00-00' && $v_row['collecting_case_created_date'] != ''){
		if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
			$statusRow =  $formText_CollectingLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_created_date'])).")";
		} else {
			$statusRow =  $formText_ClosedInCollectingLevel_output;
		}
	} else if($v_row['warning_case_created_date'] != '0000-00-00' && $v_row['warning_case_created_date'] != '') {
		if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
			$statusRow =  $formText_WarningLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['warning_case_created_date'])).")";
		} else {
			$statusRow =  $formText_ClosedInWarningLevel_output;
		}
	}

	if(($v_row['case_closed_date'] != "0000-00-00" AND $v_row['case_closed_date'] != "")){
		if($v_row['case_closed_reason'] >= 0){
			$statusRow .=  "\n".$closed_reasons[$v_row['case_closed_reason']];
		}
	}
	
	if($v_row['forgivenAmountOnMainClaim'] != 0) {
		$statusRow .=  "\n".$formText_ForgivenAmountOnMainClaim_output." ".number_format($v_row['forgivenAmountOnMainClaim'], 2, ",", "");
	}
	if($v_row['forgivenAmountExceptMainClaim'] != 0) {
		$statusRow .=  "\n".$formText_ForgivenAmountExceptMainClaim_output." ".number_format($v_row['forgivenAmountExceptMainClaim'], 2, ",", "");
	}
	if($v_row['overpaidAmount'] != 0) {
		$statusRow .=  "\n".$formText_OverpaidAmount_output." ".number_format($v_row['overpaidAmount'], 2, ",", "");
	}
	$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $statusRow);
	$objPHPExcel->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setWrapText(true);
	
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="export.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

$objWriter->save('php://output');
