<?php
if(!function_exists("generateRandomString")){
    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}
if(!function_exists("output_line")){
	function output_line($pdf, $creditor_transaction, $creditor_fees_by_link, $creditor_payments_by_link){
		
		$border_style = array('LTRB' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

		$fees_and_interest = 0;
		$total_balance = $creditor_transaction['amount'];
		$fees = array();
		$payments = array();

		if($creditor_transaction['link_id'] != ""){
			$fees = $creditor_fees_by_link[$creditor_transaction['link_id']];
			$payments = $creditor_payments_by_link[$creditor_transaction['link_id']];
		}
		foreach($fees as $fee) {
			$fees_and_interest+=$fee['amount'];
		}
		$total_balance+= $fees_and_interest;
		foreach($payments as $payment){
			$total_balance+=$payment['amount'];
		}

		$height = $pdf->getStringHeight(20, $creditor_transaction['invoice_nr']);
		$maxHeight = $height;
		$height = $pdf->getStringHeight(30, $creditor_transaction['kid_number']);
		if($maxHeight < $height) {
			$maxHeight = $height;
		}		
		$height = $pdf->getStringHeight(25, $creditor_transaction['date']);
		if($maxHeight < $height) {
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(25, $creditor_transaction['due_date']);
		if($maxHeight < $height) {
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(25, $creditor_transaction['amount']);
		if($maxHeight < $height) {
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(25, $fees_and_interest);
		if($maxHeight < $height) {
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(0, $total_balance);
		if($maxHeight < $height) {
			$maxHeight = $height;
		}
		$pdf->MultiCell(20, 0, $creditor_transaction['invoice_nr'], $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(30, 0, $creditor_transaction['kid_number'], $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, 0, date("d.m.Y", strtotime($creditor_transaction['date'])), $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, 0, date("d.m.Y", strtotime($creditor_transaction['due_date'])), $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, 0, number_format($creditor_transaction['amount'], 2, ",", " "), $border_style, 'R', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, 0, number_format($fees_and_interest, 2, ",", " "), $border_style, 'R', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(0, 0, number_format($total_balance, 2, ",", " "), $border_style, 'R', 1, 1, '', '', true, 0, true);
			
		return $pdf;
	}
}
if(!function_exists("generate_report")){
    function generate_report($creditor_id, $debitor_id){
        global $o_main;
		$v_return = array();
        $errors = array();
        define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../../')); // this is modified to fit this files location
        define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
        $v_tmp = explode("/",ACCOUNT_PATH);
        $accountname = array_pop($v_tmp);
		
		if(!class_exists("TCPDF")){
	        include(dirname(__FILE__).'/tcpdf/tcpdf.php');
		}
        include(dirname(__FILE__).'/../../languagesOutput/no.php');

		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_id));
		$creditor = ($o_query ? $o_query->row_array() : array());
		if(!$creditor) return $v_return;
		
		$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor['id'], $debitor_id));
		$debitor = ($o_query ? $o_query->row_array() : array());
		if(!$debitor) return $v_return;

		$s_sql = "SELECT * FROM creditor_transactions ct WHERE creditor_id = ? AND external_customer_id = ? AND IFNULL(open, 0) = 1 
		AND system_type='InvoiceCustomer' AND ct.due_date < CURDATE() AND (ct.comment is null OR (ct.comment NOT LIKE '%reminderFee_%' AND ct.comment NOT LIKE '%interest_%')) ";
		$o_query = $o_main->db->query($s_sql, array($creditor['id'], $debitor['creditor_customer_id']));
		$creditor_transactions = ($o_query ? $o_query->result_array() : array());
		if(count($creditor_transactions) == 0) return $v_return;

		$s_sql = "SELECT * FROM creditor_transactions ct WHERE creditor_id = ? AND external_customer_id = ? AND IFNULL(open, 0) = 1 
		AND system_type='InvoiceCustomer' AND  (ct.comment LIKE '%reminderFee_%' OR ct.comment LIKE '%interest_%') ";
		$o_query = $o_main->db->query($s_sql, array($creditor['id'], $debitor['creditor_customer_id']));
		$creditor_fees = ($o_query ? $o_query->result_array() : array());
		$creditor_fees_by_link = array();
		foreach($creditor_fees as $creditor_fee){
			$creditor_fees_by_link[$creditor_fee['link_id']][] = $creditor_fee; 
		}

		$creditor_payments_by_link = array();
		$s_sql = "SELECT * FROM creditor_transactions ct WHERE creditor_id = ? AND external_customer_id = ? AND IFNULL(open, 0) = 1 
		AND (system_type='Payment' OR system_type ='CreditnoteCustomer')";
		$o_query = $o_main->db->query($s_sql, array($creditor['id'], $debitor['creditor_customer_id']));
		$creditor_payments = ($o_query ? $o_query->result_array() : array());
		foreach($creditor_payments as $creditor_payment){
			$creditor_payments_by_link[$creditor_payment['link_id']][] = $creditor_payment; 
		}
		if($debitor['integration_invoice_language'] == 1) {
			include(dirname(__FILE__).'/../../languagesOutput/en.php');
		}
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("");
		$pdf->SetTitle("");
		$pdf->SetSubject("");
		$pdf->SetKeywords("");
		$pdf->SetCompression(true);

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

		// add a page
		$pdf->AddPage();

		setlocale(LC_TIME, 'no_NO');
		$pdf->SetFont('calibri', '', 11);

		$logoImage = json_decode($creditor['invoicelogo']);
		$companyNamePdf = $creditor['companyname'];
		$companyAddress = $creditor['companypostalbox'].", ".$creditor['companyzipcode']." ".$creditor['companypostalplace'];
		$companyPhone = $creditor['companyphone'];
		$companyOrgNr = $creditor['companyorgnr'];
		$companyEmail = $creditor['companyEmail'];
		$bank_account = $creditor['bank_account'];
		if($creditor['use_local_email_phone_for_reminder']) {
			$companyPhone = $creditor['local_phone'];
			$companyEmail = $creditor['local_email'];
		}

		
		if(count($logoImage) > 0){
			$pdf->SetY(3);
			$imageLocation = ACCOUNT_PATH."/".$logoImage[0][1][0];
			$ext = end(explode(".", $imageLocation));
			$image = base64_encode(file_get_contents($imageLocation));
			$pdf->writeHTML('<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" height="60" />', true, false, true, false, '');
		} else {

		}
		$pdf->SetY(20);
		$pdf->MultiCell(70, 0, $companyNamePdf, 0, 'L', 0, 1, 125, '', true, 0, true);
		$pdf->MultiCell(70, 0, $companyAddress, 0, 'L', 0, 1, 125, '', true, 0, true);
		$pdf->MultiCell(70, 0, $formText_OrgNr_pdf." ".$companyOrgNr, 0, 'L', 0, 1, 125, '', true, 0, true);
		$pdf->MultiCell(70, 0, $formText_Phone_pdf." ".$companyPhone, 0, 'L', 0, 1, 125, '', true, 0, true);
		$pdf->MultiCell(70, 0, $formText_Email_pdf." ".$companyEmail, 0, 'L', 0, 1, 125, '', true, 0, true);
		if($bank_account != "") {
			$pdf->MultiCell(70, 0, $formText_BankAccount_pdf." ".$bank_account, 0, 'L', 0, 1, 125, '', true, 0, true);
		}
		$pdf->Ln(2);
		$pdf->MultiCell(70, 0, $formText_Date_pdf." ".date("d.m.Y"), 0, 'L', 0, 1, 125, '', true, 0, true);
		$date = date("d.m.Y", time());
		$pdf->Ln(2);
		// $pdf->MultiCell(70, 0, $formText_Date_pdf." ".$date, 0, 'L', 0, 1, 125, '', true, 0, true);
		// $dueDatePdf = date("d.m.Y", strtotime($caseData['due_date']));
		// $pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$dueDatePdf."", 0, 'L', 0, 1, 125, '', true, 0, true);


		// $pdf->MultiCell(50, 0, $formText_RegistrationNumber_pdf, 0, 'R', 0, 0, 100, '', true, 0, true);
		// $pdf->MultiCell(30, 0, $ownercompany['companyorgnr'], 0, 'R', 0, 1, 165, '', true, 0, true);
		$pdf->SetFont('calibri', '', 9);
		$pdf->SetX(20);

		$lineBreakAfter = 40;
		$linesAfterInfo = 5;
		$pdf->SetY(20);
		$pdf->MultiCell(70, 0, $formText_Sender_output.": ".$companyNamePdf.", ".$companyAddress, 0, 'L', 0, 1, '', '', true, 0, true);

		$pdf->SetFont('calibri', '', 11);
		$pdf->SetX(20);
		$pdf->SetY($lineBreakAfter);

		$pdf->MultiCell(100, 0, $debitor['name']." ".$debitor['middle_name']." ".$debitor['last_name'], 0, 'L', 0, 1, '', '', true, 0, true);
		$pdf->MultiCell(100, 0, $debitor['paStreet'], 0, 'L', 0, 1, '', '', true, 0, true);
		$pdf->MultiCell(100, 0, $debitor['paPostalNumber']." ".$debitor['paCity'], 0, 'L', 0, 1, '', '', true, 0, true);
		$pdf->MultiCell(100, 0, $debitor['paCountry'], 0, 'L', 0, 1, '', '', true, 0, true);

		$y = $pdf->GetY();
		if($showLogo){
			$imageLocation = ACCOUNT_PATH."/uploads/249/0/Oflow-logo (1).png";
			if(file_exists($imageLocation)) {
				$pdf->Ln(29);
				$ext = end(explode(".", $imageLocation));
				$image = base64_encode(file_get_contents($imageLocation));
				$pdf->writeHTMLCell(80, 0, 125, $y+29, '<div style="text-alight: left;">'.$formText_OurPartnerOnCollectingCases_output.':<br/><img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="140" /></div>', 0, 0, 0, true,'', true);
				// $pdf->writeHTML('<div style="text-alight: left;">'.$formText_OurPartnerOnCollectingCases_output.':<br/><img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="140" /></div>', true, false, true, false, 'L');
				// $linesAfterInfo = 0;
			}
		}
		$pdf->SetY($y);
		$pdf->Ln($linesAfterInfo);

		$pdf->SetFont('calibri', '', 11);

		
		// $project_name = $creditor_invoice['project_name'];
		// if($creditor['activate_project_code_in_reminderletter'] && $project_name != "") {
		// 	$pdf->MultiCell(55, 0, "".$formText_Reference_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
		// 	$pdf->MultiCell(0, 0, $project_name, 0, 'L', 0, 1, '', '', true, 0, true);
		// }
		// if(!$reminderingFromCreditor){
		// 	$pdf->MultiCell(55, 0, "".$formText_CreditorName_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
		// 	$pdf->MultiCell(0, 0, "<b>".$creditor['companyname']."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
		// 	$pdf->SetFont('calibri', '', 11);
		// 	$pdf->MultiCell(55, 0, "".$formText_CaseId_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
		// 	$pdf->MultiCell(0, 0, $caseData['id'], 0, 'L', 0, 1, '', '', true, 0, true);
		// }
		// if($system_settings['debitor_portal_url'] != "" && $addCodeOnPdf) {
		// 	$pdf->MultiCell(55, 0, $formText_LoginToYourCaseHere.": ", 0, 'L', 0, 0, '', '', true, 0, true);
		// 	$pdf->Write(0, $system_settings['debitor_portal_url'], $system_settings['debitor_portal_url'], false, 'L', true);
		// 	$pdf->MultiCell(55, 0, "".$formText_UseThisCodeWhenLogin_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
		// 	$pdf->MultiCell(0, 0, "<b>".$code."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
		// }

		$pdf->Ln(5);
		$pdf->SetFont('calibri', 'b', 15);
		$pdf->MultiCell(0, 0, $formText_ReminderOverviewHeadline_output, 0, 'L', 0, 1, '', '', true, 0, true);
		$pdf->SetFont('calibri', '', 11);
		$pdf->MultiCell(0, 0, $formText_ReminderOverviewText_output, 0, 'L', 0, 1, '', '', true, 0, true);
		$pdf->Ln(5);

		$pdf->setCellPaddings(2, 2, 2, 2);

		$pdf->SetFillColor(255, 255, 255);
		$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
		
		$pdf->SetFont('calibri', '', 9);

		$height = $pdf->getStringHeight(20, $formText_InvoiceNr_output);
		$maxHeight = $height;
		$height = $pdf->getStringHeight(30, $formText_kidNumber_output);
		if($maxHeight < $height){
			$maxHeight = $height;
		}		
		$height = $pdf->getStringHeight(25, $formText_OriginalDate_output);
		if($maxHeight < $height){
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(25, $formText_OriginalDueDate_output);
		if($maxHeight < $height){
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(25, $formText_OriginalAmount_output);
		if($maxHeight < $height){
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(25, $formText_FeesAndInterest_output);
		if($maxHeight < $height){
			$maxHeight = $height;
		}
		$height = $pdf->getStringHeight(0, $formText_TotalBalance_output);
		if($maxHeight < $height){
			$maxHeight = $height;
		}
		$border_style = array('LTRB' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

		$pdf->MultiCell(20, $maxHeight, $formText_InvoiceNr_output, $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(30, $maxHeight, $formText_kidNumber_output, $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, $maxHeight, $formText_OriginalDate_output, $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, $maxHeight, $formText_OriginalDueDate_output, $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, $maxHeight, $formText_OriginalAmount_output, $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(25, $maxHeight, $formText_FeesAndInterest_output, $border_style, 'L', 1, 0, '', '', true, 0, true);
		$pdf->MultiCell(0, $maxHeight, $formText_TotalBalance_output, $border_style, 'L', 1, 1, '', '', true, 0, true);
		foreach($creditor_transactions as $creditor_transaction) {
			$pdf->startTransaction();
			$start_page = $pdf->getPage();
			$pdf = output_line($pdf, $creditor_transaction, $creditor_fees_by_link, $creditor_payments_by_link);			
			$end_page = $pdf->getPage();
			if  ($end_page != $start_page) {
				$pdf->rollbackTransaction(true); // don't forget the true
				$pdf->AddPage();
				$pdf = output_line($pdf, $creditor_transaction, $creditor_fees_by_link, $creditor_payments_by_link);
			}
		}
		//Close and save PDF document
		$s_filename = 'uploads/protected/'.$formText_ReminderOverview_output.'_'.$debitor['id'].'_'.time().'.pdf';
		$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');

		$s_sql = "SELECT * FROM moduledata WHERE name = 'CollectingCases'";
		$o_query = $o_main->db->query($s_sql);
		$moduleInfo = ($o_query ? $o_query->row_array() : array());
		$moduleID = $moduleInfo['uniqueID'];

		$s_sql = "INSERT INTO creditor_debitor_reminder_overview_report SET moduleID = '".$o_main->db->escape_str($moduleID)."', 
		createdBy='process', created=NOW(), creditor_id='".$o_main->db->escape_str($creditor['id'])."', 
		debitor_id='".$o_main->db->escape_str($debitor['id'])."', pdf='".$o_main->db->escape_str($s_filename)."'";
		$o_query = $o_main->db->query($s_sql);

		if($o_query) {
			$report_id = $o_main->db->insert_id();
			foreach($creditor_transactions as $creditor_transaction) {
				$s_sql = "INSERT INTO creditor_debitor_reminder_overview_report_line SET moduleID = '".$o_main->db->escape_str($moduleID)."', 
				createdBy='process', created=NOW(), creditor_debitor_reminder_overview_report_id='".$o_main->db->escape_str($report_id)."', 
				transaction_id='".$o_main->db->escape_str($creditor_transaction['id'])."'";
				$o_query = $o_main->db->query($s_sql);
			}
		}
        if(count($errors) > 0){
            $v_return['errors'] = $errors;
        } else {
			$v_return['report_id'] = $report_id;
			$v_return['pdf'] = $s_filename;
		}
        return $v_return;
    }
}
?>
