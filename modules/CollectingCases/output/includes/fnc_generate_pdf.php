<?php
if(!function_exists("generateRandomString")){
    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}
if(!function_exists("generate_pdf")){
    function generate_pdf($caseId, $rest_note = 0){
        global $o_main;
		global $variables;
        $errors = array();
        define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
        define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
        $v_tmp = explode("/",ACCOUNT_PATH);
        $accountname = array_pop($v_tmp);
		if(!class_exists("TCPDF")){
	        include(dirname(__FILE__).'/tcpdf/tcpdf.php');
		}
        include(dirname(__FILE__).'/../languagesOutput/no.php');
		if($rest_note){
	        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
	        $o_query = $o_main->db->query($s_sql, array($caseId));
	        $caseData = ($o_query ? $o_query->row_array() : array());
		} else {
	        $s_sql = "SELECT * FROM collecting_cases WHERE id = ? AND create_letter = 1";
	        $o_query = $o_main->db->query($s_sql, array($caseId));
	        $caseData = ($o_query ? $o_query->row_array() : array());
		}

        if($caseData)
        {
			$isCompany = false;
            $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
            $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
            $creditor = ($o_query ? $o_query->row_array() : array());

            $s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
            $o_query = $o_main->db->query($s_sql);
            $system_settings = ($o_query ? $o_query->row_array() : array());

            $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
            $o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
            $debitor = ($o_query ? $o_query->row_array() : array());

			if($debitor['integration_invoice_language'] == 1){
		        include(dirname(__FILE__).'/../languagesOutput/en.php');
			}
			$customer_type_collect_debitor = $debitor['customer_type_collect'];
			if($debitor['customer_type_collect_addition'] > 0){
				$customer_type_collect_debitor = $debitor['customer_type_collect_addition'] - 1;
			}									
			if($customer_type_collect_debitor == 0){
				$isCompany = true;
				// if($debitor['organization_type'] == "ENK") {
				// 	$customer_type_collect_debitor = 1;
				// }
			}

            $case_level = "reminder";
            $s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
            $o_query = $o_main->db->query($s_sql, array($caseData['id']));
            $creditor_invoice = ($o_query ? $o_query->row_array() : array());
			if($creditor_invoice['open']){
				$currencyName = "";
				if($creditor_invoice['currency'] == 'LOCAL') {
					$currencyName = trim($creditor['default_currency']);
				} else {
					$currencyName = trim($creditor_invoice['currency']);
				}

				if($caseData['status'] == 1 || $caseData['status'] == 2 || $caseData['status'] == 0) {

					$transactionsNotClosed = false;
					$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='Payment' AND link_id = ? AND creditor_id = ? ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($creditor_invoice['link_id'], $creditor_invoice['creditor_id']));
					$claim_transactions = ($o_query ? $o_query->result_array() : array());

					foreach($claim_transactions as $claim_transaction) {
						$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ?";
						$o_query = $o_main->db->query($sql, array($claim_transaction['comment']));
						$parent_transaction = $o_query ? $o_query->row_array() : array();
						if($parent_transaction && $parent_transaction['open']){
							$transactionsNotClosed = true;
						}
					}
				} else if($caseData['status'] == 3 || $caseData['status'] == 4){
					$transactionsNotClosed = false;
					$case_level = "collecting";
				}
				if(!$transactionsNotClosed){
					$s_sql = "SELECT * FROM ownercompany";
					$o_query = $o_main->db->query($s_sql);
					$ownercompany = ($o_query ? $o_query->row_array() : array());
					if($case_level == "collecting"){
						$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
						$process_step = ($o_query ? $o_query->row_array() : array());
					} else {
						$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
						$process_step = ($o_query ? $o_query->row_array() : array());
					}
					if($process_step || $rest_note) {
						if($caseData['reminder_profile_id'] > 0){
							$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($caseData['reminder_profile_id']));
							$profile = $o_query ? $o_query->row_array() : array();
						}
						if(!$profile) {
							if($debitor['creditor_reminder_profile_id'] > 0){
								$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($debitor['creditor_reminder_profile_id']));
								$profile = $o_query ? $o_query->row_array() : array();
							}
							if(!$profile){
								if($customer_type_collect_debitor == 0){
									$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
									$profile = $o_query ? $o_query->row_array() : array();
								} else {
									$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
									$profile = $o_query ? $o_query->row_array() : array();
								}
							}
						}
						$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
						$o_query = $o_main->db->query($s_sql, array($profile['id']));
						$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();

						$profile_values = array();
						foreach($unprocessed_profile_values as $unprocessed_profile_value) {
							$profile_values[$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
						}
						$profile_value = $profile_values[$process_step['id']];
						$customizedText = false;
						if($rest_note){
							$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
							$o_query = $o_main->db->query($s_sql, array($system_settings['rest_note_pdftext_id']));
							$pdfText = ($o_query ? $o_query->row_array() : array());
						} else {
							if($profile_value['pdftext_title'] != "" || $profile_value['pdftext_text'] != ""){
								$pdfText['title'] = $profile_value['pdftext_title'];
								$pdfText['text'] = $profile_value['pdftext_text'];
								$customizedText = true;
								// $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
								// $o_query = $o_main->db->query($s_sql, array($profile_value['collecting_cases_pdftext_id']));
								// $pdfText = ($o_query ? $o_query->row_array() : array());
							} else {
								$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
								$o_query = $o_main->db->query($s_sql, array($process_step['collecting_cases_pdftext_id']));
								$pdfText = ($o_query ? $o_query->row_array() : array());
							}
						}
						$pdfTextTitle = $pdfText['title'];
						$pdfTextMain = nl2br($pdfText['text']);
						$in_english_language = 0;
						if($debitor['integration_invoice_language'] == 1 && !$customizedText) {
							$in_english_language = 1;
							$pdfTextTitle = $pdfText['title_english'];
							$pdfTextMain = nl2br($pdfText['text_english']);
						}

						$showLogo = false;
						if($process_step['show_collecting_company_logo']) {
							$showLogo = true;
						}
						if($profile_value['show_collecting_company_logo'] > 0){
							if($profile_value['show_collecting_company_logo'] == 2) {
								$showLogo = true;
							} else {
								$showLogo = false;
							}
						}
						$s_sql = "SELECT * FROM claim_letter_bottomtext";
						$o_query = $o_main->db->query($s_sql);
						$bottomText = ($o_query ? $o_query->row_array() : array());

						if(intval($process_step['bank_account_choice']) == 0) {
							$bankAccount = $creditor['bank_account'];
							$kidNumber = $creditor_invoice['kid_number'];
						} else if (intval($process_step['bank_account_choice']) == 1) {
							$bankAccount = $ownercompany['companyaccount'];
							$kidNumber = "";
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

						// set some language-dependent strings (optional)
						if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
							require_once(dirname(__FILE__).'/lang/eng.php');
							$pdf->setLanguageArray($l);
						}

						// ---------------------------------------------------------
						$companyName = $_SESSION['companyName'];
						$code_entry_id = 0;
						if($system_settings['debitor_portal_url'] != ""){
							do {
								$key = generateRandomString("10");
								$s_sql = "select * from collecting_cases_debitor_codes where code = ? AND expiration_time > NOW()";
								$o_query = $o_main->db->query($s_sql, array($key));
								$key_item = $o_query ? $o_query->row_array() : array();
							} while(count($key_item) > 0);

							$code = $key;
							$customer_id = $debitor['id'];
							$expiration_time = date("Y-m-d H:i:s", strtotime("+".$system_settings['days_for_debitor_code_to_expire']." days"));
							$s_sql = "INSERT INTO collecting_cases_debitor_codes SET createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(),
							customer_id = ?, code= ?, expiration_time = ?, collecting_cases_id = ?";
							$o_query = $o_main->db->query($s_sql, array($customer_id, $code, $expiration_time, $caseData['id']));
							$code_entry_id = $o_main->db->insert_id();
						}

						// add a page
						$pdf->AddPage();


						setlocale(LC_TIME, 'no_NO');
						$pdf->SetFont('calibri', '', 11);

						$reminderingFromCreditor = false;
						$addCodeOnPdf = false;
						if(!$creditor['addCreditorPortalCodeOnLetter'] || intval($creditor['send_reminder_from']) == 1 || intval($process_step['status_id']) == 3) {
							$addCodeOnPdf = true;
						}
						if((intval($process_step['status_id']) == 0 || intval($process_step['status_id']) == 1) && intval($creditor['send_reminder_from']) == 0) {
							$reminderingFromCreditor = true;
						}
						$companyIban = "";
						$companySwift = "";
						if($reminderingFromCreditor) {
							$logoImage = json_decode($creditor['invoicelogo']);
							$companyNamePdf = $creditor['companyname'];
							$companyAddress = $creditor['companypostalbox'].", ".$creditor['companyzipcode']." ".$creditor['companypostalplace'];
							$companyPhone = $creditor['companyphone'];
							$companyOrgNr = $creditor['companyorgnr'];
							$companyEmail = $creditor['companyEmail'];
							$companyIban = $creditor['companyiban'];
							$companySwift = $creditor['companyswift'];
							if($creditor['use_local_email_phone_for_reminder']) {
								$companyPhone = $creditor['local_phone'];
								$companyEmail = $creditor['local_email'];
							}
						} else {
							$logoImage = json_decode($ownercompany['invoicelogo']);
							$companyNamePdf = $ownercompany['companyname'];
							$companyAddress = $ownercompany['companypostalbox'].", ".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
							$companyPhone = $ownercompany['companyphone'];
							$companyOrgNr = $ownercompany['companyorgnr'];
							$companyEmail = $ownercompany['companyEmail'];
							$companyIban = $ownercompany['companyiban'];
							$companySwift = $ownercompany['companyswift'];
						}

						$addedLogo = false;
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
						$pdf->Ln(2);
						if($companyIban != "") {
							$pdf->MultiCell(70, 0, $formText_Iban_pdf.": ".$companyIban, 0, 'L', 0, 1, 125, '', true, 0, true);
						}
						if($companySwift != "") {
							$pdf->MultiCell(70, 0, $formText_Swift_pdf.": ".$companySwift, 0, 'L', 0, 1, 125, '', true, 0, true);
						}
						$date = date("d.m.Y", time());
						$pdf->Ln(2);
						$pdf->MultiCell(70, 0, $formText_Date_pdf." ".$date, 0, 'L', 0, 1, 125, '', true, 0, true);
						$dueDatePdf = date("d.m.Y", strtotime($caseData['due_date']));
						// if($rest_note){
						// 	$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$formText_Immediately_output."", 0, 'L', 0, 1, 125, '', true, 0, true);
						// } else {
							$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$dueDatePdf."", 0, 'L', 0, 1, 125, '', true, 0, true);
						// }


						// $pdf->MultiCell(50, 0, $formText_RegistrationNumber_pdf, 0, 'R', 0, 0, 100, '', true, 0, true);
						// $pdf->MultiCell(30, 0, $ownercompany['companyorgnr'], 0, 'R', 0, 1, 165, '', true, 0, true);
						$pdf->SetFont('calibri', '', 9);
						$pdf->SetX(20);

						if($addedLogo){
							$pdf->SetY(40);
							$lineBreakAfter = 60;
							$linesAfterInfo = 16;
							$linesForOflowLogo = 9;
						} else {
							$pdf->SetY(20);
							$linesAfterInfo = 36;
							$lineBreakAfter = 40;
							$linesForOflowLogo = 29;
						}
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
								$pdf->Ln($linesForOflowLogo);
								$ext = end(explode(".", $imageLocation));
								$image = base64_encode(file_get_contents($imageLocation));
								$pdf->writeHTMLCell(80, 0, 125, $y+$linesForOflowLogo, '<div style="text-alight: left;">'.$formText_OurPartnerOnCollectingCases_output.':<br/><img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="140" /></div>', 0, 0, 0, true,'', true);
								// $pdf->writeHTML('<div style="text-alight: left;">'.$formText_OurPartnerOnCollectingCases_output.':<br/><img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="140" /></div>', true, false, true, false, 'L');
								// $linesAfterInfo = 0;
							}
						}
						$pdf->SetY($y);
						$pdf->Ln($linesAfterInfo);

						$pdf->SetFont('calibri', '', 11);
						$project_name = $creditor_invoice['project_name'];
						if($creditor['activate_project_code_in_reminderletter'] && $project_name != "") {
							$pdf->MultiCell(55, 0, "".$formText_Reference_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $project_name, 0, 'L', 0, 1, '', '', true, 0, true);
						}
						if(!$reminderingFromCreditor){
							$pdf->MultiCell(55, 0, "".$formText_CreditorName_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, "<b>".$creditor['companyname']."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
							$pdf->SetFont('calibri', '', 11);
							$pdf->MultiCell(55, 0, "".$formText_CaseId_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $caseData['id'], 0, 'L', 0, 1, '', '', true, 0, true);
						}
						if($system_settings['debitor_portal_url'] != "" && $addCodeOnPdf) {
							$pdf->MultiCell(55, 0, $formText_LoginToYourCaseHere.": ", 0, 'L', 0, 0, '', '', true, 0, true);
							$pdf->Write(0, $system_settings['debitor_portal_url'], $system_settings['debitor_portal_url'], false, 'L', true);
							$pdf->MultiCell(55, 0, "".$formText_UseThisCodeWhenLogin_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, "<b>".$code."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
						}

						$pdf->Ln(5);
						$pdf->SetFont('calibri', 'b', 15);
						$pdf->MultiCell(0, 0, $pdfTextTitle, 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->SetFont('calibri', '', 11);
						$pdf->MultiCell(0, 0, $pdfTextMain, 0, 'L', 0, 1, '', '', true, 0, true);

						$pdf->Ln(10);

						$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($caseData['id']));
						$invoice = ($o_query ? $o_query->row_array() : array());

						$totalSumPaid = 0;
						$totalSumDue = 0;

						$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='Payment' AND link_id = ? AND creditor_id = ? ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
						$payments = ($o_query ? $o_query->result_array() : array());

						foreach($payments as $payment) {
							$totalSumPaid += $payment['amount'];
						}

						$connected_transactions = array();
						$all_connected_transaction_ids = array($invoice['id']);
						if($invoice['link_id'] > 0 && ($creditor['checkbox_1'])) {
							$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
							$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['id']));
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

						$totalSumDue += $invoice['collecting_case_original_claim'];
						foreach($connected_transactions as $connected_transaction) {
							$totalSumDue += $connected_transaction['amount'];
						}
						$s_sql = "SELECT creditor_transactions.*
							FROM creditor_transactions WHERE system_type = 'CreditnoteCustomer' AND creditor_transactions.link_id = ? AND creditor_transactions.creditor_id = ?
						ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
						$creditnotes = ($o_query ? $o_query->result_array() : array());

						foreach($creditnotes as $creditnote) {
							if(!in_array($creditnote['id'], $all_connected_transaction_ids)){
								$totalSumDue += $creditnote['amount'];
							}
						}
						//includes invoice + payments done before case is created
						$original_main_claim = $totalSumDue;

						$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
						$o_query = $o_main->db->query($s_sql, array($caseData['id']));
						$claims = ($o_query ? $o_query->result_array() : array());

						$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
						$claim_transactions = ($o_query ? $o_query->result_array() : array());
						foreach($claim_transactions as $claim) {
							$totalSumDue += $claim['amount'];
						}
						foreach($claims as $claim) {
							$totalSumDue += $claim['amount'];
						}

						$pdf->setCellPaddings(2, 2, 2, 2);

						$pdf->SetFillColor(255, 255, 255);

						// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

						$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));

						//lastRow
						// $pdf->SetFillColor(254, 209, 71);
						$topBordered = false;
						if($invoice){
							$topBordered = true;
							$claimAmount = number_format($invoice['collecting_case_original_claim'], 2, ",", " ");
							$invoiceDueText = "";
							if($invoice['due_date'] != "0000-00-00" && $invoice['due_date'] != ""){
								$dueDate = date("d.m.Y", strtotime($invoice['due_date']));
								$invoiceDueText = " - ".$formText_DueDate_output." ".$dueDate;
							}
							$pdf->MultiCell(120, 0, $formText_InvoiceNumber_output." ".$invoice['invoice_nr'].$invoiceDueText, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(46, 0, $claimAmount, 'T', 'R', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $currencyName, 'RT', 'R', 1, 1, '', '', true, 0, true);
							foreach($payments as $payment){
								$pdf->MultiCell(120, 0, $formText_Payment_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(46, 0, number_format($payment['amount'], 2, ",", " "), '', 'R', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $currencyName, 'R', 'R', 1, 1, '', '', true, 0, true);
							}
							foreach($creditnotes as $payment){
								$pdf->MultiCell(120, 0, $formText_CreditNote_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(46, 0, number_format($payment['amount'], 2, ",", " "), '', 'R', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $currencyName, 'R', 'R', 1, 1, '', '', true, 0, true);
							}	
							foreach($connected_transactions as $connected_transaction) {
								$invoiceDueText = "";
								if($connected_transaction['due_date'] != "0000-00-00" && $connected_transaction['due_date'] != ""){
									$dueDate_connected = date("d.m.Y", strtotime($connected_transaction['due_date']));
									$invoiceDueText = " - ".$formText_DueDate_output." ".$dueDate_connected;
								}
								$invoice_label = $formText_ExtraInvoiceConnected_output;
								if($connected_transaction['system_type'] == "CreditnoteCustomer") {
									$invoice_label = $formText_CreditNote_output;
								}
								$pdf->MultiCell(120, 0, $invoice_label." ".$connected_transaction['invoice_nr'].$invoiceDueText, 'L', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(46, 0, number_format($connected_transaction['amount'], 2, ",", " "), '', 'R', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $currencyName, 'R', 'R', 1, 1, '', '', true, 0, true);
							}
						}

						foreach($claim_transactions as $claim_transaction) {
							$claim_text_array = explode("_", $claim_transaction['comment']);
							if($topBordered){
								$border = "L";
								$border2 = "R";
								$border3 = "";
							} else {
								$border = "LT";
								$border2 = "RT";
								$border3 = "T";
								$topBordered = true;
							}
							
							$claim_name = $claim_text_array[0];
							$claim_name_array = explode(" ", $claim_name);
							if($in_english_language) {
								foreach($claim_name_array as $key=> $claim_name_single){
									$s_sql = "SELECT * FROM collecting_cases_claim_line_translations WHERE base_text='".$o_main->db->escape_str($claim_name_single)."' AND language = 1";
									$o_query = $o_main->db->query($s_sql);
									$translation = ($o_query ? $o_query->row_array() : array());
									if($translation) {
										$claim_name_array[$key] = $translation['translated_text'];
									}
								}
							}
							$claimAmount = number_format($claim_transaction['amount'], 2, ",", " ");
							$pdf->MultiCell(120, 0, implode(" ", $claim_name_array), $border, 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(46, 0, $claimAmount, $border3, 'R', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
						}
						foreach($claims as $claim) {
							if($topBordered){
								$border = "L";
								$border2 = "R";
								$border3 = "";
							} else {
								$border = "LT";
								$border2 = "RT";
								$border3 = "T";
								$topBordered = true;
							}
							$claim_name = $claim['name'];
							$claim_name_array = explode(" ", $claim_name);
							if($in_english_language) {
								foreach($claim_name_array as $key=> $claim_name_single){
									$s_sql = "SELECT * FROM collecting_cases_claim_line_translations WHERE base_text='".$o_main->db->escape_str($claim_name_single)."' AND language = 1";
									$o_query = $o_main->db->query($s_sql);
									$translation = ($o_query ? $o_query->row_array() : array());
									if($translation) {
										$claim_name_array[$key] = $translation['translated_text'];
									}
								}
							}
							$claimAmount = number_format($claim['amount'], 2, ",", " ");
							$pdf->MultiCell(120, 0, implode(" ", $claim_name_array), $border, 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(46, 0, $claimAmount, $border3, 'R', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
						}

						$pdf->MultiCell(120, 0, $formText_AmountToPay_pdf, 'TLB', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(46, 0, number_format($totalSumDue+$totalSumPaid, 2, ",", " "), 'TB', 'R', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, $currencyName, 'TRB', 'R', 1, 1, '', '', true, 0, true);

						if($isCompany){
							$pdf->SetFont('calibri', '', 8);
							$pdf->Ln(1);
							$pdf->MultiCell(0, 0, $formText_FeeExplanationText_output, '', 'L', 0, 1, '', '', true, 0, true);
						}
						$pdf->Ln(5);

						$pdf->SetFont('calibri', '', 11);

						$pdf->setCellPaddings(0, 0, 0, 0);
						$pdf->MultiCell(0, 0, "<b>".$formText_BankAccount_pdf.":</b> ".$bankAccount, 0, 'L', 0, 1, '', '', true, 0, true);
						if($kidNumber != "") {
							$pdf->MultiCell(0, 0, "<b>".$formText_KidNumber_pdf.":</b> ".$kidNumber, 0, 'L', 0, 1, '', '', true, 0, true);
						}

						// if($rest_note){
						// 	$pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$formText_Immediately_output, 0, 'L', 0, 1, '', '', true, 0, true);
						// } else {
							$pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$dueDatePdf, 0, 'L', 0, 1, '', '', true, 0, true);
						// }
						$pdf->MultiCell(0, 0, "<b>".$formText_AmountDue_pdf.":</b> ".number_format($totalSumDue+$totalSumPaid, 2, ",", " ")." ".$currencyName, 0, 'L', 0, 1, '', '', true, 0, true);

						$pdf->Ln(15);

						$pdf->MultiCell(0, 0, $formText_BestRegards_pdf, 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, "<b>".$companyNamePdf."</b>", 0, 'L', 0, 1, '', '', true, 0, true);

						if($case_level == "collecting") {
							$pdf->AddPage();
							$topBordered = false;
							$pdf->setCellPaddings(2, 2, 2, 2);
							$pdf->SetFillColor(255, 255, 255);

							// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');
							if(count($main_claims) > 0) {
								$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
								$pdf->MultiCell(50, 0, $formText_InvoiceNr_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, $formText_InvoiceDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, $formText_InvoiceDueDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $formText_MainClaim_output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
								foreach($main_claims as $main_claim){
									$mainClaimTotal = number_format($main_claim['amount'], 2, ".", " ");
									$pdf->MultiCell(50, 0, $main_claim['invoice_nr'], 'LT', 'L', 1, 0, '', '', true, 0, true);
									$pdf->MultiCell(35, 0, date("d.m.Y", strtotime($main_claim['date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
									$pdf->MultiCell(35, 0, date("d.m.Y", strtotime($main_claim['original_due_date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
									$pdf->MultiCell(0, 0, $mainClaimTotal, 'LRT', 'R', 1, 1, '', '', true, 0, true);
								}
								$pdf->MultiCell(50, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);
							}

							$pdf->Ln(10);
							$s_sql = "SELECT * FROM collecting_interest ORDER BY date ASC";
							$o_query = $o_main->db->query($s_sql);
							$interests = ($o_query ? $o_query->result_array() : array());
							$pdf->MultiCell(35, 0, $formText_From_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(35, 0, $formText_To_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(40, 0, $formText_Interest_output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							foreach($interests as $key => $interest) {
								$interestValue = $interest['rate'];
								$fromDate = date("d.m.Y", strtotime($interest['date']));
								$toDate = "";
								if(isset($interests[$key+1])){
									$toDate = date("d.m.Y", strtotime($interests[$key+1]['date']));
								}
								$pdf->MultiCell(35, 0, $fromDate, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, $toDate, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(40, 0, $interestValue, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							}
							$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(40, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);

						}
						// ---------------------------------------------------------
						$step_name = $process_step['name'];
						$step_id = $process_step['id'];
						$sending_action = $process_step['sending_action'];

						if($profile_value['sending_action'] > 0) {
							$sending_action = $profile_value['sending_action'];
						}
						if($sending_action == 2) {
							if(preg_replace('/\xc2\xa0/', '', trim($debitor['invoiceEmail'])) == "") {
								$sending_action = 1;
							}
						}
						if($sending_action == 4) {
							if($debitor['phone'] == "") {
								if(preg_replace('/\xc2\xa0/', '', trim($debitor['invoiceEmail'])) == "") {
									$sending_action = 1;
								} else {
									$sending_action = 2;
								}
							}
						}
						//Close and save PDF document
						$s_filename = 'uploads/protected/'.$formText_Claimletter_output.'_'.$caseData['id'].'_'.time().'.pdf';
						$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');
						$sending_sql = ", sending_status = 0";
						if($sending_action == 1) {
							if($creditor['print_reminders'] == 0) {
								$sending_sql = ", sending_status = 1, performed_action = 2, performed_date = NOW()";
							}
						}

						$s_sql = "SELECT * FROM moduledata WHERE name = 'CollectingCases'";
						$o_query = $o_main->db->query($s_sql);
						$moduleInfo = ($o_query ? $o_query->row_array() : array());
						$moduleID = $moduleInfo['uniqueID'];
						if($creditor['is_demo']) {
							$sending_sql = ", sending_status = 5";
						}
						$sending_action_backup = $sending_action;
						if($creditor['activate_send_reminders_by_ehf'] && !$debitor['never_send_by_ehf']) {
							if($debitor['invoiceBy'] == 2){
								$sending_action = 5;
							}
						}
						if($debitor['prefer_email_before_print_or_ehf']) {
							if(preg_replace('/\xc2\xa0/', '', trim($debitor['invoiceEmail'])) != "") {
								$sending_action == 2;
							}
						}
						$s_sql = "INSERT INTO collecting_cases_claim_letter SET moduleID = '".$o_main->db->escape_str($moduleID)."', createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(), case_id='".$o_main->db->escape_str($caseData['id'])."', sending_action='".$o_main->db->escape_str($sending_action)."', sending_action_backup='".$o_main->db->escape_str($sending_action_backup)."', pdf='".$o_main->db->escape_str($s_filename)."', total_amount = '".$o_main->db->escape_str($totalSumDue+$totalSumPaid)."',
						due_date = '".date("Y-m-d", strtotime($dueDatePdf))."', step_name = '".$o_main->db->escape_str($step_name)."', step_id = '".$o_main->db->escape_str($step_id)."', in_english_language = '".$o_main->db->escape_str($in_english_language)."', rest_note = '".$o_main->db->escape_str($rest_note)."'".$sending_sql;
						$o_query = $o_main->db->query($s_sql);
						if($o_query){
							$claim_letter_id = $o_main->db->insert_id();

							$caseFirstLetterDates_update="";
							if($caseData['status'] == 0 || $caseData['status'] == 1) {
								if($caseData['first_reminder_letter_date'] == "0000-00-00" || $caseData['first_reminder_letter_date'] == "") {
									$caseFirstLetterDates_update .= ", first_reminder_letter_date = NOW()";
								}
							}else if($caseData['status'] == 3){
								if($caseData['first_collecting_letter_date'] == "0000-00-00"  || $caseData['first_collecting_letter_date'] == "") {
									$caseFirstLetterDates_update .= ", first_collecting_letter_date = NOW()";
								}
							}
							if($caseData['original_main_claim'] <= 0){
								$caseFirstLetterDates_update .= ", original_main_claim = '".$o_main->db->escape_str($original_main_claim)."'";
							}

							$collectedMainClaim = 0;
							$collectedInterest = 0;
							$collectedLegalCost = 0;
							$collectedVat = 0;


							$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created ASC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$payments = ($o_query ? $o_query->result_array() : array());

							foreach($payments as $payment) {
								$s_sql = "SELECT collecting_cases_payment_coverlines.*, clbc.claimline_type_category_id
								FROM collecting_cases_payment_coverlines
								LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig clbc ON clbc.id = collecting_cases_payment_coverlines.collecting_claim_line_type
								WHERE collecting_cases_payment_coverlines.collecting_cases_payment_id = ?";
								$o_query = $o_main->db->query($s_sql, array($payment['id']));
								$paymentCoverlines = $o_query ? $o_query->result_array() : array();
								foreach($paymentCoverlines as $paymentCoverline){
									if($paymentCoverline['claimline_type_category_id'] == 1) {
										$collectedMainClaim += $paymentCoverline['amount'];
									} else if($paymentCoverline['claimline_type_category_id'] == 4){
										$collectedInterest += $paymentCoverline['amount'];
									} else if($paymentCoverline['claimline_type_category_id'] == 5){
										$collectedLegalCost += $paymentCoverline['amount'];
									}
								}
							}


							$caseFirstLetterDates_update .= ", current_total_claim = '".$o_main->db->escape_str($totalSumDue+$totalSumPaid)."'";
							$caseFirstLetterDates_update .= ", collected_main_claim = '".$o_main->db->escape_str($collectedMainClaim)."'";
							$caseFirstLetterDates_update .= ", collected_interest = '".$o_main->db->escape_str($collectedInterest)."'";
							$caseFirstLetterDates_update .= ", collected_legal_cost = '".$o_main->db->escape_str($collectedLegalCost)."'";
							$caseFirstLetterDates_update .= ", collected_vat = '".$o_main->db->escape_str($collectedVat)."'";

							if($rest_note){
								if($system_settings['minimumDayUntilNextStep'] > 0){
									$currentDueDateTimestamp = strtotime($caseData['due_date']);
									$currentTimestamp = time();
									if($currentDueDateTimestamp - $currentTimestamp < 3600*24*$system_settings['minimumDayUntilNextStep']){
										$newDueDate = date("Y-m-d", strtotime("+".$system_settings['minimumDayUntilNextStep']." days"));
										$caseFirstLetterDates_update .= ", due_date = '".$o_main->db->escape_str($newDueDate)."'";
									}
								}
							}
							$s_sql = "UPDATE collecting_cases SET create_letter = 0, updated = NOW()".$caseFirstLetterDates_update." WHERE id = '".$o_main->db->escape_str($caseData['id'])."'";
							$o_query = $o_main->db->query($s_sql);

							if($code_entry_id > 0){
								$s_sql = "UPDATE collecting_cases_debitor_codes SET collecting_cases_claim_letter_id = ? WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($claim_letter_id, $code_entry_id));
							}
							$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($claim_letter_id));
							$claim_letter = ($o_query ? $o_query->row_array() : array());
							if($claim_letter){
								$v_return['item'] = $claim_letter;
							} else {
								$errors[] = $formText_LetterNotFound_output;
							}
						} else {
							$errors[] = $formText_ErrorAddingLetter_output;
						}
					} else {
						$errors[] = $formText_StepNotFound_output;
					}
				} else {
					$errors[] = $formText_TransactionNotClosed_output;
				}
			} else {
				$errors[] = $formText_InvoiceClosed_output;
								
				$s_sql = "UPDATE collecting_cases SET create_letter = 0 WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($caseData['id']));
			}
        } else {
            $errors[] = $formText_CaseNotFound_output;
        }
        if(count($errors) > 0){
            $v_return['errors'] = $errors;
        }
        return $v_return;
    }
}
if(!function_exists("generate_pdf_from_letter")){
    function generate_pdf_from_letter($letter_id, $rest_note = 0){
        global $o_main;
		global $variables;
        $errors = array();
        define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
        define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
        $v_tmp = explode("/",ACCOUNT_PATH);
        $accountname = array_pop($v_tmp);
		if(!class_exists("TCPDF")){
			include(dirname(__FILE__).'/tcpdf/tcpdf.php');
		}
        include(__DIR__.'/../languagesOutput/no.php');

        $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($letter_id));
        $letter = ($o_query ? $o_query->row_array() : array());

        if($letter){

            $s_sql = "SELECT * FROM collecting_cases WHERE id = ? ";
            $o_query = $o_main->db->query($s_sql, array($letter['case_id']));
            $caseData = ($o_query ? $o_query->row_array() : array());
            if($caseData){
                $s_sql = "SELECT creditor.* FROM creditor  WHERE creditor.id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
                $creditor = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
                $o_query = $o_main->db->query($s_sql);
                $system_settings = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
                $debitor = ($o_query ? $o_query->row_array() : array());
				if($debitor['integration_invoice_language'] == 1) {
			        include(dirname(__FILE__).'/../languagesOutput/en.php');
				}

				$customer_id = $debitor['id'];
				$s_sql = "SELECT * FROM collecting_cases_debitor_codes WHERE customer_id = ? AND collecting_cases_id = ? ORDER BY expiration_time DESC";
				$o_query = $o_main->db->query($s_sql, array($customer_id, $caseData['id']));
				$code_entry = $o_query ? $o_query->row_array() : array();
				$code = $code_entry['code'];
                $s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['id']));
                $creditor_invoice = ($o_query ? $o_query->row_array() : array());

                $case_level = "reminder";
                if($caseData['status'] == 1 || $caseData['status'] == 2) {
                    $transactionsNotClosed = false;
                    $s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='Payment' AND link_id = ? AND creditor_id = ? ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($creditor_invoice['link_id'], $creditor_invoice['creditor_id']));
                    $claim_transactions = ($o_query ? $o_query->result_array() : array());

                    foreach($claim_transactions as $claim_transaction) {
                        $sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ?";
                        $o_query = $o_main->db->query($sql, array($claim_transaction['comment']));
                        $parent_transaction = $o_query ? $o_query->row_array() : array();
                        if($parent_transaction && $parent_transaction['open']){
                            $transactionsNotClosed = true;
                        }
                    }
                } else if($caseData['status'] == 3 || $caseData['status'] == 4){
                    $transactionsNotClosed = false;
                    $case_level = "collecting";
                }
                if(!$transactionsNotClosed){
                    $s_sql = "SELECT * FROM ownercompany";
                    $o_query = $o_main->db->query($s_sql);
                    $ownercompany = ($o_query ? $o_query->row_array() : array());
                    if($case_level == "collecting"){
                        $s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
                        $process_step = ($o_query ? $o_query->row_array() : array());
                    } else {
                        $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
                        $process_step = ($o_query ? $o_query->row_array() : array());
                    }
                    if($process_step || $rest_note) {
						if($caseData['reminder_profile_id'] > 0){
							$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($caseData['reminder_profile_id']));
							$profile = $o_query ? $o_query->row_array() : array();
						}
						if(!$profile) {
							if($debitor['creditor_reminder_profile_id'] > 0){
								$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($debitor['creditor_reminder_profile_id']));
								$profile = $o_query ? $o_query->row_array() : array();
							}
							if(!$profile){
								$customer_type_collect_debitor = $debitor['customer_type_collect'];
								if($debitor['customer_type_collect_addition'] > 0){
									$customer_type_collect_debitor = $debitor['customer_type_collect_addition'] - 1;
								}							
								if($customer_type_collect_debitor == 0){
									// if($debitor['organization_type'] == "ENK") {
									// 	$customer_type_collect_debitor = 1;
									// }
								}
								if($customer_type_collect_debitor == 0){
									$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_for_company_id']));
									$profile = $o_query ? $o_query->row_array() : array();
								} else {
									$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditor['creditor_reminder_default_profile_id']));
									$profile = $o_query ? $o_query->row_array() : array();
								}
							}
						}
						$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
						$o_query = $o_main->db->query($s_sql, array($profile['id']));
						$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();

						$profile_values = array();
						foreach($unprocessed_profile_values as $unprocessed_profile_value) {
							$profile_values[$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
						}
						$profile_value = $profile_values[$process_step['id']];
						$customizedText = false;
						if($rest_note){
		                    $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
		                    $o_query = $o_main->db->query($s_sql, array($system_settings['rest_note_pdftext_id']));
		                    $pdfText = ($o_query ? $o_query->row_array() : array());
						} else {
							if($profile_value['pdftext_title'] != "" || $profile_value['pdftext_text'] != ""){
							 	$pdfText['title'] = $profile_value['pdftext_title'];
							 	$pdfText['text'] = $profile_value['pdftext_text'];
								$customizedText = true;
			                    // $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
			                    // $o_query = $o_main->db->query($s_sql, array($profile_value['collecting_cases_pdftext_id']));
			                    // $pdfText = ($o_query ? $o_query->row_array() : array());
							} else {
			                    $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
			                    $o_query = $o_main->db->query($s_sql, array($process_step['collecting_cases_pdftext_id']));
			                    $pdfText = ($o_query ? $o_query->row_array() : array());
							}
						}

						$pdfTextTitle = $pdfText['title'];
						$pdfTextMain = nl2br($pdfText['text']);

						if($debitor['integration_invoice_language'] == 1 && !$customizedText) {
							$pdfTextTitle = $pdfText['title_english'];
							$pdfTextMain = nl2br($pdfText['text_english']);
						}

						$showLogo = 0;
						if($process_step['show_collecting_company_logo']) {
							$showLogo = 1;
						}
						if($profile_value['show_collecting_company_logo'] > 0){
							if($profile_value['show_collecting_company_logo'] == 2) {
								$showLogo = 1;
							} else {
								$showLogo = 0;
							}
						}

                        $s_sql = "SELECT * FROM claim_letter_bottomtext";
                        $o_query = $o_main->db->query($s_sql);
                        $bottomText = ($o_query ? $o_query->row_array() : array());

                        if(intval($process_step['bank_account_choice']) == 0) {
                            $bankAccount = $creditor['bank_account'];
                            $kidNumber = $creditor_invoice['kid_number'];
                        } else if (intval($process_step['bank_account_choice']) == 1) {
                            $bankAccount = $ownercompany['companyaccount'];
                            //TODO generation of kid number
                            $kidNumber = "";
                        }
						$currencyName = "";
						if($creditor_invoice['currency'] == 'LOCAL') {
							$currencyName = trim($creditor['default_currency']);
						} else {
							$currencyName = trim($creditor_invoice['currency']);
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
                    	$pdf->SetFont('calibri', '', 11);

                        $reminderingFromCreditor = false;
                        $addCodeOnPdf = false;
                        if(!$creditor['addCreditorPortalCodeOnLetter'] || intval($creditor['send_reminder_from']) == 1 || intval($process_step['status_id']) == 3) {
                            $addCodeOnPdf = true;
                        }
                        if((intval($process_step['status_id']) == 0 || intval($process_step['status_id']) == 1) && intval($creditor['send_reminder_from']) == 0) {
                            $reminderingFromCreditor = true;
                        }
                        if($reminderingFromCreditor) {
                            $logoImage = json_decode($creditor['invoicelogo']);
                            $companyNamePdf = $creditor['companyname'];
                            $companyAddress = $creditor['companypostalbox'].", ".$creditor['companyzipcode']." ".$creditor['companypostalplace'];
                            $companyPhone = $creditor['companyphone'];
                            $companyOrgNr = $creditor['companyorgnr'];
                            $companyEmail = $creditor['companyEmail'];
							if($creditor['use_local_email_phone_for_reminder']) {
								$companyPhone = $creditor['local_phone'];
		                        $companyEmail = $creditor['local_email'];
							}
                        } else {
                            $logoImage = json_decode($ownercompany['invoicelogo']);
                            $companyNamePdf = $ownercompany['companyname'];
                            $companyAddress = $ownercompany['companypostalbox'].", ".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
                            $companyPhone = $ownercompany['companyphone'];
                            $companyOrgNr = $ownercompany['companyorgnr'];
                            $companyEmail = $ownercompany['companyEmail'];
                        }

						$addedLogo = false;
                        if(count($logoImage) > 0){
							$pdf->SetY(3);
                            $imageLocation = ACCOUNT_PATH."/".$logoImage[0][1][0];
                            $ext = end(explode(".", $imageLocation));
                            $image = base64_encode(file_get_contents($imageLocation));
							$pdf->writeHTML('<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" height="70" />', true, false, true, false, '');
							
						} else {

                        }

                        $pdf->SetY(20);
                    	$pdf->MultiCell(70, 0, $companyNamePdf, 0, 'L', 0, 1, 125, '', true, 0, true);
                    	$pdf->MultiCell(70, 0, $companyAddress, 0, 'L', 0, 1, 125, '', true, 0, true);
                    	$pdf->MultiCell(70, 0, $formText_OrgNr_pdf." ".$companyOrgNr, 0, 'L', 0, 1, 125, '', true, 0, true);
                    	$pdf->MultiCell(70, 0, $formText_Phone_pdf." ".$companyPhone, 0, 'L', 0, 1, 125, '', true, 0, true);
                    	$pdf->MultiCell(70, 0, $formText_Email_pdf." ".$companyEmail, 0, 'L', 0, 1, 125, '', true, 0, true);
                    	$pdf->Ln(2);
                    	$pdf->MultiCell(70, 0, $formText_Date_pdf." ".date("d.m.Y", strtotime($letter['created'])), 0, 'L', 0, 1, 125, '', true, 0, true);
                        $dueDatePdf = date("d.m.Y", strtotime($caseData['due_date']));
						// if($rest_note){
						// 	$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$formText_Immediately_output."", 0, 'L', 0, 1, 125, '', true, 0, true);
						// } else {
							$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$dueDatePdf."", 0, 'L', 0, 1, 125, '', true, 0, true);
						// }

                    	// $pdf->MultiCell(50, 0, $formText_RegistrationNumber_pdf, 0, 'R', 0, 0, 100, '', true, 0, true);
                    	// $pdf->MultiCell(30, 0, $ownercompany['companyorgnr'], 0, 'R', 0, 1, 165, '', true, 0, true);


						$pdf->SetFont('calibri', '', 9);
						$pdf->SetX(20);

						if($addedLogo){
							$pdf->SetY(40);
							$lineBreakAfter = 60;
							$linesAfterInfo = 16;
							$linesForOflowLogo = 9;
						} else {
							$pdf->SetY(20);
							$linesAfterInfo = 36;
							$lineBreakAfter = 40;
							$linesForOflowLogo = 29;
						}
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
	                    		$pdf->Ln($linesForOflowLogo);
								$ext = end(explode(".", $imageLocation));
								$image = base64_encode(file_get_contents($imageLocation));
								$pdf->writeHTMLCell(80, 0, 125, $y+$linesForOflowLogo, '<div style="text-alight: left;">'.$formText_OurPartnerOnCollectingCases_output.':<br/><img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="140" /></div>', 0, 0, 0, true,'', true);
								// $pdf->writeHTML('<div style="text-alight: left;">'.$formText_OurPartnerOnCollectingCases_output.':<br/><img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="140" /></div>', true, false, true, false, 'L');
								// $linesAfterInfo = 0;
							}
						}
						$pdf->SetY($y);
                    	$pdf->Ln($linesAfterInfo);

                        $pdf->SetFont('calibri', '', 11);
						$project_name = $creditor_invoice['project_name'];
						if($creditor['activate_project_code_in_reminderletter'] && $project_name != "") {
							$pdf->MultiCell(55, 0, "".$formText_Reference_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $project_name, 0, 'L', 0, 1, '', '', true, 0, true);
						}
                        if(!$reminderingFromCreditor){
                        	$pdf->MultiCell(55, 0, "".$formText_CreditorName_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(0, 0, "<b>".$creditor['companyname']."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
                            $pdf->SetFont('calibri', '', 11);
                        	$pdf->MultiCell(55, 0, "".$formText_CaseId_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(0, 0, $caseData['id'], 0, 'L', 0, 1, '', '', true, 0, true);
                        }
                        if($system_settings['debitor_portal_url'] != "" && $addCodeOnPdf) {
                            $pdf->MultiCell(55, 0, $formText_LoginToYourCaseHere.": ", 0, 'L', 0, 0, '', '', true, 0, true);
                            $pdf->Write(0, $system_settings['debitor_portal_url'], $system_settings['debitor_portal_url'], false, 'L', true);
                            $pdf->MultiCell(55, 0, "".$formText_UseThisCodeWhenLogin_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
                            $pdf->MultiCell(0, 0, "<b>".$code."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
                        }

                        $pdf->Ln(5);
                        $pdf->SetFont('calibri', 'b', 15);
                    	$pdf->MultiCell(0, 0, $pdfTextTitle, 0, 'L', 0, 1, '', '', true, 0, true);
                        $pdf->SetFont('calibri', '', 11);
                    	$pdf->MultiCell(0, 0, $pdfTextMain, 0, 'L', 0, 1, '', '', true, 0, true);

                    	$pdf->Ln(10);


                    	$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
                    	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
                    	$invoice = ($o_query ? $o_query->row_array() : array());

                        $totalSumPaid = 0;
                        $totalSumDue = 0;

                        $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='Payment' AND link_id = ? AND creditor_id = ? ORDER BY created DESC";
                        $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
                        $payments = ($o_query ? $o_query->result_array() : array());

                        foreach($payments as $payment) {
                            $totalSumPaid += $payment['amount'];
                        }

						$connected_transactions = array();
						$all_connected_transaction_ids = array($invoice['id']);
						if($invoice['link_id'] > 0 && ($creditor['checkbox_1'])) {
							$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
							$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['id']));
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

	                    $totalSumDue += $invoice['collecting_case_original_claim'];
						foreach($connected_transactions as $connected_transaction) {
							$totalSumDue += $connected_transaction['amount'];
						}
                        $s_sql = "SELECT creditor_transactions.*
                            FROM creditor_transactions WHERE  system_type = 'CreditnoteCustomer' AND creditor_transactions.link_id = ? AND creditor_transactions.creditor_id = ?
						ORDER BY created DESC";
                    	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
                    	$creditnotes = ($o_query ? $o_query->result_array() : array());

                        foreach($creditnotes as $creditnote) {
							if(!in_array($creditnote['id'], $all_connected_transaction_ids)){
		                        $totalSumDue += $creditnote['amount'];
							}
                        }
                        //includes invoice + payments done before case is created
                        $original_main_claim = $totalSumDue;

                    	$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
                    	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
                    	$claims = ($o_query ? $o_query->result_array() : array());

        				$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
						$claim_transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($claim_transactions as $claim) {
                            $totalSumDue += $claim['amount'];
                        }
                        foreach($claims as $claim) {
                            $totalSumDue += $claim['amount'];
                        }

                    	$pdf->setCellPaddings(2, 2, 2, 2);

                    	$pdf->SetFillColor(255, 255, 255);

                        // $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

                        $pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));

                    	//lastRow
                    	// $pdf->SetFillColor(254, 209, 71);
                        $topBordered = false;

                        if($invoice){
                            $topBordered = true;
                            $claimAmount = number_format($invoice['collecting_case_original_claim'], 2, ",", " ");
                            $invoiceDueText = "";
                            if($invoice['due_date'] != "0000-00-00" && $invoice['due_date'] != ""){
                                $dueDate = date("d.m.Y", strtotime($invoice['due_date']));
                                $invoiceDueText = " - ".$formText_DueDate_output." ".$dueDate;
                            }
                        	$pdf->MultiCell(120, 0, $formText_InvoiceNumber_output." ".$invoice['invoice_nr'].$invoiceDueText, 'LT', 'L', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(46, 0, $claimAmount, 'T', 'R', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $currencyName, 'RT', 'R', 1, 1, '', '', true, 0, true);
                            foreach($payments as $payment){
                                $pdf->MultiCell(120, 0, $formText_Payment_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
                            	$pdf->MultiCell(46, 0, number_format($payment['amount'], 2, ",", " "), '', 'R', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $currencyName, 'R', 'R', 1, 1, '', '', true, 0, true);
                            }
							foreach($creditnotes as $payment){
                                $pdf->MultiCell(120, 0, $formText_CreditNote_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
                            	$pdf->MultiCell(46, 0, number_format($payment['amount'], 2, ",", " "), '', 'R', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $currencyName, 'R', 'R', 1, 1, '', '', true, 0, true);
                            }
							foreach($connected_transactions as $connected_transaction) {
		                        $invoiceDueText = "";
		                        if($connected_transaction['due_date'] != "0000-00-00" && $connected_transaction['due_date'] != ""){
		                            $dueDate_connected = date("d.m.Y", strtotime($connected_transaction['due_date']));
		                            $invoiceDueText = " - ".$formText_DueDate_output." ".$dueDate_connected;
		                        }
								$invoice_label = $formText_ExtraInvoiceConnected_output;
								if($connected_transaction['system_type'] == "CreditnoteCustomer") {
									$invoice_label = $formText_CreditNote_output;
								}
								$pdf->MultiCell(120, 0, $invoice_label." ".$connected_transaction['invoice_nr'].$invoiceDueText, 'L', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(46, 0, number_format($connected_transaction['amount'], 2, ",", " "), '', 'R', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $currencyName, 'R', 'R', 1, 1, '', '', true, 0, true);
							}
                        }
                        foreach($claim_transactions as $claim_transaction) {
                            $claim_text_array = explode("_", $claim_transaction['comment']);
                            if($topBordered){
                                $border = "L";
                                $border2 = "R";
								$border3 = "";
                            } else {
                                $border = "LT";
                                $border2 = "RT";
								$border3 = "T";
                                $topBordered = true;
                            }
							$claim_name = $claim_text_array[0];
							$claim_name_array = explode(" ", $claim_name);
							if($in_english_language) {
								foreach($claim_name_array as $key=> $claim_name_single){
									$s_sql = "SELECT * FROM collecting_cases_claim_line_translations WHERE base_text='".$o_main->db->escape_str($claim_name_single)."' AND language = 1";
									$o_query = $o_main->db->query($s_sql);
									$translation = ($o_query ? $o_query->row_array() : array());
									if($translation) {
										$claim_name_array[$key] = $translation['translated_text'];
									}
								}
							}
							$claimAmount = number_format($claim_transaction['amount'], 2, ",", " ");
							$pdf->MultiCell(120, 0, implode(" ", $claim_name_array), $border, 'L', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(46, 0, $claimAmount, $border3, 'R', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
                        }
                        foreach($claims as $claim) {
                            if($topBordered){
                                $border = "L";
                                $border2 = "R";
								$border3 = "";
                            } else {
                                $border = "LT";
                                $border2 = "RT";
								$border3 = "T";
                                $topBordered = true;
                            }
							$claim_name = $claim['name'];
							$claim_name_array = explode(" ", $claim_name);
							if($in_english_language) {
								foreach($claim_name_array as $key=> $claim_name_single){
									$s_sql = "SELECT * FROM collecting_cases_claim_line_translations WHERE base_text='".$o_main->db->escape_str($claim_name_single)."' AND language = 1";
									$o_query = $o_main->db->query($s_sql);
									$translation = ($o_query ? $o_query->row_array() : array());
									if($translation) {
										$claim_name_array[$key] = $translation['translated_text'];
									}
								}
							}
                            $claimAmount = number_format($claim['amount'], 2, ",", " ");
							$pdf->MultiCell(120, 0, implode(" ", $claim_name_array), $border, 'L', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(46, 0, $claimAmount, $border3, 'R', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
                        }

                    	$pdf->MultiCell(120, 0, $formText_AmountToPay_pdf, 'TLB', 'L', 1, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(46, 0, number_format($totalSumDue+$totalSumPaid, 2, ",", " "), 'TB', 'R', 1, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(0, 0, $currencyName, 'TRB', 'R', 1, 1, '', '', true, 0, true);

                    	$pdf->Ln(5);

                    	$pdf->SetFont('calibri', '', 11);

                    	$pdf->setCellPaddings(0, 0, 0, 0);
                        $pdf->MultiCell(0, 0, "<b>".$formText_BankAccount_pdf.":</b> ".$bankAccount, 0, 'L', 0, 1, '', '', true, 0, true);
                        if($kidNumber != "") {
                            $pdf->MultiCell(0, 0, "<b>".$formText_KidNumber_pdf.":</b> ".$kidNumber, 0, 'L', 0, 1, '', '', true, 0, true);
                        }
						// if($rest_note){
						// 	$pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$formText_Immediately_output, 0, 'L', 0, 1, '', '', true, 0, true);
						// } else {
		                    $pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$dueDatePdf, 0, 'L', 0, 1, '', '', true, 0, true);
						// }
                        $pdf->MultiCell(0, 0, "<b>".$formText_AmountDue_pdf.":</b> ".number_format($totalSumDue+$totalSumPaid, 2, ",", " ")." ".$currencyName, 0, 'L', 0, 1, '', '', true, 0, true);

                        $pdf->Ln(15);

                        $pdf->MultiCell(0, 0, $formText_BestRegards_pdf, 0, 'L', 0, 1, '', '', true, 0, true);
                        $pdf->MultiCell(0, 0, "<b>".$companyNamePdf."</b>", 0, 'L', 0, 1, '', '', true, 0, true);

						if($case_level == "collecting") {
							$pdf->AddPage();
							$topBordered = false;
						   	$pdf->setCellPaddings(2, 2, 2, 2);
						   	$pdf->SetFillColor(255, 255, 255);

						   	// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');
						   	if(count($main_claims) > 0) {
							   	$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
								$pdf->MultiCell(50, 0, $formText_InvoiceNr_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, $formText_InvoiceDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, $formText_InvoiceDueDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $formText_MainClaim_output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							   	foreach($main_claims as $main_claim){
								   	$mainClaimTotal = number_format($main_claim['amount'], 2, ".", " ");
		   						   	$pdf->MultiCell(50, 0, $main_claim['invoice_nr'], 'LT', 'L', 1, 0, '', '', true, 0, true);
	 	   						   	$pdf->MultiCell(35, 0, date("d.m.Y", strtotime($main_claim['date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
	 	   						   	$pdf->MultiCell(35, 0, date("d.m.Y", strtotime($main_claim['original_due_date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
		   						   	$pdf->MultiCell(0, 0, $mainClaimTotal, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							   	}
								$pdf->MultiCell(50, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);
							}

	                        $pdf->Ln(10);
                            $s_sql = "SELECT * FROM collecting_interest ORDER BY date ASC";
                            $o_query = $o_main->db->query($s_sql);
                            $interests = ($o_query ? $o_query->result_array() : array());
							$pdf->MultiCell(35, 0, $formText_From_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(35, 0, $formText_To_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(40, 0, $formText_Interest_output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							foreach($interests as $key => $interest) {
								$interestValue = $interest['rate'];
								$fromDate = date("d.m.Y", strtotime($interest['date']));
								$toDate = "";
								if(isset($interests[$key+1])){
									$toDate = date("d.m.Y", strtotime($interests[$key+1]['date']));
								}
								$pdf->MultiCell(35, 0, $fromDate, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(35, 0, $toDate, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(40, 0, $interestValue, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							}
							$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(40, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);

						}

                    	// ---------------------------------------------------------
                		//Close and save PDF document
                        // if($letter['pdf'] != ""){
                    	// 	$s_filename = $letter['pdf'];
                        // } else {
                    		$s_filename = 'uploads/protected/'.$formText_Claimletter_output.'_'.$caseData['id'].'_'.time().'.pdf';
                        // }
                		$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');
                        $s_sql = "UPDATE collecting_cases_claim_letter SET pdf='".$o_main->db->escape_str($s_filename)."' WHERE id = '".$o_main->db->escape_str($letter['id'])."'";
                        $o_query = $o_main->db->query($s_sql);
                        if($o_query){
                            $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($letter['id']));
                            $claim_letter = ($o_query ? $o_query->row_array() : array());
                            if($claim_letter){
                                $v_return['item'] = $claim_letter;
                            } else {
                                $errors[] = $formText_LetterNotFound_output;
                            }
                        } else {
                            $errors[] = $formText_ErrorAddingLetter_output;
                        }
                    } else {
                    	$errors[] = $formText_StepNotFound_output;
                    }
                } else {
                    $errors[] = $formText_TransactionNotClosed_output;
                }
            } else {
                $errors[] = $formText_CaseNotFound_output;
            }
        } else {
            $errors[] = $formText_CaseNotFound_output;
        }
        if(count($errors) > 0){
            $v_return['errors'] = $errors;
        }
        return $v_return;
    }
}
?>
