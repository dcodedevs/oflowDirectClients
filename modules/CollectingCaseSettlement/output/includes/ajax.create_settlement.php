<?php
$paymentsToSettle = $_POST['paymentsToSettle'];
$total_shares = array();
$collectingcompany_shares = array();
$creditor_shares= array();
$debitor_shares = array();
$agent_shares = array();
$hasErrors = false;
foreach($paymentsToSettle as $paymentId){
    $s_sql = "SELECT * FROM collecting_cases_payments WHERE id = ? AND (settlement_id = 0 OR settlement_id is null)";
    $o_query = $o_main->db->query($s_sql, array($paymentId));
    $payment = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($payment['collecting_case_id']));
    $collecting_case = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
    $creditor = $o_query ? $o_query->row_array() : array();
    if($payment && $collecting_case && $creditor) {
        $s_sql = "SELECT * FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ?";
        $o_query = $o_main->db->query($s_sql, array($payment['id']));
        $paymentlines = $o_query ? $o_query->result_array() : array();
        foreach($paymentlines as $paymentline){
            $collectingcompany_shares[$creditor['id']] += $paymentline['collectingcompany_amount'];
            $creditor_shares[$creditor['id']] += $paymentline['creditor_amount'];
            $debitor_shares[$creditor['id']] += $paymentline['debitor_amount'];
            $agent_shares[$creditor['id']] += $paymentline['agent_amount'];
            $total_shares[$creditor['id']] += $paymentline['amount'];
        }
    } else {
        $hasErrors = true;
    }
}
if(!$hasErrors) {
    $collectingcompany_share_total = 0;
    $creditor_share_total = 0;
    $debitor_share_total = 0;
    $agent_share_total = 0;
    $total_share_total = 0;
    foreach($collectingcompany_shares as $collectingcompany_share) {
        $collectingcompany_share_total+=$collectingcompany_share;
    }
    foreach($creditor_shares as $creditor_share) {
        $creditor_share_total+=$creditor_share;
    }
    foreach($debitor_shares as $debitor_share) {
        $debitor_share_total+=$debitor_share;
    }
    foreach($agent_shares as $agent_share) {
        $agent_share_total+=$agent_share;
    }

    $s_sql = "INSERT INTO collectingcompany_settlement SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
    date = NOW(),
    collectingcompany_total_amount = '".$o_main->db->escape_str($collectingcompany_share_total)."',
    creditor_total_amount = '".$o_main->db->escape_str($creditor_share_total)."',
    debitor_total_amount = '".$o_main->db->escape_str($debitor_share_total)."',
    agent_total_amount = '".$o_main->db->escape_str($agent_share_total)."'";
    $o_query = $o_main->db->query($s_sql);
    $collectingcompany_settlement_id = $o_main->db->insert_id();

    include_once(__DIR__.'/../../../CollectingCases/output/includes/tcpdf/tcpdf.php');

    $s_sql = "SELECT * FROM ownercompany";
    $o_query = $o_main->db->query($s_sql);
    $ownercompany = ($o_query ? $o_query->row_array() : array());

    if($collectingcompany_settlement_id > 0){
        $collecting_case_forgiven_shown = array();
        foreach($collectingcompany_shares as $creditorId=>$collectingcompany_share) {
            $creditor_share = $creditor_shares[$creditorId];
            $debitor_share = $debitor_shares[$creditorId];
            $agent_share = $agent_shares[$creditorId];

            $s_sql = "SELECT * FROM creditor WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($creditorId));
            $creditor = $o_query ? $o_query->row_array() : array();

            $s_sql = "SELECT customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
            $o_query = $o_main->db->query($s_sql, array($creditorId));
            $creditorCustomer = ($o_query ? $o_query->row_array() : array());

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


        	setlocale(LC_TIME, 'no_NO');
        	$pdf->SetFont('calibri', '', 9);


            $pdf->SetFillColor(255, 255, 255);

            // $borderStyleArray = array('width' => 0.5, 'color' => array(0,255,0), 'dash' => 0, 'cap' => 'butt');

            $pdf->SetLineStyle(array('width' =>0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

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
        	$pdf->MultiCell(100, 0, $creditorCustomer['name'], 0, 'L', 0, 1, '', '', true, 0, true);

            $pdf->Ln(10);
            $pdf->setCellPaddings(1, 1, 1, 1);
            $totalCreditor = 0;
            $totalCollecting = 0;
            foreach($paymentsToSettle as $paymentId){
                $s_sql = "SELECT * FROM collecting_cases_payments WHERE id = ? AND (settlement_id = 0 OR settlement_id is null)";
                $o_query = $o_main->db->query($s_sql, array($paymentId));
                $payment = $o_query ? $o_query->row_array() : array();

                $s_sql = "SELECT * FROM collecting_cases_payment_plan_lines WHERE id = ? AND status = 1";
                $o_query = $o_main->db->query($s_sql, array($payment['collecting_cases_payment_plan_line_id']));
                $collecting_cases_payment_plan_line = $o_query ? $o_query->row_array() : array();

                $s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE id = ? AND status <> 2";
                $o_query = $o_main->db->query($s_sql, array($collecting_cases_payment_plan_line['collecting_cases_payment_plan_id']));
                $collecting_cases_payment_plan = $o_query ? $o_query->row_array() : array();

                $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($payment['collecting_case_id']));
                $collecting_case = $o_query ? $o_query->row_array() : array();

                $s_sql = "SELECT * FROM creditor WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
                $creditor = $o_query ? $o_query->row_array() : array();

                $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['debitor_id']));
                $debitor = ($o_query ? $o_query->row_array() : array());

                if($creditor['id'] == $creditorId) {
                    $payed = 0;
                    $s_sql = "SELECT * FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($payment['id']));
                    $paymentlines = $o_query ? $o_query->result_array() : array();
                    foreach($paymentlines as $paymentline) {
                        $payed += $paymentline['creditor_amount'];
                        $payed += $paymentline['collectingcompany_amount'];
                    }
                    $forgiven = 0;
                    if($collecting_case['forgiven_too_little_payed'] > 0){
                        $forgiven = $collecting_case['forgiven_too_little_payed'];
                    }
                    $forgivenText = "";
                    if(!isset($collecting_case_forgiven_shown[$collecting_case['id']])){
                        $forgivenText = $formText_Forgiven_output.": ".$forgiven;
                    }
                    if(intval($collecting_case['status']) == 0){
                        $collecting_case['status'] = 1;
                    }
                    $s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig WHERE id = ? ORDER BY id ASC";
                    $o_query = $o_main->db->query($s_sql, array(intval($collecting_case['status'])));
                    $collecting_case_status = ($o_query ? $o_query->row_array() : array());

                    $s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE id = ? ORDER BY id ASC";
                    $o_query = $o_main->db->query($s_sql, array(intval($collecting_case['sub_status'])));
                    $collecting_case_substatus = ($o_query ? $o_query->row_array() : array());

                    $tableRowHeight = $pdf->getStringHeight(30, 0, date("d.m.Y", strtotime($payment['date'])), 'LRBT', 'L', 1, 0, '', '', true, 0, true);
                    $tmpHeight = $pdf->getStringHeight(55, 0, $formText_CaseId_output.": ".$collecting_case['id']." (".$debitor['name']." ".$debitor['middlename']." ".$debitor['lastname'].")", 'RT', 'L', 1, 0, '', '', true, 0, true);
                    if($tmpHeight > $tableRowHeight){
                        $tableRowHeight = $tmpHeight;
                    }
                    $tmpHeight = $pdf->getStringHeight(30, 0, $forgivenText, 'RT', 'L', 1, 0, '', '', true, 0, true);
                    if($tmpHeight > $tableRowHeight){
                        $tableRowHeight = $tmpHeight;
                    }
                    $tmpHeight = $pdf->getStringHeight(30, 0, $formText_Payed_Output.": ".$payed, 'RT', 'L', 1, 0, '', '', true, 0, true);
                    if($tmpHeight > $tableRowHeight){
                        $tableRowHeight = $tmpHeight;
                    }
                    $tmpHeight = $pdf->getStringHeight(30, 0, $collecting_case_status['name'], 'RT', 'L', 1, 1, '', '', true, 0, true);
                    if($tmpHeight > $tableRowHeight){
                        $tableRowHeight = $tmpHeight;
                    }
                	$pdf->MultiCell(30, $tableRowHeight, date("d.m.Y", strtotime($payment['date'])), 'LRBT', 'L', 1, 0, '', '', true, 0, true);
                	$pdf->MultiCell(55, $tableRowHeight, $formText_CaseId_output.": ".$collecting_case['id']." (".$debitor['name']." ".$debitor['middlename']." ".$debitor['lastname'].")", 'RT', 'L', 1, 0, '', '', true, 0, true);
                	$pdf->MultiCell(30, $tableRowHeight, $forgivenText, 'RT', 'L', 1, 0, '', '', true, 0, true);
                	$pdf->MultiCell(30, $tableRowHeight, $formText_Payed_Output.": ".$payed, 'RT', 'L', 1, 0, '', '', true, 0, true);
                	$pdf->MultiCell(30, $tableRowHeight, $collecting_case_status['name'], 'RT', 'L', 1, 1, '', '', true, 0, true);

                    $collecting_case_forgiven_shown[$collecting_case['id']] = $forgiven;

                    $pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
                    $pdf->MultiCell(45, 0, $formText_ClaimlineType_output, 'LRBT', 'L', 1, 0, '', '', true, 0, true);
                    $pdf->MultiCell(50, 0, $formText_CreditorShare_output, 'RBT', 'R', 1, 0, '', '', true, 0, true);
                    $pdf->MultiCell(50, 0, $formText_CollectingCompanyShare_output, 'RBT', 'R', 1, 1, '', '', true, 0, true);
                    $totalLinesCreditor = 0;
                    $totalLinesCollecting = 0;
                    foreach($paymentlines as $paymentline) {
                        $s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($paymentline['collecting_claim_line_type']));
                        $claim_type = $o_query ? $o_query->row_array() : array();
                        if($claim_type){
                        	$pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(45, 0, $claim_type['type_name'], 'LRBT', 'L', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(50, 0, number_format($paymentline['creditor_amount'], 2, ",", ""), 'RBT', 'R', 1, 0, '', '', true, 0, true);
                        	$pdf->MultiCell(50, 0, number_format($paymentline['collectingcompany_amount'], 2, ",", ""), 'RBT', 'R', 1, 1, '', '', true, 0, true);
                            $totalLinesCreditor+=$paymentline['creditor_amount'];
                            $totalLinesCollecting+=$paymentline['collectingcompany_amount'];
                        }
                    }
                    $pdf->MultiCell(30, 0, "", '', 'L', 1, 0, '', '', true, 0, true);
                    $pdf->MultiCell(45, 0, $formText_Total_output, 'LRBT', 'L', 1, 0, '', '', true, 0, true);
                    $pdf->MultiCell(50, 0, number_format($totalLinesCreditor, 2, ",", ""), 'RBT', 'R', 1, 0, '', '', true, 0, true);
                    $pdf->MultiCell(50, 0, number_format($totalLinesCollecting, 2, ",", ""), 'RBT', 'R', 1, 1, '', '', true, 0, true);
                    $totalCreditor += $totalLinesCreditor;
                    $totalCollecting += $totalLinesCollecting;
                }
            }

            $pdf->MultiCell(75, 0, $formText_Total_output, 'LRBT', 'L', 1, 0, '', '', true, 0, true);
            $pdf->MultiCell(50, 0, number_format($totalCreditor, 2, ",", ""), 'RBT', 'R', 1, 0, '', '', true, 0, true);
            $pdf->MultiCell(50, 0, number_format($totalCollecting, 2, ",", ""), 'RBT', 'R', 1, 1, '', '', true, 0, true);




            ob_end_clean();

    		//Close and save PDF document
    		$s_filename = 'uploads/'.$formText_Settlement_output.'_'.$collectingcompany_settlement_id.'_'.$creditorId.'.pdf';
    		$pdf->Output(ACCOUNT_PATH.'/'.$s_filename, 'F');

            $s_sql = "INSERT INTO creditor_settlement SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
            collectingcompany_settlement_id = '".$o_main->db->escape_str($collectingcompany_settlement_id)."',
            creditor_id = '".$o_main->db->escape_str($creditorId)."',
            collectingcompany_amount = '".$o_main->db->escape_str($collectingcompany_share)."',
            creditor_amount = '".$o_main->db->escape_str($creditor_share)."',
            debitor_amount = '".$o_main->db->escape_str($debitor_share)."',
            agent_amount = '".$o_main->db->escape_str($agent_share)."',
            pdf='".$o_main->db->escape_str($s_filename)."'";
            $o_query = $o_main->db->query($s_sql);
            if(!$o_query) {
                $lineErrors = true;
            }
        }
        if(!$lineErrors) {
            foreach($paymentsToSettle as $paymentId){
                $s_sql = "UPDATE collecting_cases_payments SET settlement_id = ? WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($collectingcompany_settlement_id, $paymentId));
            }
        } else {
            $fw_error_msg[] = $formText_ErrorCreatingSettlementLines_output;
            $s_sql = "DELETE FROM collectingcompany_settlement WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($collectingcompany_settlement_id));
        }
    } else {
        $fw_error_msg[] = $formText_ErrorCreatingSettlement_output;
    }
} else {
    $fw_error_msg[] = $formText_ErrorWithPaymentLines_output;
}
?>
