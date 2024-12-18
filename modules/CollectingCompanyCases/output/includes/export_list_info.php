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

require_once __DIR__ . '/functions.php';

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);
include(__DIR__."/readOutputLanguage.php");


$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'warning';
$sublist_filter = $_GET['sublist_filter'] ? ($_GET['sublist_filter']) : $sublist_filter;
$filters['sublist_filter'] = $sublist_filter;
$customerList = get_customer_list($o_main, $list_filter, $filters, 0, 9999999999);

$processedCustomerList = array();
foreach($customerList as $v_row) {

	$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
	LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
	WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
	ORDER BY cccl.claim_type ASC, cccl.created DESC";
	$o_query = $o_main->db->query($s_sql, array($v_row['id']));
	$claims = ($o_query ? $o_query->result_array() : array());

	$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($v_row['id']));
	$payments = ($o_query ? $o_query->result_array() : array());

	$balance = 0;
	$checksum = 0;
	$ledgerChecksum = 0;
	$forgivenChecksum = 0;

	$main_claim = 0;
	$fees = 0;
	$interest = 0;
	$sum_of_claims = 0;
	$payment_sum = 0;

	foreach($claims as $claim) {
		if(!$claim['payment_after_closed']) {
			$balance += $claim['amount'];
			$checksum += $claim['amount'];
			$sum_of_claims+=$claim['amount'];
		}
		if($claim['claim_type'] == 1 || $claim['claim_type'] == 16 || ($claim['claim_type'] == 15 && !$claim['payment_after_closed'])){
			$forgivenChecksum += $claim['amount'];
		}
		if($claim['claim_type'] == 1){
			$main_claim += $claim['amount'];
		}
		if($claim['claim_type'] == 2){
			$fees += $claim['amount'];
		}
		if($claim['claim_type'] == 8) {
			$interest += $claim['amount'];
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
			$payment_sum+=$transaction['amount'];
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

	$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($v_row['id']));
	$notes = ($o_query ? $o_query->result_array() : array());

	$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? ORDER BY created_date DESC";
	$o_query = $o_main->db->query($s_sql, array($v_row['id']));
	$stops = ($o_query ? $o_query->result_array() : array());

	$v_row['main_claim'] = $main_claim;
	$v_row['fees'] = $fees;
	$v_row['interest'] = $interest;
	$v_row['sum_of_claims'] = $sum_of_claims;
	$v_row['payment_sum'] = $payment_sum;
	$v_row['balance'] = $balance;

	$v_row['claims'] = $claims;
	if(!$show_not_zero_filter || ($show_not_zero_filter AND (number_format($checksum, 2, ".", "") != "0.00" || number_format($ledgerChecksum, 2, ".", "")  != "0.00" || number_format($forgivenChecksum, 2, ".", "")  != "0.00"))){
		$processedCustomerList[] = $v_row;
	}
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
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $formText_DateFirstLetter_text);
$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $formText_MainClaim_text);
$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $formText_Fees_text);
$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $formText_Interest_text);
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $formText_SumOfClaims_text);
$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $formText_Payments_text);
$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $formText_Balance_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $formText_exportPaStreet_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $formText_exportPaPostalNumber_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $formText_exportPaCity_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $formText_exportPaCountry_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $formText_exportVaStreet_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $formText_exportVaPostalNumber_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $formText_exportVaCity_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $formText_exportVaCountry_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $formText_exportPhone_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $formText_exportMobile_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $formText_exportFax_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $formText_exportEmail_text);
//$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $formText_exportMembershipStatus_text);

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
	$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $v_row['id']);
	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $v_row['debitorName']);
	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, ((''!=$first_claim['created']&&'0000-00-00 00:00:00'!=$first_claim['created'])?date('d.m.Y',strtotime($first_claim['created'])):''));

	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $v_row['main_claim']);
	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $v_row['fees']);
	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $v_row['interest']);
	$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $v_row['sum_of_claims']);
	$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $v_row['payment_sum']);
	$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $v_row['balance']);

//	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $customer['paStreet']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $customer['paPostalNumber']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $customer['paCity']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $customer['paCountry']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $customer['vaStreet']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $customer['vaPostalNumber']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $customer['vaCity']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $customer['vaCountry']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $customer['phone']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $customer['mobile']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $customer['fax']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $customer['email']);
//	$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $memberStatus);
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="export.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

$objWriter->save('php://output');
