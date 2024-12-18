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

    $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($paymentId));
    $payment = $o_query ? $o_query->row_array() : array();

    if($fromScratch) {
        $s_sql = "DELETE FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ?";
        $o_query = $o_main->db->query($s_sql, array($payment['id']));
    }
    if($payment){
        $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? AND id <> ?";
        $o_query = $o_main->db->query($s_sql, array($collectingCase['id'], $payment['id']));
        $previousPayments = $o_query ? $o_query->result_array() : array();
    } else {
        $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ?";
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
        $protected_amount = 0;
        if($amountLeftToDistributed > 0){
            if($coveringOrderAndSplitLine['collecting_claim_line_type'] == 1) {
                $lineAmount = $originalClaimAmount;
            } else {
                $s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND claim_type = ?";
                $o_query = $o_main->db->query($s_sql, array($collectingCase['id'], $coveringOrderAndSplitLine['collecting_claim_line_type']));
                $collecting_cases_claim_lines = $o_query ? $o_query->result_array() : array();
                foreach($collecting_cases_claim_lines as $collecting_cases_claim_line) {
                    if(($collecting_cases_claim_line['claim_type'] == 9 || $collecting_cases_claim_line['claim_type'] == 10) && ($collecting_cases_claim_line['court_fee_released_date'] == '0000-00-00' || $collecting_cases_claim_line['court_fee_released_date'] == '')){
                        $protected_amount +=  $collecting_cases_claim_line['amount'];
                    }
                    $lineAmount += $collecting_cases_claim_line['amount'];
                }
            }
            foreach($previousPayments as $previousPayment){
                $s_sql = "SELECT * FROM cs_mainbook_transaction cmt
				JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.cs_bookaccount_id = cmt.bookaccount_id
				WHERE cmt.cs_mainbook_voucher_id = ? AND cccltb.id = ?";
                $o_query = $o_main->db->query($s_sql, array($previousPayment['id'], $coveringOrderAndSplitLine['collecting_claim_line_type']));
                $paymentCoverlines = $o_query ? $o_query->result_array() : array();
                foreach($paymentCoverlines as $paymentCoverline) {
                    $lineAmount += $paymentCoverline['amount'];
                }

                $s_sql = "SELECT * FROM cs_mainbook_transaction cmt
				JOIN collecting_cases_claim_line_type_basisconfig cccltb ON cccltb.cs_bookaccount_creditor = cmt.bookaccount_id
				WHERE cmt.cs_mainbook_voucher_id = ? AND cccltb.id = ?";
                $o_query = $o_main->db->query($s_sql, array($previousPayment['id'], $coveringOrderAndSplitLine['collecting_claim_line_type']));
                $paymentCoverlines = $o_query ? $o_query->result_array() : array();
                foreach($paymentCoverlines as $paymentCoverline) {
                    $lineAmount += $paymentCoverline['amount'];
                }
            }
            if($lineAmount > 0) {
                $amountLeftToDistributedTemp = round($amountLeftToDistributed - $lineAmount, 2);
                $collectioncompany_share = $coveringOrderAndSplitLine['collectioncompany_share'];
                $creditor_share = $coveringOrderAndSplitLine['creditor_share'];
                if($amountLeftToDistributedTemp > 0) {
                    $amountToDivide = $lineAmount;
                    $amountLeftToDistributed = $amountLeftToDistributedTemp;
                } else {
                    $amountToDivide = $amountLeftToDistributed;
                    $amountLeftToDistributed = 0;
                }
                $amountToDivide -= $protected_amount;
                $collection_amount = round($amountToDivide/100*$collectioncompany_share, 2);
                $creditor_amount = $amountToDivide-$collection_amount;

                $agent_amount = 0;
                $debitor_amount = 0;
                if($lastLineNumber == $currentLineNumber){
                    $debitor_amount = $amountLeftToDistributed;
                }
                $insertInfo[$coveringOrderAndSplitLine['collecting_claim_line_type']] = array($collection_amount, $creditor_amount, $agent_amount, $amountToDivide, $debitor_amount, $protected_amount);

                $difference = $amountToDivide - $collection_amount - $creditor_amount - $debitor_amount;
                if($difference != 0){
                    $hasErrors = true;
                }
				if($coveringOrderAndSplitLine['collecting_claim_line_type'] == 4 || $coveringOrderAndSplitLine['collecting_claim_line_type'] == 6) {
					$collection_amount = 0;
					$creditor_amount = 0;
					$agent_amount = 0;
					$total_amount = 0;
					$debitor_amount = 0;
					$protected_amount = 0;
					if(isset($insertInfo[17])){
						$collection_amount = $insertInfo[17][0];
						$creditor_amount = $insertInfo[17][1];
						$agent_amount = $insertInfo[17][2];
						$total_amount = $insertInfo[17][3];
						$debitor_amount = $insertInfo[17][4];
					}
					$collection_amount += round($coverline_vat_percent/100*$amountToDivide, 2);
					$creditor_amount += ($collection_amount*(-1));

					$insertInfo[17] = array($collection_amount, $creditor_amount, $agent_amount, $total_amount, $debitor_amount, $protected_amount);
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
