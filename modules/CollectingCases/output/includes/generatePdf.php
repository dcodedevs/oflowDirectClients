<?php
session_start();
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
// ini_set('max_execution_time', 600);
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';
$_GET['folder'] = 'output';
include_once(dirname(__FILE__).'/tcpdf/tcpdf.php');
include_once(dirname(__FILE__).'/readOutputLanguage.php');

function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

include("fnc_calculate_interest.php");
// include_once(dirname(__FILE__).'/../languagesOutput/no.php');

$caseId = intval($_GET['caseId']);

$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($caseId));
$caseData = ($o_query ? $o_query->row_array() : array());

if(isset($_GET['sending_action']) && 0 < $_GET['sending_action'])
{
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

        $s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
        $o_query = $o_main->db->query($s_sql, array($caseData['id']));
        $creditor_invoice = ($o_query ? $o_query->row_array() : array());

        $s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='Payment' AND invoice_nr = ? AND creditor_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($creditor_invoice['invoice_nr'], $creditor_invoice['creditor_id']));
        $claim_transactions = ($o_query ? $o_query->result_array() : array());
        $transactionsNotClosed = false;

        foreach($claim_transactions as $claim_transaction) {
            $sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ?";
            $o_query = $o_main->db->query($sql, array($claim_transaction['comment']));
            $parent_transaction = $o_query ? $o_query->row_array() : array();
            if($parent_transaction && $parent_transaction['open']){
                $transactionsNotClosed = true;
            }
        }
        if(!$transactionsNotClosed){
            $s_sql = "SELECT * FROM ownercompany";
            $o_query = $o_main->db->query($s_sql);
            $ownercompany = ($o_query ? $o_query->row_array() : array());

            if($handling_action){
                $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
                $process_step = ($o_query ? $o_query->row_array() : array());
                if($process_step) {
                    $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
                    $o_query = $o_main->db->query($s_sql, array($process_step['collecting_cases_pdftext_id']));
                    $pdfText = ($o_query ? $o_query->row_array() : array());

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
                        $s_sql = "INSERT INTO collecting_cases_debitor_codes SET createdBy='process', created=NOW(),
                        customer_id = ?, code= ?, expiration_time = ?, collecting_cases_id = ?";
                        $o_query = $o_main->db->query($s_sql, array($customer_id, $code, $expiration_time, $caseData['id']));
                        $code_entry_id = $o_main->db->insert_id();
                    }

                	// add a page
                	$pdf->AddPage();


                	setlocale(LC_TIME, 'no_NO');
                	$pdf->SetFont('calibri', '', 12);

                    $reminderingFromCreditor = false;
                    $addCodeOnPdf = false;
                    if($creditor['addCreditorPortalCodeOnLetter'] || intval($creditor['send_reminder_from']) == 1 || intval($process_step['status_id']) == 3) {
                        $addCodeOnPdf = true;
                    }
                    if((intval($process_step['status_id']) == 0 || intval($process_step['status_id']) == 1) && intval($creditor['send_reminder_from']) == 0) {
                        $reminderingFromCreditor = true;
                    }
                    if($reminderingFromCreditor) {
                        $logoImage = json_decode($creditor['invoicelogo']);
                        $companyNamePdf = $creditor['companyname'];
                        $companyAddress = $creditor['companypostalbox']." ".$creditor['companyzipcode']." ".$creditor['companypostalplace'];
                        $companyPhone = $creditor['companyphone'];
                        $companyOrgNr = $creditor['companyorgnr'];
                        $companyEmail = $creditor['companyEmail'];
                    } else {
                        $logoImage = json_decode($ownercompany['invoicelogo']);
                        $companyNamePdf = $ownercompany['companyname'];
                        $companyAddress = $ownercompany['companypostalbox']." ".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
                        $companyPhone = $ownercompany['companyphone'];
                        $companyOrgNr = $ownercompany['companyorgnr'];
                        $companyEmail = $ownercompany['companyEmail'];
                    }

                	$pdf->SetY(10);
                    if(count($logoImage) > 0){
                        $imageLocation = ACCOUNT_PATH."/".$logoImage[0][1][0];
                        $ext = end(explode(".", $imageLocation));
                        $image = base64_encode(file_get_contents($imageLocation));
                    	$pdf->MultiCell(210, 0, '<img src="../../../../'.$logoImage[0][1][0].'" width="130" />', 0, 'C', 0, 1, 0, '', true, 0, true);
                    } else {

                    }

                    $pdf->SetY(35);
                	$pdf->MultiCell(100, 0, $companyNamePdf, 0, 'R', 0, 1, 95, '', true, 0, true);
                	$pdf->MultiCell(100, 0, $companyAddress, 0, 'R', 0, 1, 95, '', true, 0, true);
                	$pdf->MultiCell(100, 0, $companyPhone, 0, 'R', 0, 1, 95, '', true, 0, true);
                	$pdf->MultiCell(100, 0, $formText_OrgNr_pdf." ".$companyOrgNr, 0, 'R', 0, 1, 95, '', true, 0, true);
                	$pdf->MultiCell(100, 0, $companyEmail, 0, 'R', 0, 1, 95, '', true, 0, true);
                	$pdf->Ln(2);
                	$pdf->MultiCell(100, 0, $formText_Date_pdf." ".date("d.m.Y", time()), 0, 'R', 0, 1, 95, '', true, 0, true);
                    $dueDatePdf = date("d.m.Y", strtotime("+".$process_step['sending_number_of_days_to_due_date']." days", time()));
                	$pdf->MultiCell(100, 0, $formText_DueDate_pdf." ".$dueDatePdf."", 0, 'R', 0, 1, 95, '', true, 0, true);


                	// $pdf->MultiCell(50, 0, $formText_RegistrationNumber_pdf, 0, 'R', 0, 0, 100, '', true, 0, true);
                	// $pdf->MultiCell(30, 0, $ownercompany['companyorgnr'], 0, 'R', 0, 1, 165, '', true, 0, true);

                	$pdf->SetX(20);
                	$pdf->SetY(35);

                	$pdf->MultiCell(100, 0, $debitor['name']." ".$debitor['middle_name']." ".$debitor['last_name'], 0, 'L', 0, 1, '', '', true, 0, true);
                	$pdf->MultiCell(100, 0, $debitor['paStreet'], 0, 'L', 0, 1, '', '', true, 0, true);
                	$pdf->MultiCell(100, 0, $debitor['paPostalNumber']." ".$debitor['paCity'], 0, 'L', 0, 1, '', '', true, 0, true);
                	$pdf->MultiCell(100, 0, $debitor['paCountry'], 0, 'L', 0, 1, '', '', true, 0, true);

                	$pdf->Ln(36);



                    $pdf->SetFont('calibri', '', 12);
                    if(!$reminderingFromCreditor){
                    	$pdf->MultiCell(55, 0, "".$formText_CreditorName_pdf.": ", 0, 'L', 0, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(0, 0, "<b>".$creditor['companyname']."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
                        $pdf->SetFont('calibri', '', 12);
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
                    $pdf->SetFont('calibri', 'b', 16);
                	$pdf->MultiCell(0, 0, $pdfText['title'], 0, 'L', 0, 1, '', '', true, 0, true);
                    $pdf->SetFont('calibri', '', 12);
                	$pdf->MultiCell(0, 0, $pdfText['text'], 0, 'L', 0, 1, '', '', true, 0, true);

                	$pdf->Ln(10);

                	$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
                	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
                	$invoice = ($o_query ? $o_query->row_array() : array());


                    $s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($caseData['id']));
                    $payments = ($o_query ? $o_query->result_array() : array());

                    $totalSumPaid = 0;
                    $totalSumDue = 0;

                    foreach($payments as $payment) {
                        $totalSumPaid += $payment['amount'];
                    }
                    if($totalSumPaid > 0){
                        $totalSumPaid = $totalSumPaid*(-1);
                    }
                    $totalSumDue += $invoice['collecting_case_original_claim'];

                    $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                    WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 0 OR before_or_after_case is null)";
                    $o_query = $o_main->db->query($sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
                    $payments = $o_query ? $o_query->result_array() : array();

                    foreach($payments as $payment) {
                        $totalSumDue += $payment['amount'];
                    }
                    //includes invoice + payments done before case is created
                    $original_main_claim = $totalSumDue;


            		if(intval($caseData['status']) == 7 || $caseData['status'] == 3) {
                        $s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_case_id = ? ";
                        $o_query = $o_main->db->query($s_sql, array($caseData['id']));

                        $currentClaimInterest = 0;
                        $interestArray = calculate_interest($invoice, $caseData);
                        $totalInterest = 0;
                        foreach($interestArray as $interest) {
                            $interestRate = $interest['rate'];
                            $interestAmount = $interest['amount'];
                            $interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
                            $interestTo = date("Y-m-d", strtotime($interest['dateTo']));

                            $s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
                            date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."'";
                            $o_query = $o_main->db->query($s_sql, array());
                            $totalInterest += $interestAmount;
                        }

                        $s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND claim_type = 8 ORDER BY created DESC";
                        $o_query = $o_main->db->query($s_sql, array($caseData['id']));
                        $interest_claim_line = ($o_query ? $o_query->row_array() : array());
                        if($interest_claim_line) {
                            $s_sql = "UPDATE collecting_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
                            collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."'
                            WHERE id = '".$o_main->db->escape_str($interest_claim_line['id'])."'";
                            $o_query = $o_main->db->query($s_sql);
                        } else {
                            $s_sql = "INSERT INTO collecting_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
                            collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."', claim_type = 8, name= '".$o_main->db->escape_str($formText_Interest_output)."'";
                            $o_query = $o_main->db->query($s_sql);
                        }
                    }

                	$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
                	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
                	$claims = ($o_query ? $o_query->result_array() : array());

    				$s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='InvoiceCustomer' AND invoice_nr = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
    				$o_query = $o_main->db->query($s_sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
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
                    	$pdf->MultiCell(0, 0, $claimAmount, 'RT', 'R', 1, 1, '', '', true, 0, true);

                        foreach($payments as $payment){
                            $pdf->MultiCell(120, 0, $formText_Payment_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(0, 0, number_format($payment['amount'], 2, ",", " "), 'R', 'R', 1, 1, '', '', true, 0, true);
                        }
                    }
                    foreach($claim_transactions as $claim_transaction) {
                        $claim_text_array = explode("_", $claim_transaction['comment']);
                        if($topBordered){
                            $border = "L";
                            $border2 = "R";
                        } else {
                            $border = "LT";
                            $border2 = "RT";
                            $topBordered = true;
                        }
                        $claimAmount = number_format($claim_transaction['amount'], 2, ",", "");
                    	$pdf->MultiCell(120, 0, $claim_text_array[0], $border, 'L', 1, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(0, 0, $claimAmount, $border2, 'R', 1, 1, '', '', true, 0, true);
                    }
                    foreach($claims as $claim) {
                        if($topBordered){
                            $border = "L";
                            $border2 = "R";
                        } else {
                            $border = "LT";
                            $border2 = "RT";
                            $topBordered = true;
                        }
                        $claimAmount = number_format($claim['amount'], 2, ",", "");
                    	$pdf->MultiCell(120, 0, $claim['name'], $border, 'L', 1, 0, '', '', true, 0, true);
                    	$pdf->MultiCell(0, 0, $claimAmount, $border2, 'R', 1, 1, '', '', true, 0, true);
                    }
                    foreach($invoices as $invoice) {
                        if($topBordered){
                            $border = "LR";
                            $border2 = "R";
                        } else {
                            $border = "LRT";
                            $border2 = "RT";
                            $topBordered = true;
                        }
                        $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                        WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 1)";
                        $o_query = $o_main->db->query($sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
                        $payments = $o_query ? $o_query->result_array() : array();

                        foreach($payments as $payment) {
                            $claimAmount = number_format($payment['amount'], 2, ",", "");
                        	$pdf->MultiCell(120, 0, $payment['external_transaction_id'], $border, 'L', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(0, 0, $claimAmount, $border2, 'R', 1, 1, '', '', true, 0, true);
                        }
                    }

                    // foreach($interestArray as $interest) {
                    //     $interestRate = $interest['rate'];
                    //     $interestAmount = $interest['amount'];
                    //     $interestFrom = date("d.m.Y", strtotime($interest['dateFrom']));
                    //     $interestTo = date("d.m.Y", strtotime($interest['dateTo']));
                    //
                    // 	$pdf->MultiCell(120, 0, $formText_Interest_output." (".$interestFrom." - ".$interestTo.") ".$interestRate."%", 'TLB', 'L', 1, 0, '', '', true, 0, true);
                    // 	$pdf->MultiCell(0, 0, number_format($interestAmount, 2, ",", ""), 'TRB', 'R', 1, 1, '', '', true, 0, true);
                    // }

                    $pdf->MultiCell(120, 0, $formText_TotalSumPaid_Output, 'TLB', 'L', 1, 0, '', '', true, 0, true);
                    $pdf->MultiCell(0, 0, number_format($totalSumPaid, 2, ",", ""), 'TRB', 'R', 1, 1, '', '', true, 0, true);

                	$pdf->MultiCell(120, 0, $formText_AmountToPay_pdf, 'TLB', 'L', 1, 0, '', '', true, 0, true);
                	$pdf->MultiCell(0, 0, number_format($totalSumDue+$totalSumPaid, 2, ",", ""), 'TRB', 'R', 1, 1, '', '', true, 0, true);

                	$pdf->Ln(5);

                	$pdf->SetFont('calibri', '', 12);

                	$pdf->setCellPaddings(0, 0, 0, 0);
                    $pdf->MultiCell(0, 0, "<b>".$formText_BankAccount_pdf.":</b> ".$bankAccount, 0, 'L', 0, 1, '', '', true, 0, true);
                    if($kidNumber != "") {
                        $pdf->MultiCell(0, 0, "<b>".$formText_KidNumber_pdf.":</b> ".$kidNumber, 0, 'L', 0, 1, '', '', true, 0, true);
                    }
                    $pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf.":</b> ".date("d.m.Y", strtotime("+".$process_step['sending_number_of_days_to_due_date']." days", time())), 0, 'L', 0, 1, '', '', true, 0, true);
                    $pdf->MultiCell(0, 0, "<b>".$formText_AmountDue_pdf.":</b> ".number_format($totalSumDue+$totalSumPaid, 2, ",", ""), 0, 'L', 0, 1, '', '', true, 0, true);

                    $pdf->Ln(15);

                    $pdf->MultiCell(0, 0, $formText_BestRegards_pdf, 0, 'L', 0, 1, '', '', true, 0, true);
                    $pdf->MultiCell(0, 0, "<b>".$companyNamePdf."</b>", 0, 'L', 0, 1, '', '', true, 0, true);


                	// ---------------------------------------------------------
                	ob_end_clean();
                    $step_name = $process_step['name'];
                    $step_id = $process_step['id'];
            		//Close and save PDF document
            		$s_filename = 'uploads/'.$formText_Claimletter_output.'_'.$caseData['id'].'_'.time().'.pdf';
            		$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');
            		$s_sql = "INSERT INTO collecting_cases_claim_letter SET createdBy='process', created=NOW(), case_id='".$o_main->db->escape_str($caseData['id'])."', sending_action='".$o_main->db->escape_str($_GET['sending_action'])."', pdf='".$o_main->db->escape_str($s_filename)."', total_amount = '".$o_main->db->escape_str($totalSumDue+$totalSumPaid)."', due_date = '".date("Y-m-d", strtotime($dueDatePdf))."', step_name = '".$o_main->db->escape_str($step_name)."', step_id = '".$o_main->db->escape_str($step_id)."'";
            		$o_query = $o_main->db->query($s_sql);
            		$v_return = array(
            			'status' => 1,
            			's_sql' => $s_sql
            		);
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

                        $s_sql = "UPDATE collecting_cases SET due_date = '".date("Y-m-d", strtotime($dueDatePdf))."', updated = NOW()".$caseFirstLetterDates_update." WHERE id = '".$o_main->db->escape_str($caseData['id'])."'";
                        $o_query = $o_main->db->query($s_sql);

                        if($code_entry_id > 0){
                            $s_sql = "UPDATE collecting_cases_debitor_codes SET collecting_cases_claim_letter_id = ? WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($claim_letter_id, $code_entry_id));
                        }
                    }
            		echo json_encode($v_return);
            		return;
                } else {
                	header('Location: ' . $_SERVER['HTTP_REFERER']);
                }
            } else {
            	header('Location: ' . $_SERVER['HTTP_REFERER']);
            }
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    } else {
    	header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
} else {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
