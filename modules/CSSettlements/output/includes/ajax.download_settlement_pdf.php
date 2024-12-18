<?php
set_time_limit(300);
ini_set('memory_limit', '256M');

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"settlement.pdf\"");
header("Content-type: application/pdf; charset=UTF-8");

function output_transaction_mva($pdf, $mainbook_voucher, $vat_info) {
	global $o_main;

	$pdf->MultiCell(30, 0, $mainbook_voucher['case_id'], 1, 'L', 0, 0, '', '', true, 0, true);
	$pdf->MultiCell(90, 0, $mainbook_voucher['debitor_name'], 1, 'L', 0, 0, '', '', true, 0, true);
	$pdf->MultiCell(30, 0, number_format(abs($mainbook_voucher['basis']), 2, ",", " "), 1, 'R', 0, 0, '', '', true, 0, true);
	$pdf->MultiCell(30, 0, number_format(abs($vat_info['vat']), 2, ",", " "), 1, 'R', 0, 1, '', '', true, 0, true);	

	return $pdf;
}

function output_transaction($pdf, $payment, $collecting_case, $creditor, $debitor,$collecting_case_forgiven_shown) {
	global $o_main;
	global $formText_Forgiven_output;
	global $formText_Surveillance_output;
	global $formText_Started_output;
	global $formText_ClosedInSurveillance_output;
	global $formText_ManualProcess_output;
	global $formText_ClosedInManualProcess_output;
	global $formText_CollectingLevel_output;
	global $formText_ClosedInCollectingLevel_output;
	global $formText_WarningLevel_output;
	global $formText_ClosedInWarningLevel_output;
	global $formText_CaseId_output;
	global $formText_Payed_Output;
	global $formText_Bookaccount_output;
	global $formText_ClaimlineType_output;
	global $formText_Amount_output;
	global $formText_PayedOutNow_output;
	global $formText_DrawnInThisSettlement_output;
	global $include_mva_spec;


	$payed = 0;
	$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ?";
	$o_query = $o_main->db->query($s_sql, array($payment['id']));
	$transactions = $o_query ? $o_query->result_array() : array();
	$creditor_ledger_amount = 0;
	foreach($transactions as $transaction) {
		if($transaction['bookaccount_id'] == 1){
			$payed += $transaction['amount'];
		} else if($transaction['bookaccount_id'] == 16) {
			$creditor_ledger_amount += $transaction['amount'];
		}
	}
	$forgiven = 0;
	if($collecting_case['forgivenAmountOnMainClaim'] > 0){
		$forgiven = $collecting_case['forgivenAmountOnMainClaim'];
	}
	$forgivenText = "";
	if(!isset($collecting_case_forgiven_shown[$collecting_case['id']])){
		$forgivenText = $formText_Forgiven_output.": ".$forgiven;
	}
	$case_status_text = "";

	if($collecting_case['collecting_case_surveillance_date'] != '0000-00-00' && $collecting_case['collecting_case_surveillance_date'] != ''){
		if(($collecting_case['case_closed_date'] == "0000-00-00" OR $collecting_case['case_closed_date'] == "")){
			$case_status_text = $formText_Surveillance_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($collecting_case['collecting_case_surveillance_date'])).")";
		} else {
			$case_status_text = $formText_ClosedInSurveillance_output;
		}
	} else if($collecting_case['collecting_case_manual_process_date'] != '0000-00-00' && $collecting_case['collecting_case_manual_process_date'] != ''){
		if(($collecting_case['case_closed_date'] == "0000-00-00" OR $collecting_case['case_closed_date'] == "")){
			$case_status_text = $formText_ManualProcess_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($collecting_case['collecting_case_manual_process_date'])).")";
		} else {
			$case_status_text = $formText_ClosedInManualProcess_output;
		}
	} else if($collecting_case['collecting_case_created_date'] != '0000-00-00' && $collecting_case['collecting_case_created_date'] != ''){
		if(($collecting_case['case_closed_date'] == "0000-00-00" OR $collecting_case['case_closed_date'] == "")){
			$case_status_text = $formText_CollectingLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($collecting_case['collecting_case_created_date'])).")";
		} else {
			$case_status_text = $formText_ClosedInCollectingLevel_output;
		}
	} else if($collecting_case['warning_case_created_date'] != '0000-00-00' && $collecting_case['warning_case_created_date'] != '') {
		if(($collecting_case['case_closed_date'] == "0000-00-00" OR $collecting_case['case_closed_date'] == "")){
			$case_status_text = $formText_WarningLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($collecting_case['warning_case_created_date'])).")";
		} else {
			$case_status_text = $formText_ClosedInWarningLevel_output;
		}
	}


	$tableRowHeight = $pdf->getStringHeight(30, 0, date("d.m.Y", strtotime($payment['date'])), '', 'L', 1, 0, '', '', true, 0, true);
	$tmpHeight = $pdf->getStringHeight(145, 0, $formText_CaseId_output.": ".$collecting_case['id']." (".$debitor['name']." ".$debitor['middlename']." ".$debitor['lastname'].")", '', 'L', 1, 0, '', '', true, 0, true);
	if($tmpHeight > $tableRowHeight){
		$tableRowHeight = $tmpHeight;
	}
	// $tmpHeight = $pdf->getStringHeight(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
	// if($tmpHeight > $tableRowHeight){
	// 	$tableRowHeight = $tmpHeight;
	// }
	// $tmpHeight = $pdf->getStringHeight(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
	// if($tmpHeight > $tableRowHeight){
	// 	$tableRowHeight = $tmpHeight;
	// }
	// $tmpHeight = $pdf->getStringHeight(30, 0, "", '', 'L', 1, 1, '', '', true, 0, true);
	// if($tmpHeight > $tableRowHeight){
	// 	$tableRowHeight = $tmpHeight;
	// }
	$topPosition = $pdf->GetY();
	// $pdf->MultiCell(175, 0, "", 'B', '', 1, 1, '', '', true, 0, true);
	$pdf->Line(15, $topPosition, 195, $topPosition);

	$pdf->MultiCell(30, $tableRowHeight, date("d.m.Y", strtotime($payment['date'])), '', 'L', 1, 0, '', '', true, 0, true);
	$pdf->MultiCell(145, $tableRowHeight, $formText_CaseId_output.": ".$collecting_case['id']." (".$debitor['name']." ".$debitor['middlename']." ".$debitor['lastname'].")", '', 'L', 1, 1, '', '', true, 0, true);

	$collecting_case_forgiven_shown[$collecting_case['id']] = $forgiven;

	// $pdf->MultiCell(175, 0, "", 'B', '', 1, 1, '', '', true, 0, true);
	$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

	// $pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
	// $pdf->MultiCell(50, 0, $formText_Bookaccount_output, '', 'L', 1, 0, '', '', true, 0, true);
	// $pdf->MultiCell(50, 0, $formText_Amount_output, '', 'R', 1, 1, '', '', true, 0, true);

	$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND (claim_type = 1 OR claim_type = 16 OR (claim_type = 15 AND IFNULL(payment_after_closed, 0) = 0))";
	$o_query = $o_main->db->query($s_sql, array($payment['case_id']));
	$claimlines = $o_query ? $o_query->result_array() : array();
	foreach($claimlines as $claimline){
		$pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(70, 0, $claimline['name'], '', 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(50, 0, number_format($claimline['amount'], 2, ",", ""), '', 'R', 1, 1, '', '', true, 0, true);
	}
	$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

	$totalLinesCreditor = 0;
	$totalLinesCollecting = 0;
	if($forgiven != 0) {
		$pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(70, 0, $formText_Forgiven_output, '', 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(50, 0, number_format($forgiven*-1, 2, ",", ""), '', 'R', 1, 1, '', '', true, 0, true);
	}
	$transactionsToShow = array();
	foreach($transactions as $transaction) {
		$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
		$bookaccount = $o_query ? $o_query->row_array() : array();
		if($transaction['bookaccount_id'] == 20){
			$pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
			$pdf->MultiCell(70, 0, $bookaccount['name'], '', 'L', 1, 0, '', '', true, 0, true);
			$pdf->MultiCell(50, 0, number_format($transaction['amount']*-1, 2, ",", ""), '', 'R', 1, 1, '', '', true, 0, true);
		} else if($bookaccount['summarize_on_ledger'] == 2) {			
			if($include_mva_spec || (!$include_mva_spec && $bookaccount['id'] != 27)){
				$transactionsToShow[] = $transaction;
			}
		}
	}
	foreach($transactionsToShow as $transaction) {
		$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
		$bookaccount = $o_query ? $o_query->row_array() : array();

		$pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(70, 0, $bookaccount['name'], '', 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(50, 0, number_format($transaction['amount']*-1, 2, ",", ""), '', 'R', 1, 1, '', '', true, 0, true);
	}

	foreach($transactions as $transaction) {
		if($transaction['bookaccount_id'] == 16){
			if($include_mva_spec){
				$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
				$bookaccount = $o_query ? $o_query->row_array() : array();

				$pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
				if($transaction['amount'] > 0){
					$pdf->MultiCell(70, 0, $formText_DrawnInThisSettlement_output, '', 'L', 1, 0, '', '', true, 0, true);
				} else {
					$pdf->MultiCell(70, 0, $formText_PayedOutNow_output, '', 'L', 1, 0, '', '', true, 0, true);
				}
				$pdf->MultiCell(50, 0, number_format($transaction['amount']*-1, 2, ",", ""), '', 'R', 1, 1, '', '', true, 0, true);
				break;
			}
		}
	}

	$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
	$bottomPosition = $pdf->GetY();

	$pdf->Line(15, $topPosition, 15, $bottomPosition);
	$pdf->Line(195, $topPosition, 195, $bottomPosition);
	$pdf->ln(10);
	return array($pdf, $collecting_case_forgiven_shown);
}

$creditorId = $_POST['creditorId'];
$settlementId = $_POST['settlementId'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditorId));
$creditor = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM cs_settlement WHERE id = $settlementId";
$o_query = $o_main->db->query($sql);
$settlement = $o_query ? $o_query->row_array() : array();
if($creditor && $settlement) {
	ob_start();
    include_once(__DIR__.'/../../../CollectingCases/output/includes/tcpdf/tcpdf.php');
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor("");
	$pdf->SetTitle("");
	$pdf->SetSubject("");
	$pdf->SetKeywords("");

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}
	// add a page
	$pdf->AddPage();
	$include_mva_spec = true;

    $s_sql = "SELECT * FROM ownercompany";
    $o_query = $o_main->db->query($s_sql);
    $ownercompany = ($o_query ? $o_query->row_array() : array());

	setlocale(LC_TIME, 'no_NO');
	$pdf->SetFont('calibri', '', 9);


	$pdf->SetFillColor(255, 255, 255);

	// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

	$pdf->SetLineStyle(array('width' =>0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

	$pdf->SetY(10);
	$logoImage = json_decode($ownercompany['invoicelogo']);
	if(count($logoImage) > 0){
		$imageLocation = ACCOUNT_PATH."/".$logoImage[0][1][0];
		$ext = end(explode(".", $imageLocation));
		$image = base64_encode(file_get_contents($imageLocation));
		$pdf->MultiCell(210, 0, '<img src="'.__DIR__.'/../../../../'.$logoImage[0][1][0].'" width="130" />', 0, 'C', 0, 1, 0, '', true, 0, true);
	} else {

	}
	$pdfY = $pdf->GetY();
	$pdf->MultiCell(60, 0, $formText_Date_output." ".date("d.m.Y"), 0, 'L', 0, 1, 155, '', true, 0, true);
	$pdf->MultiCell(60, 0, $formText_Page_output." ".$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 'L', 0, 1, 155, '', true, 0, true);

	$pdf->SetY($pdfY);
	$pdf->MultiCell(100, 0, $formText_SettlementId_output." ".$collectingcompany_settlement_id, 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(100, 0, $creditor['companyname'], 0, 'L', 0, 1, '', '', true, 0, true);

	$pdf->Ln(10);
	$pdf->setCellPaddings(1, 1, 1, 1);
	$totalCreditor = 0;
	$totalCollecting = 0;


	$s_sql = "SELECT cmv.*, cmv.case_id, CONCAT_WS(' ',deb.name, deb.middlename, deb.lastname) as debitorName FROM cs_mainbook_voucher cmv
	JOIN collecting_company_cases cc ON cc.id = cmv.case_id
	JOIN customer deb ON deb.id = cc.debitor_id
	WHERE IFNULL(cmv.settlement_id, 0) = ? AND cc.creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($settlement['id'], $creditor['id']));
	$payments = $o_query ? $o_query->result_array() : array();
	$total_creditor_ledger_amount = 0;
	$total_creditor_mva_amount = 0;
	foreach($payments as $payment) {
		$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ? AND bookaccount_id = 16";
		$o_query = $o_main->db->query($s_sql, array($payment['id']));
		$transactions = $o_query ? $o_query->result_array() : array();
		foreach($transactions as $transaction) {
			$total_creditor_ledger_amount += $transaction['amount']*-1;
		}
		//mva 
		$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ? AND bookaccount_id = 27";
		$o_query = $o_main->db->query($s_sql, array($payment['id']));
		$transactions = $o_query ? $o_query->result_array() : array();
		foreach($transactions as $transaction) {
			$total_creditor_mva_amount += $transaction['amount'];
		}
	}
	if($total_creditor_ledger_amount < 0){
		$include_mva_spec = false;
	}
	if(!$include_mva_spec){		
		$total_creditor_ledger_amount += $total_creditor_mva_amount;
	}
	$pdf->MultiCell(170, 0, $formText_TotalPayedOutNow_output." ".number_format($total_creditor_ledger_amount, 2, ",", ""), '', 'L', 1, 1, '', '', true, 0, true);
	$pdf->Ln(2);
    $collecting_case_forgiven_shown = array();
	foreach($payments as $payment) {

		$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($payment['case_id']));
		$collecting_case = $o_query ? $o_query->row_array() : array();

		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
		$creditor = $o_query ? $o_query->row_array() : array();

		$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
		$o_query = $o_main->db->query($s_sql, array($collecting_case['debitor_id']));
		$debitor = ($o_query ? $o_query->row_array() : array());

		$pdf->startTransaction();
		$start_page = $pdf->getPage();
		$collecting_case_forgiven_shown_before = $collecting_case_forgiven_shown;
		list($pdf, $collecting_case_forgiven_shown) = output_transaction($pdf, $payment, $collecting_case, $creditor,  $debitor,$collecting_case_forgiven_shown);
		$end_page = $pdf->getPage();

		if  ($end_page != $start_page) {
			$collecting_case_forgiven_shown = $collecting_case_forgiven_shown_before;
			$pdf->rollbackTransaction(true); // don't forget the true
			$pdf->AddPage();
			list($pdf, $collecting_case_forgiven_shown) = output_transaction($pdf, $payment, $collecting_case, $creditor, $debitor,$collecting_case_forgiven_shown);
		} else {
			$pdf->commitTransaction();
		}
	}
	if($include_mva_spec){
		$pdf->AddPage();

		setlocale(LC_TIME, 'no_NO');
		$pdf->SetFont('calibri', '', 9);
		$logoImage = json_decode($ownercompany['invoicelogo']);
		$companyNamePdf = $ownercompany['companyname'];
		$companyAddress = $ownercompany['companypostalbox'].", ".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
		$companyPhone = $ownercompany['companyphone'];
		$companyOrgNr = $ownercompany['companyorgnr'];
		$companyEmail = $ownercompany['companyEmail'];
		$companyIban = $ownercompany['companyiban'];
		$companySwift = $ownercompany['companyswift'];

		$pdf->SetY(5);
		$pdf->SetX(22);
		if(count($logoImage) > 0){
			$imageLocation = ACCOUNT_PATH."/".$logoImage[0][1][0];
			$ext = end(explode(".", $imageLocation));
			$image = base64_encode(file_get_contents($imageLocation));
			$pdf->writeHTML('<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="170" />', true, false, true, true, '');
		} else {

		}

		$pdf->SetY(20);
		$pdf->MultiCell(70, 0, $companyNamePdf, 0, 'L', 0, 1, 145, '', true, 0, true);
		$pdf->MultiCell(70, 0, $companyAddress, 0, 'L', 0, 1, 145, '', true, 0, true);
		$pdf->MultiCell(70, 0, $formText_OrgNr_pdf." ".$companyOrgNr, 0, 'L', 0, 1, 145, '', true, 0, true);
		$pdf->Ln(2);
		$pdf->MultiCell(70, 0, $formText_Phone_output." ".$companyPhone, 0, 'L', 0, 1, 145, '', true, 0, true);
		$pdf->MultiCell(70, 0, $formText_Email_output." ".$companyEmail, 0, 'L', 0, 1, 145, '', true, 0, true);
		$pdf->Ln(2);
		if($companyIban != "") {
			$pdf->MultiCell(70, 0, $formText_Iban_pdf." ".$companyIban, 0, 'L', 0, 1, 145, '', true, 0, true);
		}
		if($companySwift != "") {
			$pdf->MultiCell(70, 0, $formText_Swift_pdf." ".$companySwift, 0, 'L', 0, 1, 145, '', true, 0, true);
		}
		$pdf->Ln(2);
		$pdf->MultiCell(70, 0, $formText_Date_pdf." ".date("d.m.Y"), 0, 'L', 0, 1, 145, '', true, 0, true);

		$pdf->SetFont('calibri', '', 9);
		$pdf->SetX(20);
		$pdf->SetY(20);
		$pdf->MultiCell(80, 0, $formText_Return_output.": ". $companyNamePdf.", ".$companyAddress, 0, 'L', 0, 1, '', '', true, 0, true);

		$pdf->SetFont('calibri', '', 9);
		$pdf->SetX(20);
		$pdf->SetY(40);
		$pdf->MultiCell(100, 0, $creditor['companyname'], 0, 'L', 0, 1, '', '', true, 0, true);
		$pdf->MultiCell(100, 0, $creditor['companypostalbox'], 0, 'L', 0, 1, '', '', true, 0, true);
		$pdf->MultiCell(100, 0, $creditor['companyzipcode']." ".$creditor['companypostalplace'], 0, 'L', 0, 1, '', '', true, 0, true);

		$pdf->Ln(26);

		$pdf->SetFont('calibri', '', 12);
		$pdf->MultiCell(180, 0, $formText_VatSpecifications_output, 1, 'C', 0, 1, '', '', true, 0, true);
		$pdf->SetFont('calibri', '', 9);
		$pdf->MultiCell(30, 0, $formText_CaseNr_output, 1, 'L', 0, 0, '', '', true, 0, true);
		$pdf->MultiCell(90, 0, $formText_DebitorName_output, 1, 'L', 0, 0, '', '', true, 0, true);
		$pdf->MultiCell(30, 0, $formText_Basis_output, 1, 'R', 0, 0, '', '', true, 0, true);
		$pdf->MultiCell(30, 0, $formText_Vat_output, 1, 'R', 0, 1, '', '', true, 0, true);

				
		$s_sql = "SELECT cmv.*, SUM(cmt.amount) as basis, concat_ws(' ', debitor.name, debitor.middlename, debitor.lastname) as debitor_name FROM cs_mainbook_voucher cmv
		JOIN cs_mainbook_transaction cmt ON cmt.cs_mainbook_voucher_id = cmv.id
		JOIN cs_bookaccount cb ON cb.id = cmt.bookaccount_id
		JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id 
		JOIN customer debitor ON debitor.id = ccc.debitor_id
		WHERE cmv.settlement_id = ? AND (cb.id = 3 OR cb.id = 5) AND ccc.creditor_id = ? GROUP BY cmv.case_id";
		$o_query = $o_main->db->query($s_sql, array($settlement['id'], $creditor['id']));
		$mainbook_vouchers = ($o_query ? $o_query->result_array() : array());
		$total_basis = 0;
		$total_vat = 0;
		foreach($mainbook_vouchers as $mainbook_voucher){		
			$s_sql = "SELECT cmv.*, SUM(cmt.amount) as vat
			FROM cs_mainbook_voucher cmv
			JOIN cs_mainbook_transaction cmt ON cmt.cs_mainbook_voucher_id = cmv.id
			JOIN cs_bookaccount cb ON cb.id = cmt.bookaccount_id
			WHERE cmv.id = ? AND (cb.id = 14) GROUP BY cmv.id";
			$o_query = $o_main->db->query($s_sql, array($mainbook_voucher['id']));
			$vat_info = ($o_query ? $o_query->row_array() : array());

			$pdf->startTransaction();
			$start_page = $pdf->getPage();
			$pdf = output_transaction_mva($pdf, $mainbook_voucher, $vat_info);
			$end_page = $pdf->getPage();

			if  ($end_page != $start_page) {
				$pdf->rollbackTransaction(true); // don't forget the true
				$pdf->AddPage();
				$pdf = output_transaction_mva($pdf, $mainbook_voucher, $vat_info);
			} else {
				$pdf->commitTransaction();
			}
			$total_basis += $mainbook_voucher['basis'];
			$total_vat += $vat_info['vat'];
		}
		$pdf->MultiCell(30, 0, $formText_Summary_output, 1, 'L', 0, 0, '', '', true, 0, true);
		$pdf->MultiCell(90, 0, "", 1, 'L', 0, 0, '', '', true, 0, true);
		$pdf->MultiCell(30, 0, number_format(abs($total_basis), 2, ",", " "), 1, 'R', 0, 0, '', '', true, 0, true);
		$pdf->MultiCell(30, 0, number_format(abs($total_vat), 2, ",", " "), 1, 'R', 0, 1, '', '', true, 0, true);

	}
	ob_end_clean();
	$pdf->Output('settlement.pdf', 'D');
	exit;
}
?>
