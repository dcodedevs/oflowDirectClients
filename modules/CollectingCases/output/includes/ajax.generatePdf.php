<?php
session_start();
ini_set('max_execution_time', 600);

include_once(dirname(__FILE__).'/tcpdf/tcpdf.php');

$caseId= intval($_POST['caseId']);

$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($caseId));
$caseData = ($o_query ? $o_query->row_array() : array());

if($caseData){
    $s_sql = "SELECT customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
    $creditor = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
    $debitor = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM ownercompany";
    $o_query = $o_main->db->query($s_sql);
    $ownercompany = ($o_query ? $o_query->row_array() : array());

	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor();
	$pdf->SetTitle("");
	$pdf->SetSubject();
	$pdf->SetKeywords();

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

	// ---------------------------------------------------------
	$companyName = $_SESSION['companyName'];


	// add a page
	$pdf->AddPage();


	setlocale(LC_TIME, 'no_NO');
	$pdf->SetFont('times', 'B', 14);
	$pdf->MultiCell(0, 0, $companyName." - ".$formText_SalaryReportNr_output." ".$salaryReport['id']." - ".strftime("%B %Y", strtotime($salaryReport['date']))." - ".$formText_Page_output." ".$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 'L', 0, 1, 10, 10, true, 0, true);
	$pdf->Ln(10);

	$s_sql = "SELECT * FROM salaryreportline LEFT JOIN people ON people.id = salaryreportline.employeeId
	 WHERE salaryreportline.salaryreportId = ? GROUP BY salaryreportline.employeeId";
	$o_query = $o_main->db->query($s_sql, array($salaryReport['id']));
	$employees = ($o_query ? $o_query->result_array() : array());

	$pdf->SetFont('times', '', 12);

	$style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

	$pdf->Line(5, $pdf->GetY(), 205, $pdf->GetY(), $style);
	foreach($employees as $employee){
		$pdf->startTransaction();
		$start_page = $pdf->getPage();

		$pdf->Ln(5);
		$pdf->MultiCell(0, 0, $formText_Name_output.": ".$employee['name']." ".$employee['middle_name']. " ".$employee['last_name'], 0, 'L', 0, 0, 10, "", true, 0, true);
		$pdf->MultiCell(0, 0, $formText_PersonNumber_output.": ".$employee['personNumber'], 0, 'L', 0, 1, 100, "", true, 0, true);
		$pdf->Ln(5);

		$s_sql = "SELECT * FROM salaryreportline WHERE salaryreportline.employeeId = ? AND salaryreportline.salaryreportId = ?";
		$o_query = $o_main->db->query($s_sql, array($employee['id'], $salaryReport['id']));
		$reportlines = ($o_query ? $o_query->result_array() : array());

		$totalHour = 0;
		$totalMoney = 0;
		foreach($reportlines as $reportline){
			$money = $reportline['time'] * $reportline['ratePerHour'];
			$pdf->MultiCell(0, 0, $reportline['name'], 0, 'L', 0, 0, 10, "", true, 0, true);
			$pdf->MultiCell(0, 0, number_format($reportline['time'], 2, ",", ""), 0, 'L', 0, 0, 100, "", true, 0, true);
			$pdf->MultiCell(0, 0, number_format($reportline['ratePerHour'], 2, ",", ""), 0, 'L', 0, 0, 130, "", true, 0, true);
			$pdf->MultiCell(0, 0, $money, 0, 'L', 0, 1, 160, "", true, 0, true);
			$pdf->Ln(2);
			$totalHour += $reportline['time'];
			$totalMoney += $money;
		}
		$pdf->Ln(3);
		$pdf->MultiCell(0, 0, $formText_Sum_Output, 0, 'L', 0, 0, 10, "", true, 0, true);
		$pdf->MultiCell(0, 0, number_format($totalHour, 2, ",", ""), 0, 'L', 0, 0, 100, "", true, 0, true);
		$pdf->MultiCell(0, 0, number_format($totalMoney, 2, ",", ""), 0, 'L', 0, 1, 160, "", true, 0, true);
		$pdf->Ln(5);
		$style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$pdf->Line(5, $pdf->GetY(), 205, $pdf->GetY(), $style);
		// $pdf->Ln(200);

		$end_page = $pdf->getPage();

		if  ($end_page != $start_page) {
		    $pdf->rollbackTransaction(true); // don't forget the true
		    $pdf->AddPage();
		    $pdf->SetFont('times', 'B', 14);
			$pdf->MultiCell(0, 0, $companyName." - ".$formText_SalaryReportNr_output." ".$salaryReport['id']." - ".strftime("%B %Y", strtotime($salaryReport['date']))." - ".$formText_Page_output." ".$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, 'L', 0, 1, 10, 10, true, 0, true);
			$pdf->Ln(10);

			$style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
			$pdf->Line(5, $pdf->GetY(), 205, $pdf->GetY(), $style);

			$pdf->SetFont('times', '', 12);
			$pdf->Ln(5);
			$pdf->MultiCell(0, 0, $formText_Name_output.": ".$employee['name']." ".$employee['middle_name']. " ".$employee['last_name'], 0, 'L', 0, 0, 10, "", true, 0, true);
			$pdf->MultiCell(0, 0, $formText_PersonNumber_output.": ".$employee['personNumber'], 0, 'L', 0, 1, 100, "", true, 0, true);
			$pdf->Ln(5);

			$s_sql = "SELECT * FROM salaryreportline WHERE salaryreportline.employeeId = ? AND salaryreportline.salaryreportId = ?";
			$o_query = $o_main->db->query($s_sql, array($employee['id'], $salaryReport['id']));
			$reportlines = ($o_query ? $o_query->result_array() : array());

			$totalHour = 0;
			$totalMoney = 0;
			foreach($reportlines as $reportline){
				$money = $reportline['time'] * $reportline['ratePerHour'];
				$pdf->MultiCell(0, 0, $reportline['name'], 0, 'L', 0, 0, 10, "", true, 0, true);
				$pdf->MultiCell(0, 0, number_format($reportline['time'], 2, ",", ""), 0, 'L', 0, 0, 100, "", true, 0, true);
				$pdf->MultiCell(0, 0, number_format($reportline['ratePerHour'], 2, ",", ""), 0, 'L', 0, 0, 130, "", true, 0, true);
				$pdf->MultiCell(0, 0, $money, 0, 'L', 0, 1, 160, "", true, 0, true);
				$pdf->Ln(2);
				$totalHour += $reportline['time'];
				$totalMoney += $money;
			}
			$pdf->Ln(3);
			$pdf->MultiCell(0, 0, $formText_Sum_Output, 0, 'L', 0, 0, 10, "", true, 0, true);
			$pdf->MultiCell(0, 0, number_format($totalHour, 2, ",", ""), 0, 'L', 0, 0, 100, "", true, 0, true);
			$pdf->MultiCell(0, 0, number_format($totalMoney, 2, ",", ""), 0, 'L', 0, 1, 160, "", true, 0, true);
			$pdf->Ln(5);
			$style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
			$pdf->Line(5, $pdf->GetY(), 205, $pdf->GetY(), $style);
		} else {
			$pdf->commitTransaction();
		}

	}

	// $pdf->SetFont('times', '', 12);

	// $getSub = mysql_query("SELECT * FROM subcontent
	// 	JOIN subcontentcontent ON subcontentcontent.subcontentID = subcontent.id AND subcontentcontent.languageID = '".$lang."'
	// 	WHERE parentID = '".$activity['id']."' ORDER BY subcontent.sortnr");

	// $pdf->SetFont('times', '', 11);
	// $pdf->Ln(5);
	// $counter = 1;
	// while($ss = mysql_fetch_array($getSub)){
	// 	$pdf->startTransaction();
	// 	$start_page = $pdf->getPage();

	// 	$pdf = addSubContent($ss, $pdf, $counter);

	// 	$end_page = $pdf->getPage();
	// 	if  ($end_page != $start_page) {
	// 	    $pdf->rollbackTransaction(true); // don't forget the true
	// 	    $pdf->AddPage();
	// 		$pdf = addSubContent($ss, $pdf, $counter);

	// 	} else {
	// 		$pdf->commitTransaction();
	// 	}

	// 	$counter++;

	// }
	// ---------------------------------------------------------
	ob_end_clean();
	
	if(isset($_POST['save_pdf']) && 1 == $_POST['save_pdf'])
	{
		//Close and save PDF document
		$pdf->Output(ACCOUNT_PATH.'/uploads/report'.$caseData['id'].'.pdf', 'F');
	} else {
		//Close and output PDF document
		$pdf->Output(ACCOUNT_PATH.'/uploads/report'.$caseData['id'].'.pdf', 'I');
		if(file_exists(ACCOUNT_PATH.'/uploads/report'.$caseData['id'].'.pdf')) {
			$fw_return_data = 'report'.$caseData['id'].'.pdf';
		}
	}
}