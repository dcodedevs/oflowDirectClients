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

function output_transaction($pdf, $mainbook_voucher, $vat_info) {
	global $o_main;

	$pdf->MultiCell(30, 0, $mainbook_voucher['case_id'], 1, 'L', 0, 0, '', '', true, 0, true);
	$pdf->MultiCell(90, 0, $mainbook_voucher['debitor_name'], 1, 'L', 0, 0, '', '', true, 0, true);
	$pdf->MultiCell(30, 0, number_format(abs($mainbook_voucher['basis']), 2, ",", " "), 1, 'R', 0, 0, '', '', true, 0, true);
	$pdf->MultiCell(30, 0, number_format(abs($vat_info['vat']), 2, ",", " "), 1, 'R', 0, 1, '', '', true, 0, true);	

	return $pdf;
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


    $s_sql = "SELECT * FROM ownercompany";
    $o_query = $o_main->db->query($s_sql);
    $ownercompany = ($o_query ? $o_query->row_array() : array());
	
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
	$pdf->MultiCell(70, 0, $companyNamePdf, 0, 'L', 0, 1, 125, '', true, 0, true);
	$pdf->MultiCell(70, 0, $companyAddress, 0, 'L', 0, 1, 125, '', true, 0, true);
	$pdf->MultiCell(70, 0, $formText_OrgNr_pdf." ".$companyOrgNr, 0, 'L', 0, 1, 125, '', true, 0, true);
	$pdf->Ln(2);
	$pdf->MultiCell(70, 0, $formText_Phone_output." ".$companyPhone, 0, 'L', 0, 1, 125, '', true, 0, true);
	$pdf->MultiCell(70, 0, $formText_Email_output." ".$companyEmail, 0, 'L', 0, 1, 125, '', true, 0, true);
	$pdf->Ln(2);
	if($companyIban != "") {
		$pdf->MultiCell(70, 0, $formText_Iban_pdf." ".$companyIban, 0, 'L', 0, 1, 125, '', true, 0, true);
	}
	if($companySwift != "") {
		$pdf->MultiCell(70, 0, $formText_Swift_pdf." ".$companySwift, 0, 'L', 0, 1, 125, '', true, 0, true);
	}
	$pdf->Ln(2);
	$pdf->MultiCell(70, 0, $formText_Date_pdf." ".date("d.m.Y"), 0, 'L', 0, 1, 125, '', true, 0, true);

	$pdf->SetFont('calibri', '', 9);
	$pdf->SetX(20);
	$pdf->SetY(20);
	$pdf->MultiCell(80, 0, $formText_Return_output.": ". $companyNamePdf.", ".$companyAddress, 0, 'L', 0, 1, '', '', true, 0, true);

	$pdf->SetFont('calibri', '', 11);
	$pdf->SetX(20);
	$pdf->SetY(40);
	$pdf->MultiCell(100, 0, $creditor['companyname'], 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(100, 0, $creditor['companypostalbox'], 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(100, 0, $creditor['companyzipcode']." ".$creditor['companypostalplace'], 0, 'L', 0, 1, '', '', true, 0, true);

	$pdf->Ln(26);

	$pdf->SetFont('calibri', '', 14);
	$pdf->MultiCell(180, 0, $formText_VatSpecifications_output, 1, 'C', 0, 1, '', '', true, 0, true);
	$pdf->SetFont('calibri', '', 11);
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
		$pdf = output_transaction($pdf, $mainbook_voucher, $vat_info);
		$end_page = $pdf->getPage();

		if  ($end_page != $start_page) {
			$pdf->rollbackTransaction(true); // don't forget the true
			$pdf->AddPage();
			$pdf = output_transaction($pdf, $mainbook_voucher, $vat_info);
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

	// $pdf->MultiCell(60, 0, $formText_Page_output." ".$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 'L', 0, 1, 155, '', true, 0, true);


	ob_end_clean();
	$pdf->Output('vat_specification.pdf', 'D');
	exit;
}
?>
