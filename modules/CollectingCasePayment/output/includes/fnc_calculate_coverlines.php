<?php

function calculate_coverlines($coveringOrderAndSplit, $paymentId, $collectingCase, $amount, $fromScratch = false){
    global $o_main;
	$coverline_vat_percent = 25;

	$s_sql = "SELECT * FROM collecting_system_settings";
    $o_query = $o_main->db->query($s_sql);
    $collecting_system_settings = $o_query ? $o_query->row_array() : array();

	if($collecting_system_settings['coverline_vat_percent'] != "") {
		$coverline_vat_percent = $collecting_system_settings['coverline_vat_percent'];
	}
    $in_warning_level = false;
    if($collectingCase['collecting_cases_process_step_id'] > 0) {  
        
        $s_sql = "SELECT cccpp.* FROM collecting_cases_collecting_process_steps cccpp WHERE cccpp.id = ?";
        $o_query = $o_main->db->query($s_sql, array($collectingCase['collecting_cases_process_step_id']));
        $current_step = $o_query ? $o_query->row_array() : array();
        if($current_step['warning_level']) {
            $in_warning_level = true;   
        }
    }
	$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
	LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
	WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
	ORDER BY cccl.claim_type ASC, cccl.created DESC";
    $o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
    $collecting_cases_claim_lines = $o_query ? $o_query->result_array() : array();
    $active_claim_types = array(1);
    foreach($collecting_cases_claim_lines as $collecting_cases_claim_line) {
        $active_claim_types[] = $collecting_cases_claim_line['claim_type'];
    }
    $s_sql = "SELECT * FROM covering_order_and_split_lines WHERE covering_order_and_split_id = ? AND collecting_claim_line_type IN (".implode(',', $active_claim_types).") ORDER BY covering_order ASC";
    $o_query = $o_main->db->query($s_sql, array($coveringOrderAndSplit['id']));
    $coveringOrderAndSplitLines = $o_query ? $o_query->result_array() : array();

    $s_sql = "SELECT * FROM collecting_cases_payments WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($paymentId));
    $payment = $o_query ? $o_query->row_array() : array();

    if($fromScratch) {
        $s_sql = "DELETE FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ?";
        $o_query = $o_main->db->query($s_sql, array($payment['id']));
    }
    if($payment){
        $s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? AND id <> ?";
        $o_query = $o_main->db->query($s_sql, array($collectingCase['id'], $payment['id']));
        $previousPayments = $o_query ? $o_query->result_array() : array();
    } else {
        $s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ?";
        $o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
        $previousPayments = $o_query ? $o_query->result_array() : array();
    }
    $amountLeftToDistributed = $amount;

    // $s_sql = "SELECT * FROM creditor_invoice WHERE collecting_case_id = ?";
    // $o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
    // $invoice = $o_query ? $o_query->row_array() : array();
	$originalClaimAmount = 0;
	$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND claim_type = 1";
	$o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
	$collecting_cases_claim_lines = $o_query ? $o_query->result_array() : array();
	foreach($collecting_cases_claim_lines as $collecting_cases_claim_line) {
		$originalClaimAmount += $collecting_cases_claim_line['amount'];
	}
	$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND (claim_type = 15 || claim_type = 16)";
	$o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
	$collecting_cases_claim_line_payments = $o_query ? $o_query->result_array() : array();
	foreach($collecting_cases_claim_line_payments as $collecting_cases_claim_line_payment){
		$originalClaimAmount += $collecting_cases_claim_line_payment['amount'];
	}

    $insertInfo = array();
    $hasErrors = false;

    $amountDistributed = 0;

    $lastLineNumber = count($coveringOrderAndSplitLines);
    $currentLineNumber = 1;
	$addVatLine = false;
    foreach($coveringOrderAndSplitLines as $coveringOrderAndSplitLine) {
        $lineAmount = 0;
        if($amountLeftToDistributed > 0){
            if($coveringOrderAndSplitLine['collecting_claim_line_type'] == 1) {
                $lineAmount = $originalClaimAmount;
            } else {
                $s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND claim_type = ?";
                $o_query = $o_main->db->query($s_sql, array($collectingCase['id'], $coveringOrderAndSplitLine['collecting_claim_line_type']));
                $collecting_cases_claim_lines = $o_query ? $o_query->result_array() : array();
                foreach($collecting_cases_claim_lines as $collecting_cases_claim_line) {
                    $lineAmount += $collecting_cases_claim_line['amount'];
                }
            }
            foreach($previousPayments as $previousPayment){
                $s_sql = "SELECT * FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ? AND collecting_claim_line_type = ?";
                $o_query = $o_main->db->query($s_sql, array($previousPayment['id'], $coveringOrderAndSplitLine['collecting_claim_line_type']));
                $paymentCoverlines = $o_query ? $o_query->result_array() : array();
                foreach($paymentCoverlines as $paymentCoverline) {
                    $lineAmount -= $paymentCoverline['amount'];
                }
            }
            if($lineAmount > 0){
                $amountLeftToDistributedTemp = round($amountLeftToDistributed - $lineAmount, 2);
                $collectioncompany_share = $coveringOrderAndSplitLine['collectioncompany_share'];
                $creditor_share = $coveringOrderAndSplitLine['creditor_share'];
                if($in_warning_level) {
                    if($coveringOrderAndSplitLine['warning_level_checkbox']) {
                        $collectioncompany_share = $coveringOrderAndSplitLine['collectioncompany_share_warning'];
                        $creditor_share = $coveringOrderAndSplitLine['creditor_share_warning'];
                    }
                }
                if($amountLeftToDistributedTemp > 0){
                    $amountToDivide = $lineAmount;
                    $amountLeftToDistributed = $amountLeftToDistributedTemp;
                } else {
                    $amountToDivide = $amountLeftToDistributed;
                    $amountLeftToDistributed = 0;
                }
                $collection_amount = round($amountToDivide/100*$collectioncompany_share, 2);
                $creditor_amount = round($amountToDivide/100*$creditor_share, 2);

                $agent_amount = 0;
                $debitor_amount = 0;
                if($lastLineNumber == $currentLineNumber){
                    $debitor_amount = $amountLeftToDistributed;
                }
                $insertInfo[$coveringOrderAndSplitLine['collecting_claim_line_type']] = array($collection_amount, $creditor_amount, $agent_amount, $amountToDivide, $debitor_amount);

                $difference = round($amountToDivide - $collection_amount - $creditor_amount - $agent_amount, 0);
                if($difference != 0){
                    $hasErrors = true;
                }
				if($coveringOrderAndSplitLine['collecting_claim_line_type'] == 4 || $coveringOrderAndSplitLine['collecting_claim_line_type'] == 6) {
					$collection_amount = 0;
					$creditor_amount = 0;
					$agent_amount = 0;
					$total_amount = 0;
					$debitor_amount = 0;
					if(isset($insertInfo[17])){
						$collection_amount = $insertInfo[17][0];
						$creditor_amount = $insertInfo[17][1];
						$agent_amount = $insertInfo[17][2];
						$total_amount = $insertInfo[17][3];
						$debitor_amount = $insertInfo[17][4];
					}
					$collection_amount += $coverline_vat_percent/100*$amountToDivide;
					$creditor_amount += ($collection_amount*(-1));

					$insertInfo[17] = array($collection_amount, $creditor_amount, $agent_amount, $total_amount, $debitor_amount);
				}
            }
        }
        $currentLineNumber++;
    }
    if($hasErrors){
        return false;
    } else {
        return $insertInfo;
    }
}
?>
