<?php
if(!function_exists("generateRandomString")){
    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}
include_once(__DIR__.'/../../../../CollectingCases/output/includes/tcpdf/tcpdf.php');
$dueDate = $case['due_date'];
$paymentDueDate = $active_payment_plan['first_payment_date'];
if($active_payment_plan['next_payment_date'] != "0000-00-00" && $active_payment_plan['next_payment_date'] != ""){
    $paymentDueDate = $active_payment_plan['next_payment_date'];
}
$correctDueDateTime = strtotime("-".intval($system_settings['send_payment_plan_letter_days_before_due_date'])." days", strtotime($paymentDueDate));

if($correctDueDateTime < time())
{
    $caseData = $case;
    if($caseData)
    {
        $interruptPlan = false;
        $s_sql = "SELECT * FROM collecting_cases_payment_plan_lines WHERE collecting_cases_payment_plan_id = '".$o_main->db->escape_str($active_payment_plan['id'])."' AND (status = 0 OR status is null)
        ORDER BY due_date ASC";
        $o_query = $o_main->db->query($s_sql);
        $collecting_cases_payment_plan_line = $o_query ? $o_query->row_array() : array();
        if($collecting_cases_payment_plan_line) {
            $amountMultiplier = 1;
            if($system_settings['percent_allowed_without_payment_plan_interruption'] > 0){
                $amountMultiplier = $system_settings['percent_allowed_without_payment_plan_interruption']/100;
            }
            if($collecting_cases_payment_plan_line['payed'] < $collecting_cases_payment_plan_line['amount_to_pay']*$amountMultiplier){
                $interruptPlan = true;
            }
        }

        if(!$interruptPlan){
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

        	$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
        	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
        	$claims = ($o_query ? $o_query->result_array() : array());

            foreach($claims as $claim) {
                $totalSumDue += $claim['amount'];
            }
            if($active_payment_plan['monthly_payment'] > $totalSumDue){
                $active_payment_plan['monthly_payment'] = $totalSumDue;
            }

            $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
            $o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
            $debitor = ($o_query ? $o_query->row_array() : array());

            $s_sql = "SELECT * FROM ownercompany";
            $o_query = $o_main->db->query($s_sql);
            $ownercompany = ($o_query ? $o_query->row_array() : array());

            $s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
            $o_query = $o_main->db->query($s_sql, array($system_settings['choose_text_on_payment_plan_letter']));
            $pdfText = ($o_query ? $o_query->row_array() : array());

            $bankAccount = $creditor['bank_account'];
            $kidNumber = $invoice['kid_number'];


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

        	$dayNumber = date("d", strtotime($paymentDueDate));
        	$nextPaymentDate = date("Y-m-".$dayNumber, strtotime($paymentDueDate . " +1 month"));

        	// add a page
        	$pdf->AddPage();


        	setlocale(LC_TIME, 'no_NO');
        	$pdf->SetFont('calibri', '', 12);

            $reminderingFromCreditor = false;
            $addCodeOnPdf = false;
            if($creditor['addCreditorPortalCodeOnLetter'] || intval($creditor['send_reminder_from']) == 1) {
                $addCodeOnPdf = true;
            }

            $logoImage = json_decode($ownercompany['invoicelogo']);
            $companyNamePdf = $ownercompany['companyname'];
            $companyAddress = $ownercompany['companypostalbox']." ".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
            $companyPhone = $ownercompany['companyphone'];
            $companyOrgNr = $ownercompany['companyorgnr'];
            $companyEmail = $ownercompany['companyEmail'];

        	$pdf->SetY(10);
            if(count($logoImage) > 0){
                $imageLocation = ACCOUNT_PATH."/".$logoImage[0][1][0];
                $ext = end(explode(".", $imageLocation));
                $image = base64_encode(file_get_contents($imageLocation));
            	$pdf->MultiCell(210, 0, '<img src="'.__DIR__.'/../../../../../'.$logoImage[0][1][0].'" width="130" />', 0, 'C', 0, 1, 0, '', true, 0, true);
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
            $dueDatePdf = date("d.m.Y", strtotime($paymentDueDate));
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
            	$pdf->MultiCell(0, 0, "<b>".$creditorCustomer['name']." ".$creditorCustomer['middle_name']." ".$creditorCustomer['last_name']."</b>", 0, 'L', 0, 1, '', '', true, 0, true);
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
                    $invoiceDueText = ". ".$formText_DueDate_output." ".$dueDate;
                }
            	$pdf->MultiCell(120, 0, $formText_InvoiceNumber_output." ".$invoice['invoice_nr'].$invoiceDueText, 'LT', 'L', 1, 0, '', '', true, 0, true);
            	$pdf->MultiCell(0, 0, $claimAmount, 'RT', 'R', 1, 1, '', '', true, 0, true);

                foreach($payments as $payment){
                    $pdf->MultiCell(120, 0, $formText_Payment_output." ".date("d.m.Y", strtotime($payment['date'])), 'L', 'L', 1, 0, '', '', true, 0, true);
                	$pdf->MultiCell(0, 0, number_format($payment['amount'], 2, ",", " "), 'R', 'R', 1, 1, '', '', true, 0, true);
                }
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

            $pdf->MultiCell(120, 0, $formText_TotalSumPaid_Output, 'TLB', 'L', 1, 0, '', '', true, 0, true);
            $pdf->MultiCell(0, 0, number_format($totalSumPaid, 2, ",", ""), 'TRB', 'R', 1, 1, '', '', true, 0, true);

        	$pdf->MultiCell(120, 0, $formText_AmountToPay_pdf."    (".$formText_TotalDebtBeforeThisPayment_output.": ".($totalSumDue+$totalSumPaid).")", 'TLB', 'L', 1, 0, '', '', true, 0, true);
        	$pdf->MultiCell(0, 0, number_format($active_payment_plan['monthly_payment'], 2, ",", ""), 'TRB', 'R', 1, 1, '', '', true, 0, true);

        	$pdf->Ln(5);

        	$pdf->SetFont('calibri', '', 12);

        	$pdf->setCellPaddings(0, 0, 0, 0);
            $pdf->MultiCell(0, 0, "<b>".$formText_BankAccount_pdf.":</b> ".$bankAccount, 0, 'L', 0, 1, '', '', true, 0, true);
            if($kidNumber != "") {
                $pdf->MultiCell(0, 0, "<b>".$formText_KidNumber_pdf.":</b> ".$kidNumber, 0, 'L', 0, 1, '', '', true, 0, true);
            }
            $pdf->MultiCell(0, 0, "<b>".$formText_DueDate_pdf.":</b> ".date("d.m.Y", strtotime($paymentDueDate)), 0, 'L', 0, 1, '', '', true, 0, true);
            $pdf->MultiCell(0, 0, "<b>".$formText_AmountDue_pdf.":</b> ".number_format($active_payment_plan['monthly_payment'], 2, ",", ""), 0, 'L', 0, 1, '', '', true, 0, true);

            $pdf->Ln(15);

            $pdf->MultiCell(0, 0, $formText_BestRegards_pdf, 0, 'L', 0, 1, '', '', true, 0, true);
            $pdf->MultiCell(0, 0, "<b>".$companyNamePdf."</b>", 0, 'L', 0, 1, '', '', true, 0, true);


        	// ---------------------------------------------------------
        	ob_end_clean();

    		//Close and save PDF document
    		$s_filename = 'uploads/'.$formText_PaymentPlan_output.'_'.$caseData['id'].'_'.$active_payment_plan['id'].time().'.pdf';
    		$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');

        	$s_sql = "INSERT INTO collecting_cases_payment_plan_lines SET
        	id=NULL,
        	moduleID = '".$o_main->db->escape_str($moduleID)."',
        	created = now(),
        	createdBy= '".$o_main->db->escape_str($variables->loggID)."',
        	status = 0,
        	due_date = '".$o_main->db->escape_str($paymentDueDate)."',
        	amount_to_pay = '".$o_main->db->escape_str($active_payment_plan['monthly_payment'])."',
        	collecting_cases_payment_plan_id = '".$o_main->db->escape_str($active_payment_plan['id'])."',
        	payed = 0,
            pdf='".$o_main->db->escape_str($s_filename)."'";
    		$o_query = $o_main->db->query($s_sql);

            if($o_query){
                $claim_letter_id = $o_main->db->insert_id();

                $s_sql = "UPDATE collecting_cases_payment_plan SET next_payment_date = '".date("Y-m-d", strtotime($nextPaymentDate))."', updated = NOW() WHERE id = '".$o_main->db->escape_str($active_payment_plan['id'])."'";
                $o_query = $o_main->db->query($s_sql);

                if($code_entry_id > 0){
                    // $s_sql = "UPDATE collecting_cases_debitor_codes SET collecting_cases_claim_letter_id = ? WHERE id = ?";
                    // $o_query = $o_main->db->query($s_sql, array($claim_letter_id, $code_entry_id));
                }
            }
        } else {
            $s_sql = "UPDATE collecting_cases_payment_plan SET status = '2', updated = NOW() WHERE id = '".$o_main->db->escape_str($active_payment_plan['id'])."'";
            $o_query = $o_main->db->query($s_sql);
            echo $formText_PaymentPlanWasInterruptedReturnedToNormalProcessing_output." ".$caseData['id']."</br></br></br>";
            if($caseData['status'] == 0 || $caseData['status'] == 1) {
                include("handle_cases.php");
            } else if($caseData['status'] == 3) {
                include("handle_cases_collecting.php");
            }
        }


        if($collecting_cases_payment_plan_line){
            $s_sql = "UPDATE collecting_cases_payment_plan_lines SET status = '1', updated = NOW() WHERE id = '".$o_main->db->escape_str($collecting_cases_payment_plan_line['id'])."'";
            $o_query = $o_main->db->query($s_sql);
        }
    }
}
?>
