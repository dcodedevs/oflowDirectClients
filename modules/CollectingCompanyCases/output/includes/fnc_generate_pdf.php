<?php
if(!function_exists("generateRandomString")){
    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}
if(!function_exists("generate_pdf")){
    function generate_pdf($caseId, $rest_note = 0, $summary = 0, $single_task_array = array()){
        global $o_main;
		global $variables;
        $errors = array();
        define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
        define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
        $v_tmp = explode("/",ACCOUNT_PATH);
        $accountname = array_pop($v_tmp);
		if(!class_exists("TCPDF")) {
	        include(dirname(__FILE__).'/tcpdf/tcpdf.php');
		}
        include(dirname(__FILE__).'/../languagesOutput/no.php');
		$single_letter = 0;
		if($single_task_array) {
			$single_letter = 1;
		}
		if($rest_note || $summary || $single_task_array) {
	        $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
	        $o_query = $o_main->db->query($s_sql, array($caseId));
	        $caseData = ($o_query ? $o_query->row_array() : array());
		} else {
	        $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ? AND create_letter = 1";
	        $o_query = $o_main->db->query($s_sql, array($caseId));
	        $caseData = ($o_query ? $o_query->row_array() : array());
		}

        if($caseData)
        {
            $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
            $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
            $creditor = ($o_query ? $o_query->row_array() : array());

            $s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
            $o_query = $o_main->db->query($s_sql);
            $system_settings = ($o_query ? $o_query->row_array() : array());

            $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
            $o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
            $debitor = ($o_query ? $o_query->row_array() : array());
			if($debitor['extra_language'] == 1){
		        include(dirname(__FILE__).'/../languagesOutput/en.php');
			}
            $case_level = "reminder";
            if($caseData['status'] == 1 || $caseData['status'] == 2) {
                $s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['id']));
                $creditor_invoice = ($o_query ? $o_query->row_array() : array());

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
                $s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
                $process_step = ($o_query ? $o_query->row_array() : array());
                if($process_step || $rest_note || $single_task_array) {
					$extra_type_text = array();
					if($process_step){
						$s_sql = "SELECT * FROM creditor_collecting_company_letter_type_text WHERE collecting_company_letter_type_id = ? AND creditor_id = ?";
						$o_query = $o_main->db->query($s_sql, array($process_step['collecting_company_letter_type_id'], $caseData['creditor_id']));
						$extra_type_text = ($o_query ? $o_query->row_array() : array());
					}
					if($rest_note) {
	                    $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
	                    $o_query = $o_main->db->query($s_sql, array($system_settings['rest_note_pdftext_id']));
	                    $pdfText = ($o_query ? $o_query->row_array() : array());
					} else if($single_task_array) {
						$pdfText = $single_task_array['collecting_case_pdftext'];
					} else {
	                    $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
	                    $o_query = $o_main->db->query($s_sql, array($process_step['collecting_cases_pdftext_id']));
	                    $pdfText = ($o_query ? $o_query->row_array() : array());
					}
					$pdfTextTitle = $pdfText['title'];
					$pdfTextMain = nl2br($pdfText['text']);

					if($debitor['extra_language'] == 1){
						$pdfTextTitle = $pdfText['title_english'];
						$pdfTextMain = nl2br($pdfText['text_english']);
					}

                    $s_sql = "SELECT * FROM claim_letter_bottomtext";
                    $o_query = $o_main->db->query($s_sql);
                    $bottomText = ($o_query ? $o_query->row_array() : array());

                    if(intval($process_step['bank_account_choice']) == 0) {
                        $bankAccount = $creditor['bank_account'];
                    } else if (intval($process_step['bank_account_choice']) == 1) {
                        $bankAccount = $ownercompany['companyaccount'];
                    }
					$kidNumber = $caseData['kid_number'];


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
                        customer_id = ?, code= ?, expiration_time = ?, collecting_company_case_id = ?";
                        $o_query = $o_main->db->query($s_sql, array($customer_id, $code, $expiration_time, $caseData['id']));
                        $code_entry_id = $o_main->db->insert_id();
                    }

                	// add a page
                	$pdf->AddPage();


                	setlocale(LC_TIME, 'no_NO');
                	$pdf->SetFont('calibri', '', 11);

                    // $reminderingFromCreditor = false;
                    // $addCodeOnPdf = false;
                    // if($creditor['addCreditorPortalCodeOnLetter'] || intval($creditor['send_reminder_from']) == 1 || intval($process_step['status_id']) == 3) {
                    //     $addCodeOnPdf = true;
                    // }
                    // if((intval($process_step['status_id']) == 0 || intval($process_step['status_id']) == 1) && intval($creditor['send_reminder_from']) == 0) {
                    //     $reminderingFromCreditor = true;
                    // }

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
					if($summary) {
						$pdf->MultiCell(70, 0, $formText_BankAccount_pdf." ".$bankAccount, 0, 'L', 0, 1, 125, '', true, 0, true);
					}
					if($companyIban != "") {
	                	$pdf->MultiCell(70, 0, $formText_Iban_pdf." ".$companyIban, 0, 'L', 0, 1, 125, '', true, 0, true);
					}
					if($companySwift != "") {
	                	$pdf->MultiCell(70, 0, $formText_Swift_pdf." ".$companySwift, 0, 'L', 0, 1, 125, '', true, 0, true);
					}
					$pdf->Ln(2);
					$pdf->MultiCell(70, 0, $formText_Date_pdf." ".date("d.m.Y"), 0, 'L', 0, 1, 125, '', true, 0, true);
					$dueDatePdf = date("d.m.Y", strtotime($caseData['due_date']));
					if(!$summary) {
						if($rest_note){
							$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$formText_Immediately_output."", 0, 'L', 0, 1, 125, '', true, 0, true);
						} else if($single_task_array) {
							$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$dueDatePdf."", 0, 'L', 0, 1, 125, '', true, 0, true);
						} else {
							$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$dueDatePdf."", 0, 'L', 0, 1, 125, '', true, 0, true);
						}
					}

					$pdf->SetFont('calibri', '', 9);
					$pdf->SetX(20);
					$pdf->SetY(20);
					$pdf->MultiCell(80, 0, $formText_Return_output.": ". $companyNamePdf.", ".$companyAddress, 0, 'L', 0, 1, '', '', true, 0, true);

					$pdf->SetFont('calibri', '', 11);
					$pdf->SetX(20);
					$pdf->SetY(40);

					$pdf->MultiCell(100, 0, $debitor['extraName'], 0, 'L', 0, 1, '', '', true, 0, true);
					$pdf->MultiCell(100, 0, $debitor['extraStreet'], 0, 'L', 0, 1, '', '', true, 0, true);
					$pdf->MultiCell(100, 0, $debitor['extraPostalNumber']." ".$debitor['extraCity'], 0, 'L', 0, 1, '', '', true, 0, true);
					$pdf->MultiCell(100, 0, $debitor['extraCountry'], 0, 'L', 0, 1, '', '', true, 0, true);

					$pdf->Ln(26);


					if(!$summary){
	                    $pdf->SetFont('calibri', '', 10);
	                	$pdf->MultiCell(55, 0, "<b>".$formText_CreditorName_pdf.": </b>", 0, 'L', 0, 0, '', '', true, 0, true);
	                	$pdf->MultiCell(0, 0, "".$creditor['companyname']."", 0, 'L', 0, 1, '', '', true, 0, true);
	                	$pdf->MultiCell(55, 0, "<b>".$formText_CaseId_pdf.": </b>", 0, 'L', 0, 0, '', '', true, 0, true);
	                	$pdf->MultiCell(0, 0, $caseData['id'], 0, 'L', 0, 1, '', '', true, 0, true);

	                    if($system_settings['debitor_portal_url'] != "") {
	                        $pdf->MultiCell(55, 0, "<b>".$formText_LoginToYourCaseHere.":</b> ", 0, 'L', 0, 0, '', '', true, 0, true);
	                        $pdf->Write(0, $system_settings['debitor_portal_url'], $system_settings['debitor_portal_url'], false, 'L', true);
	                        $pdf->MultiCell(55, 0, "<b>".$formText_UseThisCodeWhenLogin_pdf.":</b> ", 0, 'L', 0, 0, '', '', true, 0, true);
	                        $pdf->MultiCell(0, 0, "".$code."", 0, 'L', 0, 1, '', '', true, 0, true);
	                    }
	                    $pdf->Ln(5);
	                    $pdf->SetFont('calibri', 'b', 15);
	                	$pdf->MultiCell(0, 0, $pdfTextTitle, 0, 'L', 0, 1, '', '', true, 0, true);
	                    $pdf->SetFont('calibri', '', 10);
	                	$pdf->MultiCell(0, 0, $pdfTextMain, 0, 'L', 0, 1, '', '', true, 0, true);

						if($extra_type_text){
							$pdf->Ln(2);
							$pdf->SetFont('calibri', '', 10);
							$pdf->MultiCell(0, 0, $extra_type_text['text'], 0, 'L', 0, 1, '', '', true, 0, true);
						}
	                	$pdf->Ln(7);

					} else {
						$pdf->SetFont('calibri', '', 10);
	                	$pdf->MultiCell(0, 0, "<b>".$formText_CreditorName_pdf.": </b> ".$creditor['companyname'], 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, "<b>".$formText_CaseId_pdf.": </b> ".$caseData['id'], 0, 'L', 0, 1, '', '', true, 0, true);
						if($kidNumber != "") {
	                        $pdf->MultiCell(0, 0, "<b>".$formText_KidNumber_pdf.":</b> ".$kidNumber, 0, 'L', 0, 1, '', '', true, 0, true);
	                    }
	                	$pdf->Ln(7);
					}

					$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($caseData['id']));
					$payments = ($o_query ? $o_query->result_array() : array());

					$totalSumPaid = 0;
					$totalSumPaidCollecting = 0;
					$totalSumDue = 0;

					foreach($payments as $payment) {
						$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
						$o_query = $o_main->db->query($s_sql);
						$transactions = ($o_query ? $o_query->result_array() : array());
						foreach($transactions as $transaction) {
							$totalSumPaidCollecting += $transaction['amount'];
						}
					}
					if($totalSumPaidCollecting > 0){
						$totalSumPaidCollecting = $totalSumPaidCollecting*(-1);
					}
					$totalSumPaid+=$totalSumPaidCollecting;

					$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? AND claim_type = 1 ORDER BY claim_type ASC, created DESC";
					$o_query = $o_main->db->query($s_sql, array($caseData['id']));
					$main_claims = ($o_query ? $o_query->result_array() : array());
					$mainClaimTotal = 0;
					foreach($main_claims as $main_claim){
						$mainClaimTotal += $main_claim['amount'];
					}
					//includes invoice + payments done before case is created
					$original_main_claim = $mainClaimTotal;
					$totalSumDue = $original_main_claim;

					$s_sql = "SELECT cccl.*, bconfig.type_name as claim_line_type_name FROM collecting_company_cases_claim_lines cccl
					LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
					WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0 AND claim_type <> 1
					ORDER BY cccl.claim_type ASC, cccl.created DESC";
					$o_query = $o_main->db->query($s_sql, array($caseData['id']));
					$claims = ($o_query ? $o_query->result_array() : array());

					foreach($claims as $claim) {
						if(!$claim['payment_after_closed']) {
							$totalSumDue += $claim['amount'];
						}
					}

					$pdf->setCellPaddings(2, 2, 2, 2);

					$pdf->SetFillColor(255, 255, 255);

					// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

					$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));

					//lastRow
					// $pdf->SetFillColor(254, 209, 71);
					$topBordered = false;
					$pdf->setCellPaddings(2, 2, 2, 2);

					$pdf->SetFillColor(255, 255, 255);

					// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

					$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
					$currencyName = " NOK";
					//lastRow
					// $pdf->SetFillColor(254, 209, 71);
                    $pdf->SetFont('calibri', '', 9);
					if($caseData['currency_explanation_text'] != ""){
						$pdf->MultiCell(0, 0, $caseData['currency_explanation_text'], 'TRLB', 'L', 1, 1, '', '', true, 0, true);
					}
                    $pdf->SetFont('calibri', '', 10);
					$topBordered = false;
					$topBordered = true;
					$claimAmount = number_format($mainClaimTotal, 2, ",", " ");
					$pdf->MultiCell(120, 0, $formText_MainClaim_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
					$pdf->MultiCell(46, 0, $claimAmount, 'T', 'R', 1, 0, '', '', true, 0, true);
					$pdf->MultiCell(0, 0, $currencyName, 'RT', 'R', 1, 1, '', '', true, 0, true);

					// foreach($payments as $payment){
					// 	$paymentAmount = 0;
					// 	$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
					// 	$o_query = $o_main->db->query($s_sql);
					// 	$transactions = ($o_query ? $o_query->result_array() : array());
					// 	foreach($transactions as $transaction) {
					// 		$paymentAmount += $transaction['amount'];
					// 	}
					// 	$pdf->MultiCell(120, 0, $formText_Payment_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
					// 	$pdf->MultiCell(46, 0, number_format($paymentAmount, 2, ",", " "), '', 'R', 1, 0, '', '', true, 0, true);
					// 	$pdf->MultiCell(0, 0, $currencyName, 'R', 'R', 1, 1, '', '', true, 0, true);
					// }
					// foreach($invoices as $invoice) {
					// 	$s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%' ORDER BY created DESC";
					// 	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
					// 	$claim_transactions = ($o_query ? $o_query->result_array() : array());
					// 	foreach($claim_transactions as $claim_transaction) {
					// 		$claim_text_array = explode("_", $claim_transaction['comment']);
					// 		if($topBordered){
					// 			$border = "L";
					// 			$border2 = "R";
					// 			$border3 = "";
					// 		} else {
					// 			$border = "LT";
					// 			$border2 = "RT";
					// 			$border3 = "T";
					// 			$topBordered = true;
					// 		}
					// 		$pdf->MultiCell(120, 0, $claim_text_array[0], $border, 'L', 1, 0, '', '', true, 0, true);
					// 		$pdf->MultiCell(46, 0, number_format($claim_transaction['amount'], 2, ",", " "), $border3, 'R', 1, 0, '', '', true, 0, true);
					// 		$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
					// 	}
					// }
					foreach($claims as $claim) {
						if(!$claim['payment_after_closed']) {
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
							$pdf->MultiCell(120, 0, $claim['name'], $border, 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(46, 0, number_format($claim['amount'], 2, ",", " "), $border3, 'R', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
						}
					}



                    if($totalSumPaidCollecting < 0){
                        $pdf->MultiCell(120, 0, $formText_TotalSumPaid_Output, 'TLB', 'L', 1, 0, '', '', true, 0, true);
                        $pdf->MultiCell(46, 0, number_format($totalSumPaidCollecting, 2, ",", " "), 'TB', 'R', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, $currencyName, 'TRB', 'R', 1, 1, '', '', true, 0, true);
                    }

                	$pdf->MultiCell(120, 0, $formText_AmountToPay_pdf, 'TLB', 'L', 1, 0, '', '', true, 0, true);
                	$pdf->MultiCell(46, 0, number_format($totalSumDue+$totalSumPaid, 2, ",", " "), 'TB', 'R', 1, 0, '', '', true, 0, true);
					$pdf->MultiCell(0, 0, $currencyName, 'TRB', 'R', 1, 1, '', '', true, 0, true);

                	$pdf->Ln(5);

                	$pdf->SetFont('calibri', '', 11);
					$dueDatePdf = date("d.m.Y", strtotime($caseData['due_date']));
					if(!$summary) {
	                	$pdf->setCellPaddings(0, 0, 0, 0);
	                    $pdf->MultiCell(0, 0, "<b>".$formText_BankAccount_pdf.":</b> ".$bankAccount, 0, 'L', 0, 1, '', '', true, 0, true);
	                    if($kidNumber != "") {
	                        $pdf->MultiCell(0, 0, "<b>".$formText_KidNumber_pdf.":</b> ".$kidNumber, 0, 'L', 0, 1, '', '', true, 0, true);
	                    }
						if($rest_note){
							$pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$formText_Immediately_output, 0, 'L', 0, 1, '', '', true, 0, true);
						} else if($single_task_array) {
							$pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$dueDatePdf, 0, 'L', 0, 1, '', '', true, 0, true);
						} else{
		                    $pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$dueDatePdf, 0, 'L', 0, 1, '', '', true, 0, true);
						}
						$pdf->MultiCell(0, 0, "<b>".$formText_AmountDue_pdf.":</b> ".number_format($totalSumDue+$totalSumPaid, 2, ",", " ")." ".$currencyName, 0, 'L', 0, 1, '', '', true, 0, true);

	                    $pdf->Ln(10);

	                    $pdf->MultiCell(0, 0, $formText_BestRegards_pdf, 0, 'L', 0, 1, '', '', true, 0, true);
	                    $pdf->MultiCell(0, 0, "<b>".$companyNamePdf."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
					}


					$pdf->AddPage();
					$topBordered = false;
					$pdf->setCellPaddings(1,1,1,1);
					$pdf->SetFillColor(255, 255, 255);

					// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');
					if(count($main_claims) > 0) {
						$pdf->SetFont('calibri', 'b', 11);
						$pdf->MultiCell(0, 0, $formText_MainClaimDetails_output, 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->SetFont('calibri', '', 9);
						$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
						$pdf->MultiCell(20, 0, $formText_InvoiceNr_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(30, 0, $formText_InvoiceDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(30, 0, $formText_InvoiceDueDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
						// $pdf->MultiCell(35, 0, $formText_CollectWarningDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(30, 0, $formText_OriginalAmount_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, $formText_MainClaim_output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
						foreach($main_claims as $main_claim){
							$collect_warning_date = "";
							if($main_claim['collect_warning_date'] != "0000-00-00" && $main_claim['collect_warning_date'] != ""){
								$collect_warning_date = date("d.m.Y", strtotime($main_claim['collect_warning_date']));
							}
							$pdf->MultiCell(20, 0, $main_claim['invoice_nr'], 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, date("d.m.Y", strtotime($main_claim['date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, date("d.m.Y", strtotime($main_claim['original_due_date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
							// $pdf->MultiCell(35, 0, $collect_warning_date, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, number_format($main_claim['original_amount'], 2, ",", " "), 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, number_format($main_claim['amount'], 2, ",", " "), 'LRT', 'R', 1, 1, '', '', true, 0, true);
						}
						$pdf->MultiCell(20, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(30, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(30, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(30, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);
					}

					$s_sql = "SELECT * FROM collecting_cases_interest_calculation
					WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."'
					ORDER BY created ASC";
					$o_query = $o_main->db->query($s_sql);
					$collecting_cases_interest_calculations = ($o_query ? $o_query->result_array() : array());
					if(count($collecting_cases_interest_calculations) > 0) {
						$pdf->Ln(10);
						$pdf->SetFont('calibri', 'b', 11);
						$pdf->MultiCell(0, 0, $formText_InterestDetailedInformation_output, 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->SetFont('calibri', '', 9);
						$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
						$pdf->MultiCell(120, 0, $formText_Name_Output, 'LT', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(20, 0, $formText_Rate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, $formText_Amount_Output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
						foreach($collecting_cases_interest_calculations as $collecting_cases_interest_calculation) {
							$s_sql = "SELECT * FROM collecting_company_cases_claim_lines
							WHERE id = '".$o_main->db->escape_str($collecting_cases_interest_calculation['collecting_company_cases_claim_line_id'])."'";
							$o_query = $o_main->db->query($s_sql);
							$claimline = ($o_query ? $o_query->row_array() : array());
							$claimName = "";
							if($claimline){
								$claimName .= " ".$claimline['name'];
							}
							$pdf->MultiCell(120, 0, $formText_Interest_Output.$claimName." (".date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_from']))." - ".date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_to'])).")", 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(20, 0, number_format($collecting_cases_interest_calculation['rate'], 2, ",", " "), 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, number_format($collecting_cases_interest_calculation['amount'], 2, ",", " "), 'LRT', 'R', 1, 1, '', '', true, 0, true);
						}
						$pdf->MultiCell(120, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(20, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);
					}

					// $pdf->Ln(10);
                    // $pdf->SetFont('calibri', 'b', 11);
                	// $pdf->MultiCell(0, 0, $formText_BasesForInterestCalculation_output, 0, 'L', 0, 1, '', '', true, 0, true);
					// $pdf->SetFont('calibri', '', 9);
					// $s_sql = "SELECT * FROM collecting_interest ORDER BY date ASC";
					// $o_query = $o_main->db->query($s_sql);
					// $interests = ($o_query ? $o_query->result_array() : array());
					// $pdf->MultiCell(35, 0, $formText_From_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
					// $pdf->MultiCell(35, 0, $formText_To_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
					// $pdf->MultiCell(40, 0, $formText_Interest_output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
					// foreach($interests as $key => $interest) {
					// 	$interestValue = $interest['rate'];
					// 	$fromDate = date("d.m.Y", strtotime($interest['date']));
					// 	$toDate = "";
					// 	if(isset($interests[$key+1])){
					// 		$toDate = date("d.m.Y", strtotime($interests[$key+1]['date']));
					// 	}
					// 	$pdf->MultiCell(35, 0, $fromDate, 'LT', 'L', 1, 0, '', '', true, 0, true);
					// 	$pdf->MultiCell(35, 0, $toDate, 'LT', 'L', 1, 0, '', '', true, 0, true);
					// 	$pdf->MultiCell(40, 0, $interestValue, 'LRT', 'R', 1, 1, '', '', true, 0, true);
					// }
					// $pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
					// $pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
					// $pdf->MultiCell(40, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);

                	// ---------------------------------------------------------

                    $step_name = $process_step['name'];
					if($single_letter){
						$step_name = $pdfTextTitle;
					}
                    $step_id = $process_step['id'];
                    $sending_action = $process_step['sending_action'];
					if($sending_action == 2){
						if($debitor['invoiceEmail'] == ""){
							$sending_action = 1;
						}
					}
            		//Close and save PDF document
					if(!$summary){
	            		//Close and save PDF document
	            		$s_filename = 'uploads/protected/'.$formText_Claimletter_output.'_'.$caseData['id'].'_'.time().'.pdf';
					} else {
						$s_filename = 'uploads/protected/'.$formText_CaseSummary_output.'_'.$caseData['id'].'_'.date("Y-m-d H:i:s",time()).'.pdf';
					}
            		$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');

					$s_sql = "SELECT * FROM moduledata WHERE name = 'CollectingCompanyCases'";
					$o_query = $o_main->db->query($s_sql);
					$moduleInfo = ($o_query ? $o_query->row_array() : array());
					$moduleID = $moduleInfo['uniqueID'];

					if(!$summary){
	                    $sending_sql = ", sending_status = 0";
	                    if($creditor['print_reminders'] == 0){
	                        $sending_sql = ", sending_status = 1, performed_action = 2, performed_date = NOW()";
	                    }
						if($creditor['is_demo']){
							$sending_sql = ", sending_status = 5";
						}	

						$sending_action_backup = $sending_action;
						if($process_step['warning_level']){
							if($debitor['invoiceBy'] == 2){
								$sending_action = 5;
							}
						}
						if($debitor['send_all_collecting_company_letters_by_email'] == 1 && $debitor['extra_invoice_email'] != ""){
							$sending_action = 2;
						}
						
	            		$s_sql = "INSERT INTO collecting_cases_claim_letter SET moduleID = '".$o_main->db->escape_str($moduleID)."', createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(), collecting_company_case_id='".$o_main->db->escape_str($caseData['id'])."', sending_action='".$o_main->db->escape_str($sending_action)."', sending_action_backup='".$o_main->db->escape_str($sending_action_backup)."', pdf='".$o_main->db->escape_str($s_filename)."', total_amount = '".$o_main->db->escape_str($totalSumDue+$totalSumPaid)."',
	                    due_date = '".date("Y-m-d", strtotime($dueDatePdf))."', step_name = '".$o_main->db->escape_str($step_name)."', step_id = '".$o_main->db->escape_str($step_id)."', rest_note = '".$o_main->db->escape_str($rest_note)."', single_letter = '".$o_main->db->escape_str($single_letter)."'".$sending_sql;
	            		$o_query = $o_main->db->query($s_sql);
	                    if($o_query){
	                        $claim_letter_id = $o_main->db->insert_id();

	                        $caseFirstLetterDates_update="";
	                        if($caseData['status'] == 3){
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
	                        $s_sql = "UPDATE collecting_company_cases SET create_letter = 0, updated = NOW()".$caseFirstLetterDates_update." WHERE id = '".$o_main->db->escape_str($caseData['id'])."'";
	                        $o_query = $o_main->db->query($s_sql);

	                        if($code_entry_id > 0) {
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
						$s_sql = "INSERT INTO collecting_company_cases_summary SET moduleID = '".$o_main->db->escape_str($moduleID)."', createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(), case_id='".$o_main->db->escape_str($caseData['id'])."', file='".$o_main->db->escape_str($s_filename)."'";
	            		$o_query = $o_main->db->query($s_sql);
						if($o_query) {
							$v_return['summary_id'] = $o_main->db->insert_id();
						} else {
		                	$errors[] = $formText_ErrorUpdating_output;
						}
					}
                } else {
                	$errors[] = $formText_StepNotFound_output;
                }
            } else {
                $errors[] = $formText_TransactionNotClosed_output;
            }
        }else {
            $errors[] = $formText_CaseNotFound_output;
        }
        if(count($errors) > 0){
            $v_return['errors'] = $errors;
        }
        return $v_return;
    }
}
if(!function_exists("generate_pdf_from_letter")){
    function generate_pdf_from_letter($letter_id){
        global $o_main;
        $errors = array();
        define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
        define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
        $v_tmp = explode("/",ACCOUNT_PATH);
        $accountname = array_pop($v_tmp);

        include(__DIR__.'/tcpdf/tcpdf.php');
        include(__DIR__.'/../languagesOutput/no.php');

        $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($letter_id));
        $letter = ($o_query ? $o_query->row_array() : array());

        if($letter){
			$rest_note = $letter['rest_note'];
            $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ? ";
            $o_query = $o_main->db->query($s_sql, array($letter['collecting_company_case_id']));
            $caseData = ($o_query ? $o_query->row_array() : array());
            if($caseData){
                $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
                $creditor = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
                $o_query = $o_main->db->query($s_sql);
                $system_settings = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
                $debitor = ($o_query ? $o_query->row_array() : array());
				if($debitor['extra_language'] == 1){
					include(dirname(__FILE__).'/../languagesOutput/en.php');
				}
				$customer_id = $debitor['id'];
				$s_sql = "SELECT * FROM collecting_cases_debitor_codes WHERE customer_id = ? AND collecting_company_case_id = ? ORDER BY expiration_time DESC";
				$o_query = $o_main->db->query($s_sql, array($customer_id, $caseData['id']));
				$code_entry = $o_query ? $o_query->row_array() : array();
				$code = $code_entry['code'];

                $transactionsNotClosed = false;
                $case_level = "collecting";

                if(!$transactionsNotClosed){
                    $s_sql = "SELECT * FROM ownercompany";
                    $o_query = $o_main->db->query($s_sql);
                    $ownercompany = ($o_query ? $o_query->row_array() : array());

                    $s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
                    $process_step = ($o_query ? $o_query->row_array() : array());

                    if($process_step || $rest_note) {
						
						$extra_type_text = array();
						if($process_step){
							$s_sql = "SELECT * FROM creditor_collecting_company_letter_type_text WHERE collecting_company_letter_type_id = ? AND creditor_id = ?";
							$o_query = $o_main->db->query($s_sql, array($process_step['collecting_company_letter_type_id'], $caseData['creditor_id']));
							$extra_type_text = ($o_query ? $o_query->row_array() : array());
						}

						if($rest_note){
		                    $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
		                    $o_query = $o_main->db->query($s_sql, array($system_settings['rest_note_pdftext_id']));
		                    $pdfText = ($o_query ? $o_query->row_array() : array());
						} else {
		                    $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
		                    $o_query = $o_main->db->query($s_sql, array($process_step['collecting_cases_pdftext_id']));
		                    $pdfText = ($o_query ? $o_query->row_array() : array());
						}
						$pdfTextTitle = $pdfText['title'];
						$pdfTextMain = nl2br($pdfText['text']);

						if($debitor['extra_language'] == 1){
							$pdfTextTitle = $pdfText['title_english'];
							$pdfTextMain = nl2br($pdfText['text_english']);
						}


                        $s_sql = "SELECT * FROM claim_letter_bottomtext";
                        $o_query = $o_main->db->query($s_sql);
                        $bottomText = ($o_query ? $o_query->row_array() : array());

                        if(intval($process_step['bank_account_choice']) == 0) {
                            $bankAccount = $creditor['bank_account'];
                        } else if (intval($process_step['bank_account_choice']) == 1) {
                            $bankAccount = $ownercompany['companyaccount'];
                        }
						$kidNumber = $caseData['kid_number'];

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

                        // $reminderingFromCreditor = false;
                        // $addCodeOnPdf = false;
                        // if($creditor['addCreditorPortalCodeOnLetter'] || intval($creditor['send_reminder_from']) == 1 || intval($process_step['status_id']) == 3) {
                        //     $addCodeOnPdf = true;
                        // }
                        // if((intval($process_step['status_id']) == 0 || intval($process_step['status_id']) == 1) && intval($creditor['send_reminder_from']) == 0) {
                        //     $reminderingFromCreditor = true;
                        // }
                        // if($reminderingFromCreditor) {
                        //     $logoImage = json_decode($creditor['invoicelogo']);
                        //     $companyNamePdf = $creditor['companyname'];
                        //     $companyAddress = $creditor['companypostalbox'].", ".$creditor['companyzipcode']." ".$creditor['companypostalplace'];
                        //     $companyPhone = $creditor['companyphone'];
                        //     $companyOrgNr = $creditor['companyorgnr'];
                        //     $companyEmail = $creditor['companyEmail'];
                        // } else {
                            $logoImage = json_decode($ownercompany['invoicelogo']);
                            $companyNamePdf = $ownercompany['companyname'];
                            $companyAddress = $ownercompany['companypostalbox'].", ".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
                            $companyPhone = $ownercompany['companyphone'];
                            $companyOrgNr = $ownercompany['companyorgnr'];
                            $companyEmail = $ownercompany['companyEmail'];
	                        $companyIban = $ownercompany['companyiban'];
	                        $companySwift = $ownercompany['companyswift'];
                        // }

                    	$pdf->SetY(5);
						$pdf->SetX(20);
                        if(count($logoImage) > 0){
                            $imageLocation = ACCOUNT_PATH."/".$logoImage[0][1][0];
                            $ext = end(explode(".", $imageLocation));
                            $image = base64_encode(file_get_contents($imageLocation));
                        	$pdf->writeHTML('<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $image) . '" width="170" />', true, false, true, true, '');
                        } else {

                        }

						$currencyName = " NOK";
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
						$pdf->MultiCell(70, 0, $formText_Date_pdf." ".date("d.m.Y", strtotime($letter['created'])), 0, 'L', 0, 1, 125, '', true, 0, true);
						$dueDatePdf = date("d.m.Y", strtotime($caseData['due_date']));
						if(!$summary) {
							if($rest_note){
								$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$formText_Immediately_output."", 0, 'L', 0, 1, 125, '', true, 0, true);
							} else {
								$pdf->MultiCell(70, 0, $formText_DueDate_pdf." ".$dueDatePdf."", 0, 'L', 0, 1, 125, '', true, 0, true);
							}
						}


						$pdf->SetFont('calibri', '', 9);
						$pdf->SetX(20);
						$pdf->SetY(20);
						$pdf->MultiCell(80, 0, $formText_Return_output.": ".$companyNamePdf.", ".$companyAddress, 0, 'L', 0, 1, '', '', true, 0, true);

						$pdf->SetFont('calibri', '', 11);
						$pdf->SetX(20);
						$pdf->SetY(40);

						$pdf->MultiCell(100, 0, $debitor['extraName'], 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->MultiCell(100, 0, $debitor['extraStreet'], 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->MultiCell(100, 0, $debitor['extraPostalNumber']." ".$debitor['extraCity'], 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->MultiCell(100, 0, $debitor['extraCountry'], 0, 'L', 0, 1, '', '', true, 0, true);

						$pdf->Ln(26);



                        $pdf->SetFont('calibri', '', 10);
                    	$pdf->MultiCell(55, 0, "<b>".$formText_CreditorName_pdf.": </b>", 0, 'L', 0, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(0, 0, "".$creditor['companyname']."", 0, 'L', 0, 1, '', '', true, 0, true);
                    	$pdf->MultiCell(55, 0, "<b>".$formText_CaseId_pdf.": </b>", 0, 'L', 0, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(0, 0, $caseData['id'], 0, 'L', 0, 1, '', '', true, 0, true);

	                    if($system_settings['debitor_portal_url'] != "") {
	                        $pdf->MultiCell(55, 0, "<b>".$formText_LoginToYourCaseHere.":</b> ", 0, 'L', 0, 0, '', '', true, 0, true);
	                        $pdf->Write(0, $system_settings['debitor_portal_url'], $system_settings['debitor_portal_url'], false, 'L', true);
	                        $pdf->MultiCell(55, 0, "<b>".$formText_UseThisCodeWhenLogin_pdf.":</b> ", 0, 'L', 0, 0, '', '', true, 0, true);
	                        $pdf->MultiCell(0, 0, "".$code."", 0, 'L', 0, 1, '', '', true, 0, true);
	                    }

                        $pdf->Ln(5);
                        $pdf->SetFont('calibri', 'b', 15);
                    	$pdf->MultiCell(0, 0, $pdfTextTitle, 0, 'L', 0, 1, '', '', true, 0, true);
                        $pdf->SetFont('calibri', '', 10);
                    	$pdf->MultiCell(0, 0, $pdfTextMain, 0, 'L', 0, 1, '', '', true, 0, true);
						if($extra_type_text) {
							$pdf->Ln(2);
							$pdf->SetFont('calibri', '', 10);
							$pdf->MultiCell(0, 0, $extra_type_text['text'], 0, 'L', 0, 1, '', '', true, 0, true);
						}
                    	$pdf->Ln(7);

						$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($caseData['id']));
						$payments = ($o_query ? $o_query->result_array() : array());

						$totalSumPaid = 0;
						$totalSumPaidCollecting = 0;
						$totalSumDue = 0;

						foreach($payments as $payment) {
							$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
							$o_query = $o_main->db->query($s_sql);
							$transactions = ($o_query ? $o_query->result_array() : array());
							foreach($transactions as $transaction) {
								$totalSumPaidCollecting += $transaction['amount'];
							}
						}
                        if($totalSumPaidCollecting > 0){
                            $totalSumPaidCollecting = $totalSumPaidCollecting*(-1);
                        }
	                    $totalSumPaid+=$totalSumPaidCollecting;

						$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? AND claim_type = 1 ORDER BY claim_type ASC, created DESC";
                    	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
                    	$main_claims = ($o_query ? $o_query->result_array() : array());
						$mainClaimTotal = 0;
						foreach($main_claims as $main_claim){
                            $mainClaimTotal += $main_claim['amount'];
						}
	                    //includes invoice + payments done before case is created
	                    $original_main_claim = $mainClaimTotal;
						$totalSumDue = $original_main_claim;

						$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
						LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
						WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0 AND cccl.claim_type<>1
						ORDER BY cccl.claim_type ASC, cccl.created DESC";
                    	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
                    	$claims = ($o_query ? $o_query->result_array() : array());

                        foreach($claims as $claim) {
							if(!$claim['payment_after_closed']) {
	                            $totalSumDue += $claim['amount'];
							}
                        }

                    	$pdf->setCellPaddings(2, 2, 2, 2);

                    	$pdf->SetFillColor(255, 255, 255);

                        // $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

                        $pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));

                    	//lastRow
                    	// $pdf->SetFillColor(254, 209, 71);
                        $topBordered = false;
						$pdf->setCellPaddings(2, 2, 2, 2);

                    	$pdf->SetFillColor(255, 255, 255);

                        // $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

                        $pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));

                    	//lastRow
                    	// $pdf->SetFillColor(254, 209, 71);
	                    $pdf->SetFont('calibri', '', 9);
						if($caseData['currency_explanation_text'] != "") {
							$pdf->MultiCell(0, 0, $caseData['currency_explanation_text'], 'TRLB', 'L', 1, 1, '', '', true, 0, true);
						}
	                    $pdf->SetFont('calibri', '', 10);
                        $topBordered = false;
                        $topBordered = true;
                        $claimAmount = number_format($mainClaimTotal, 2, ",", " ");
                    	$pdf->MultiCell(120, 0, $formText_MainClaim_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(46, 0, $claimAmount, 'T', 'R', 1, 0, '', '', true, 0, true);
						$pdf->MultiCell(0, 0, $currencyName, 'RT', 'R', 1, 1, '', '', true, 0, true);

                        // foreach($payments as $payment){
                        //     $pdf->MultiCell(120, 0, $formText_Payment_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
                        // 	$pdf->MultiCell(46, 0, number_format($payment['amount'], 2, ",", " "), 'R', 'R', 1, 0, '', '', true, 0, true);
                        // 	$pdf->MultiCell(0, 0, $currencyName, "R", 'R', 1, 1, '', '', true, 0, true);
                        // }
						// foreach($invoices as $invoice){
						// 	$s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%' ORDER BY created DESC";
	        			// 	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
	        			// 	$claim_transactions = ($o_query ? $o_query->result_array() : array());
	                    //     foreach($claim_transactions as $claim_transaction) {
	                    //         $claim_text_array = explode("_", $claim_transaction['comment']);
	                    //         if($topBordered){
	                    //             $border = "L";
	                    //             $border2 = "R";
						// 			$border3 = "";
	                    //         } else {
	                    //             $border = "LT";
	                    //             $border2 = "RT";
						// 			$border3 = "T";
	                    //             $topBordered = true;
	                    //         }
	                    //     	$pdf->MultiCell(120, 0, $claim_text_array[0], $border, 'L', 1, 0, '', '', true, 0, true);
	                    //     	$pdf->MultiCell(46, 0, number_format($claim_transaction['amount'], 2, ",", " "), $border3, 'R', 1, 0, '', '', true, 0, true);
	                    //     	$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
	                    //     }
						// }
                        foreach($claims as $claim) {
							if(!$claim['payment_after_closed']) {
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
	                        	$pdf->MultiCell(120, 0, $claim['name'], $border, 'L', 1, 0, '', '', true, 0, true);
	                        	$pdf->MultiCell(46, 0, number_format($claim['amount'], 2, ",", " "), $border3, 'R', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, $currencyName, $border2, 'R', 1, 1, '', '', true, 0, true);
							}
                        }


                        if($totalSumPaidCollecting < 0){
                            $pdf->MultiCell(120, 0, $formText_TotalSumPaid_Output, 'TLB', 'L', 1, 0, '', '', true, 0, true);
                            $pdf->MultiCell(46, 0, number_format($totalSumPaidCollecting, 2, ",", " "), 'TB', 'R', 1, 0, '', '', true, 0, true);
	                    	$pdf->MultiCell(0, 0, $currencyName, 'TRB', 'R', 1, 1, '', '', true, 0, true);
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
						if($rest_note){
							$pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".$formText_Immediately_output, 0, 'L', 0, 1, '', '', true, 0, true);
						} else {
		                    $pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf."</b> ".date("d.m.Y", strtotime("+".$process_step['add_number_of_days_to_due_date']." days", time())), 0, 'L', 0, 1, '', '', true, 0, true);
						}
                        $pdf->MultiCell(0, 0, "<b>".$formText_AmountDue_pdf.":</b> ".number_format($totalSumDue+$totalSumPaid, 2, ",", " ")." ".$currencyName, 0, 'L', 0, 1, '', '', true, 0, true);

                        $pdf->Ln(10);

                        $pdf->MultiCell(0, 0, $formText_BestRegards_pdf, 0, 'L', 0, 1, '', '', true, 0, true);
                        $pdf->MultiCell(0, 0, "<b>".$companyNamePdf."</b>", 0, 'L', 0, 1, '', '', true, 0, true);

						$pdf->AddPage();
                    	$pdf->SetFont('calibri', '', 9);
						$topBordered = false;
						$pdf->setCellPaddings(1,1,1,1);
					   	$pdf->SetFillColor(255, 255, 255);

					   	// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');
						// $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');
						if(count($main_claims) > 0) {
							$pdf->SetFont('calibri', 'b', 11);
							$pdf->MultiCell(0, 0, $formText_MainClaimDetails_output, 0, 'L', 0, 1, '', '', true, 0, true);
							$pdf->SetFont('calibri', '', 9);
							$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
							$pdf->MultiCell(20, 0, $formText_InvoiceNr_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, $formText_InvoiceDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, $formText_InvoiceDueDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							// $pdf->MultiCell(35, 0, $formText_CollectWarningDate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, $formText_OriginalAmount_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $formText_MainClaim_output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							foreach($main_claims as $main_claim){
								$collect_warning_date = "";
								if($main_claim['collect_warning_date'] != "0000-00-00" && $main_claim['collect_warning_date'] != ""){
									$collect_warning_date = date("d.m.Y", strtotime($main_claim['collect_warning_date']));
								}
								$pdf->MultiCell(20, 0, $main_claim['invoice_nr'], 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(30, 0, date("d.m.Y", strtotime($main_claim['date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(30, 0, date("d.m.Y", strtotime($main_claim['original_due_date'])), 'LT', 'L', 1, 0, '', '', true, 0, true);
								// $pdf->MultiCell(35, 0, $collect_warning_date, 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(30, 0, number_format($main_claim['original_amount'], 2, ",", " "), 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, number_format($main_claim['amount'], 2, ",", " "), 'LRT', 'R', 1, 1, '', '', true, 0, true);
							}
							$pdf->MultiCell(20, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(35, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(30, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);
						}
						$s_sql = "SELECT * FROM collecting_cases_interest_calculation
						WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."'
						ORDER BY created ASC";
						$o_query = $o_main->db->query($s_sql);
						$collecting_cases_interest_calculations = ($o_query ? $o_query->result_array() : array());
						if(count($collecting_cases_interest_calculations) > 0) {
							$pdf->Ln(10);
							$pdf->SetFont('calibri', 'b', 11);
							$pdf->MultiCell(0, 0, $formText_InterestDetailedInformation_output, 0, 'L', 0, 1, '', '', true, 0, true);
							$pdf->SetFont('calibri', '', 9);
							$pdf->SetLineStyle(array('width' =>0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(50, 50, 50)));
							$pdf->MultiCell(120, 0, $formText_Name_Output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(20, 0, $formText_Rate_output, 'LT', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, $formText_Amount_Output, 'LRT', 'R', 1, 1, '', '', true, 0, true);
							foreach($collecting_cases_interest_calculations as $collecting_cases_interest_calculation) {
								$s_sql = "SELECT * FROM collecting_company_cases_claim_lines
								WHERE id = '".$o_main->db->escape_str($collecting_cases_interest_calculation['collecting_company_cases_claim_line_id'])."'";
								$o_query = $o_main->db->query($s_sql);
								$claimline = ($o_query ? $o_query->row_array() : array());
								$claimName = "";
								if($claimline){
									$claimName .= " ".$claimline['name'];
								}
								$pdf->MultiCell(120, 0, $formText_Interest_Output.$claimName." (".date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_from']))." - ".date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_to'])).")", 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(20, 0, number_format($collecting_cases_interest_calculation['rate'], 2, ",", " "), 'LT', 'L', 1, 0, '', '', true, 0, true);
								$pdf->MultiCell(0, 0, number_format($collecting_cases_interest_calculation['amount'], 2, ",", " "), 'LRT', 'R', 1, 1, '', '', true, 0, true);
							}
							$pdf->MultiCell(120, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(20, 0, "", 'T', 'L', 1, 0, '', '', true, 0, true);
							$pdf->MultiCell(0, 0, "", 'T', 'R', 1, 1, '', '', true, 0, true);
						}
						$pdf->Ln(10);
	                    $pdf->SetFont('calibri', 'b', 11);
	                	$pdf->MultiCell(0, 0, $formText_BasesForInterestCalculation_output, 0, 'L', 0, 1, '', '', true, 0, true);
						$pdf->SetFont('calibri', '', 9);
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
