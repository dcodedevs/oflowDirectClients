<?php
$page = 1;

$sql = "SELECT * FROM collecting_system_settings";
$o_query = $o_main->db->query($sql);
$system_settings = $o_query ? $o_query->row_array() : array();

$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=importPayments";
if(isset($_POST['previewPayments'])){
    $fileContent = file_get_contents($_FILES['file']['tmp_name']);
    $matchingPayments = array();
    $notMatchingPayments = array();
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $fileContent) as $line){
        $paymentDate = "";
        $kidNumber = "";
        $amount = "";
        if($line[0] == "N" && $line[1] == "Y" && $line[2] == "0" && $line[3] == "9" && $line[6] == "3" && $line[7] == "0" ){
            $paymentDate = trim(substr($line, 15, 21 - 15));
            $realPaymentDate = "20".$paymentDate[4].$paymentDate[5]."-".$paymentDate[2].$paymentDate[3]."-".$paymentDate[0].$paymentDate[1];
            $kidNumber = trim(substr($line, 49, 74 - 49));
            $amount = intval(substr($line, 32, 49 - 32))/100;

        }

        //
        if($kidNumber != "" && $amount != "" && $paymentDate != "") {
            $sql = "SELECT * FROM collecting_company_cases WHERE kid_number = ?";
            $o_query = $o_main->db->query($sql, array($kidNumber));
            $collectingCase = $o_query ? $o_query->row_array() : array();
            if($collectingCase) {
				$fullyPaid = false;

				$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
				LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
				WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
				ORDER BY cccl.claim_type ASC, cccl.created DESC";
				$o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
				$claims = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
				$payments = ($o_query ? $o_query->result_array() : array());

				$totalUnpaid = $interestBearingAmount;
				foreach($claims as $claim) {
					$totalUnpaid += $claim['amount'];
				}
				foreach($payments as $payment) {
					$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE cmt.bookaccount_id = '1' AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
					$o_query = $o_main->db->query($s_sql);
					$transactions = ($o_query ? $o_query->result_array() : array());
					foreach($transactions as $transaction){
						$totalUnpaid -= $transaction['amount'];
					}
				}
				$totalUnpaid -= $amount;
				$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
				$o_query = $o_main->db->query($s_sql, array($collectingCase['creditor_id']));
				$creditor = ($o_query ? $o_query->row_array() : array());

				if(($totalUnpaid - $creditor['maximumAmountForgiveTooLittlePayed']) <= 0) {
					$fullyPaid = true;
				}
                $matchingPayments[] = array($kidNumber, $realPaymentDate,$amount, $collectingCase, $fullyPaid);
            } else {
                $notMatchingPayments[] = array($kidNumber, $realPaymentDate,$amount);
            }
        }
    }
    ?>
    <?php
}
if($variables->loggID == "byamba@dcode.no"){
	include_once("fnc_calculate_coverlines_dev.php");
} else {
	include_once("fnc_calculate_coverlines.php");
}

function import_line($kidNumber, $amount, $realPaymentDate, $variables,$paymentsImported, $ignoringPayments, $collectingCase = array()){
	global $o_main;
	global $formText_Payment_output;
	if(!$collectingCase){
		$sql = "SELECT * FROM collecting_company_cases WHERE kid_number = ?";
		$o_query = $o_main->db->query($sql, array($kidNumber));
		$collectingCase = $o_query ? $o_query->row_array() : array();
	}
	if($collectingCase) {
		$sql = "INSERT INTO cs_mainbook_voucher SET kid_number = ?, amount = ?, date = ?, created = NOW(), createdBy = ?, case_id = ?, text = ?";
		$o_query = $o_main->db->query($sql, array($collectingCase['kid_number'], $amount, $realPaymentDate, $variables->loggID, $collectingCase['id'], $formText_Payment_output));
		if($o_query) {
			$paymentsImported++;

			$paymentId = $o_main->db->insert_id();
			$collectingCaseId = $collectingCase['id'];

			$sql = "SELECT * FROM collecting_company_cases WHERE id = '".$o_main->db->escape_str($collectingCaseId)."'";
			$o_query = $o_main->db->query($sql);
			$caseData = $o_query ? $o_query->row_array() : array();

			$s_sql = "SELECT * FROM creditor WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
			$creditor = $o_query ? $o_query->row_array() : array();

			if($caseData['status'] == 7){
				$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor['warning_covering_order_and_split_id']));
				$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
			} else {
				$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor['covering_order_and_split_id']));
				$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
			}

			$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY claim_type ASC, created DESC";
			$o_query = $o_main->db->query($s_sql, array($caseData['id']));
			$claims = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($caseData['id']));
			$payments = ($o_query ? $o_query->result_array() : array());

			$totalUnpaid = $interestBearingAmount;
			foreach($claims as $claim) {
				$totalUnpaid += $claim['amount'];
			}
			foreach($payments as $payment) {
				if($payment['id'] != $paymentId) {
					$totalUnpaid -= $payment['amount'];
				}
			}

			$insertInfo = calculate_coverlines($coveringOrderAndSplit, $paymentId, $collectingCase, $amount, true);

			$summary_on_collecting_company_ledger = 0;
			$summary_on_debitor_ledger = 0;
			$summary_on_creditor_ledger = 0;
			$summary_on_protected_ledger = 0;

			$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
			cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
			amount = '".$o_main->db->escape_str($amount)."',
			bookaccount_id = '".$o_main->db->escape_str(1)."'";
			$o_query = $o_main->db->query($s_sql);

			$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str(1)."'";
			$o_query = $o_main->db->query($s_sql);
			$cs_bookaccount = ($o_query ? $o_query->row_array() : array());

			if($cs_bookaccount['summarize_on_ledger'] == 1) {
				$summary_on_collecting_company_ledger += $amount;
			} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
				$summary_on_creditor_ledger += $amount;
			} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
				$summary_on_debitor_ledger += $amount;
			}

			foreach($insertInfo as $collecting_claim_line_type => $insertInfoSingle) {
				$collectioncompany_share = $insertInfoSingle[0];
				$creditor_share = $insertInfoSingle[1];
				$agent_share = $insertInfoSingle[2];
				$total_amount = $insertInfoSingle[3];
				$debitor_share = $insertInfoSingle[4];
				$protected_share = $insertInfoSingle[5];
				$claim_line_type = array();
				if($collecting_claim_line_type > 0) {
					$s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = '".$o_main->db->escape_str($collecting_claim_line_type)."' ";
					$o_query = $o_main->db->query($s_sql);
					$claim_line_type = ($o_query ? $o_query->row_array() : array());
				}
				if($collectioncompany_share > 0) {
					$sql_update = "";
					$cs_bookaccount_id = 0;
					if($claim_line_type){
						$cs_bookaccount_id = $claim_line_type['cs_bookaccount_id'];
						$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str($cs_bookaccount_id)."'";
						$o_query = $o_main->db->query($s_sql);
						$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
						if($cs_bookaccount['is_creditor_ledger']) {
							$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
						}
						if($cs_bookaccount['is_debitor_ledger']) {
							$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
						}
						if($cs_bookaccount['summarize_on_ledger'] == 1){
							$summary_on_collecting_company_ledger += $collectioncompany_share*(-1);
						} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
							$summary_on_creditor_ledger += $collectioncompany_share*(-1);
						} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
							$summary_on_debitor_ledger += $collectioncompany_share*(-1);
						}
					}
					$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
					cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
					amount = '".$o_main->db->escape_str($collectioncompany_share*(-1))."',
					bookaccount_id = '".$o_main->db->escape_str($cs_bookaccount_id)."'".$sql_update;
					$o_query = $o_main->db->query($s_sql);
					if($collecting_claim_line_type == 17) {
						$cs_bookaccount_id = 27;
						$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str($cs_bookaccount_id)."'";
						$o_query = $o_main->db->query($s_sql);
						$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
						if($cs_bookaccount['is_creditor_ledger']) {
							$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
						}
						if($cs_bookaccount['is_debitor_ledger']) {
							$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
						}
						if($cs_bookaccount['summarize_on_ledger'] == 1){
							$summary_on_collecting_company_ledger += $collectioncompany_share;
						} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
							$summary_on_creditor_ledger += $collectioncompany_share;
						} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
							$summary_on_debitor_ledger += $collectioncompany_share;
						}
						$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
						cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
						amount = '".$o_main->db->escape_str($collectioncompany_share)."',
						bookaccount_id = '".$o_main->db->escape_str($cs_bookaccount_id)."'".$sql_update;
						$o_query = $o_main->db->query($s_sql);
					}
				}
				if($creditor_share > 0) {
					$sql_update = "";
					$cs_bookaccount_id = 0;
					if($claim_line_type){
						$cs_bookaccount_id = $claim_line_type['cs_bookaccount_creditor'];
						$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str($cs_bookaccount_id)."'";
						$o_query = $o_main->db->query($s_sql);
						$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
						if($cs_bookaccount['is_creditor_ledger']) {
							$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
						}
						if($cs_bookaccount['is_debitor_ledger']) {
							$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
						}
						if($cs_bookaccount['summarize_on_ledger'] == 1){
							$summary_on_collecting_company_ledger += $creditor_share*(-1);
						} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
							$summary_on_creditor_ledger += $creditor_share*(-1);
						} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
							$summary_on_debitor_ledger += $creditor_share*(-1);
						}
					}
					$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
					cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
					amount = '".$o_main->db->escape_str($creditor_share*(-1))."',
					bookaccount_id = '".$o_main->db->escape_str($cs_bookaccount_id)."'".$sql_update;
					$o_query = $o_main->db->query($s_sql);
				}
				if($debitor_share > 0) {
					$sql_update = "";
					if($debitor_share > $system_settings['minimumAmountForPayingBackOnCollectingCompanyCases']) {
						$cs_bookaccount_id = 28;
						$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str($cs_bookaccount_id)."'";
						$o_query = $o_main->db->query($s_sql);
						$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
					} else {
						$cs_bookaccount_id = 29;
						$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str($cs_bookaccount_id)."'";
						$o_query = $o_main->db->query($s_sql);
						$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
					}

					if($cs_bookaccount['is_creditor_ledger']) {
						$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
					}
					if($cs_bookaccount['is_debitor_ledger']) {
						$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
					}
					if($cs_bookaccount['summarize_on_ledger'] == 1){
						$summary_on_collecting_company_ledger += $debitor_share*(-1);
					} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
						$summary_on_creditor_ledger += $debitor_share*(-1);
					} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
						$summary_on_debitor_ledger += $debitor_share*(-1);
					}
					$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
					cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
					amount = '".$o_main->db->escape_str($debitor_share*(-1))."',
					bookaccount_id = '".$o_main->db->escape_str($cs_bookaccount_id)."'".$sql_update;
					$o_query = $o_main->db->query($s_sql);
				}
				if($protected_share > 0) {
					$summary_on_protected_ledger += $protected_share*(-1);
				}
				$totalUnpaid -= $total_amount;
			}
			// if($totalUnpaid < 0) {
			// 	$overpaidAmountAfterPayment = abs($totalUnpaid);
			// 	$bookaccount_for_overpaid = 29;
			// 	if($overpaidAmountAfterPayment > $system_settings['overpaidMaxKeepToCollectingCompany']) {
			// 		$bookaccount_for_overpaid = 28;
			// 	}
			// 	$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
			// 	cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
			// 	amount = '".$o_main->db->escape_str($overpaidAmountAfterPayment)."',
			// 	bookaccount_id = '".$o_main->db->escape_str($bookaccount_for_overpaid)."'";
			// 	$o_query = $o_main->db->query($s_sql);
			// }
			if($summary_on_collecting_company_ledger != 0) {
				$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str(22)."'";
				$o_query = $o_main->db->query($s_sql);
				$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
				$sql_update = "";
				if($cs_bookaccount['is_creditor_ledger']) {
					$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
				}
				if($cs_bookaccount['is_debitor_ledger']) {
					$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
				}
				$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
				cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
				amount = '".$o_main->db->escape_str($summary_on_collecting_company_ledger)."',
				bookaccount_id = '".$o_main->db->escape_str(22)."'".$sql_update;
				$o_query = $o_main->db->query($s_sql);
			}
			
			if($summary_on_creditor_ledger != 0) {
				$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str(16)."'";
				$o_query = $o_main->db->query($s_sql);
				$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
				$sql_update = "";
				if($cs_bookaccount['is_creditor_ledger']) {
					$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
				}
				if($cs_bookaccount['is_debitor_ledger']) {
					$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
				}
				$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
				cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
				amount = '".$o_main->db->escape_str($summary_on_creditor_ledger)."',
				bookaccount_id = '".$o_main->db->escape_str(16)."'".$sql_update;
				$o_query = $o_main->db->query($s_sql);
			}

			if($summary_on_debitor_ledger != 0) {
				$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str(15)."'";
				$o_query = $o_main->db->query($s_sql);
				$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
				$sql_update = "";
				if($cs_bookaccount['is_creditor_ledger']) {
					$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
				}
				if($cs_bookaccount['is_debitor_ledger']) {
					$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
				}
				$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
				cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
				amount = '".$o_main->db->escape_str($summary_on_debitor_ledger)."',
				bookaccount_id = '".$o_main->db->escape_str(15)."'".$sql_update;
				$o_query = $o_main->db->query($s_sql);
			}
			if($summary_on_protected_ledger != 0) {
				$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str(33)."'";
				$o_query = $o_main->db->query($s_sql);
				$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
				$sql_update = "";
				if($cs_bookaccount['is_creditor_ledger']) {
					$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
				}
				if($cs_bookaccount['is_debitor_ledger']) {
					$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
				}
				$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
				cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
				amount = '".$o_main->db->escape_str($summary_on_protected_ledger)."',
				bookaccount_id = '".$o_main->db->escape_str(33)."'".$sql_update;
				$o_query = $o_main->db->query($s_sql);
			}

			$collectedMainClaim = 0;
			$collectedInterest = 0;
			$collectedLegalCost = 0;
			$collectedVat = 0;

			$sql_update = "";
			$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created ASC";
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


			$sql_update .= ", current_total_claim = '".$o_main->db->escape_str($totalUnpaid)."'";
			$sql_update .= ", collected_main_claim = '".$o_main->db->escape_str($collectedMainClaim)."'";
			$sql_update .= ", collected_interest = '".$o_main->db->escape_str($collectedInterest)."'";
			$sql_update .= ", collected_legal_cost = '".$o_main->db->escape_str($collectedLegalCost)."'";
			$sql_update .= ", collected_vat = '".$o_main->db->escape_str($collectedVat)."'";

			$sql = "UPDATE collecting_company_cases SET updated = NOW()".$sql_update." WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($caseData['id']));

			$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
			$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
			$creditor = ($o_query ? $o_query->row_array() : array());
			if(($totalUnpaid - $creditor['maximumAmountForgiveTooLittlePayed']) <= 0) {

				$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY claim_type ASC, created DESC";
				$o_query = $o_main->db->query($s_sql, array($caseData['id']));
				$claims = ($o_query ? $o_query->result_array() : array());

				$totalSumPaid = 0;
				$totalSumDue = 0;

				$forgivenAmountOnMainClaim = 0;
				$forgivenAmountExceptMainClaim = 0;
				$totalMainClaim = 0;
				$totalClaim = 0;
				foreach($claims as $claim) {
					if(!$claim['payment_after_closed'] || $claim['claim_type'] != 15) {
						if($claim['claim_type'] == 1 || $claim['claim_type'] == 15 || $claim['claim_type'] == 16){
							$totalMainClaim += $claim['amount'];
						}
						$totalClaim += $claim['amount'];
					}
				}
				if($totalMainClaim < 0){
					$totalMainClaim = 0;
				}
				$totalPaymentForMain = 0;
				$totalPayment = 0;
				$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt
				LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
				WHERE cmv.case_id = ? AND (cmt.bookaccount_id = 1 OR cmt.bookaccount_id = 20) ORDER BY cmv.created DESC";
				$o_query = $o_main->db->query($s_sql, array($caseData['id']));
				$transactions = ($o_query ? $o_query->result_array() : array());
				foreach($transactions as $transaction) {
					if($transaction['bookaccount_id'] == 1) {
						$totalPayment += $transaction['amount'];
					} else if($transaction['bookaccount_id'] == 20) {
						$totalPaymentForMain += $transaction['amount'];
					}
				}

				$overpaidAmount = $totalClaim - $totalPayment;
				if($overpaidAmount < 0){
					$overpaidAmount = abs($overpaidAmount);
				} else {
					$overpaidAmount = 0;
				}
				if($totalClaim > $totalPayment) {
					if(abs($totalMainClaim) > abs($totalPaymentForMain)) {
						$forgivenAmountOnMainClaim = $totalMainClaim + $totalPaymentForMain;
						$forgivenAmountExceptMainClaim = $totalClaim - $totalPayment - $forgivenAmountOnMainClaim;
					} else {
						$forgivenAmountOnMainClaim = 0;
						$forgivenAmountExceptMainClaim = $totalClaim - $totalPayment;
					}
					if($forgivenAmountExceptMainClaim < 0) {
						$forgivenAmountExceptMainClaim = 0;
					}
					if($forgivenAmountOnMainClaim < 0) {
						$forgivenAmountOnMainClaim = 0;
					}
				}
				$sql = "UPDATE collecting_company_cases SET
				updated = now(),
				updatedBy='".$variables->loggID."',
				case_closed_date = NOW(),
				case_closed_reason = 0,
				forgivenAmountOnMainClaim = ?,
				forgivenAmountExceptMainClaim = ?,
				overpaidAmount = ?
				WHERE id = ?";
				$o_query = $o_main->db->query($sql, array($forgivenAmountOnMainClaim, $forgivenAmountExceptMainClaim, $overpaidAmount, $caseData['id']));


			}
		}
	} else {
		$ignoringPayments++;
	}
	return array($paymentsImported, $ignoringPayments);
}
if(isset($_POST['importPayments'])){
    $paymentsImported = 0;
    $ignoringPayments = 0;
    $fileContent = $_POST['fileContent'];
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $fileContent) as $line){
        $paymentDate = "";
        $kidNumber = "";
        $amount = "";
        if($line[0] == "N" && $line[1] == "Y" && $line[2] == "0" && $line[3] == "9" && $line[6] == "3" && $line[7] == "0" ){
            $paymentDate = trim(substr($line, 15, 21 - 15));
            $realPaymentDate = "20".$paymentDate[4].$paymentDate[5]."-".$paymentDate[2].$paymentDate[3]."-".$paymentDate[0].$paymentDate[1];
            $kidNumber = trim(substr($line, 49, 74 - 49));
            $amount = intval(substr($line, 32, 49 - 32))/100;

        }

        //
        if($kidNumber != "" && $amount != "" && $paymentDate != "") {
			list($paymentsImported, $ignoringPayments) = import_line($kidNumber, $amount, $realPaymentDate, $variables,$paymentsImported, $ignoringPayments);
        }
    }
    echo $paymentsImported." ".$formText_ImportedPayments_output."</br>";

    echo $ignoringPayments." ".$formText_IgnoredNotMatchingPayments_output;
} else if($_POST['importCustomPayment']){
	$paymentsImported = 0;
	$ignoringPayments = 0;
	$sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
	$o_query = $o_main->db->query($sql, array($_POST['case_id']));
	$collectingCase = $o_query ? $o_query->row_array() : array();
	if($collectingCase){
		$amount = str_replace(" ","", str_replace(",",".", $_POST['amount']));
		if($amount > 0) {
			$payment_date = $_POST['payment_date'];
			if($payment_date != ""){
				$realPaymentDate = date("Y-m-d", strtotime($payment_date));
				list($paymentsImported, $ignoringPayments) = import_line($kidNumber, $amount, $realPaymentDate, $variables, $paymentsImported, $ignoringPayments, $collectingCase);

			    echo $paymentsImported." ".$formText_ImportedPayments_output."</br>";

			    echo $ignoringPayments." ".$formText_IgnoredNotMatchingPayments_output;
			} else {
				$fw_error_msg[] = $formText_PaymentDateMissing_output;
			}
		} else {
			$fw_error_msg[] = $formText_MissingAmount_output;
		}
	} else {
		$fw_error_msg[] = $formText_MissingCollectingCase_output;
	}
}

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">

			</div>
		</div>
	</div>
</div>
<div class="popupform">
<form class="output-form2" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=importPayments";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="caseId" value="<?php print $_POST['caseId'];?>">

	<div class="inner">
		<div id="popup-validate-message"></div>
        <?php if(!isset($_POST['previewPayments'])){ ?>
            <input type="file" name="file" id="file"/>
        	<div class="popupformbtn"><input type="submit" name="previewPayments" value="<?php echo $formText_PreviewPayments_output; ?>"></div>


			<div class="line ">
				<div class="lineTitle"><?php echo $formText_CollectingCase_Output; ?></div>
				<div class="lineInput">
					<?php if($collectingCase) { ?>
					<a href="#" class="selectCollectingCase"><?php echo $collectingCase['id']." ".$collectingCase['debitorName']?></a>
					<a href="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$collectingCase['id'];?>" target="_blank"><?php echo $formText_openCase_output;?></a>
					<?php } else { ?>
					<a href="#" class="selectCollectingCase"><?php echo $formText_SelectCollectingCase_Output;?></a>
					<?php } ?>
					<input type="hidden" name="case_id" id="collectingCaseId" value="<?php print $collectingCase['id'];?>" required>

				</div>
				<div class="clear"></div>
			</div>

            <div class="line">
                <div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="amount"  value="" placeholder="" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_PaymentDate_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace datepicker" name="payment_date"  value="" placeholder="" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
			<div class="popupformbtn"><input type="submit" name="importCustomPayment" value="<?php echo $formText_ImportCustomPayment_output; ?>"></div>
        <?php } else { ?>
            <?php echo $formText_MatchingPayments_output;?>
            <ul>
                <?php
                foreach($matchingPayments as $matchingPaymentArray) {
                    $paymentDate = $matchingPaymentArray[1];
                    $kidNumber =$matchingPaymentArray[0];
                    $amount = $matchingPaymentArray[2];
                    $invoice = $matchingPaymentArray[3];
                    $fullyPaid = $matchingPaymentArray[4];
					$fullyPaidText = '';
					if($fullyPaid) {
						$fullyPaidText = $formText_FullyPaid_output;
					}
                    echo '<li>'.$kidNumber.' - '.$paymentDate.' '.number_format($amount, 2, ",", "").' '.$invoice['external_invoice_nr'].''.$fullyPaidText.'</li>';
                }
                ?>
            </ul>
            <?php echo $formText_NotMatchingPayments_output;?>
            <ul>
                <?php
                foreach($notMatchingPayments as $matchingPaymentArray) {
                    $paymentDate = $matchingPaymentArray[1];
                    $kidNumber =$matchingPaymentArray[0];
                    $amount = $matchingPaymentArray[2];
                    echo '<li>'.$kidNumber.' - '.$paymentDate.' '.number_format($amount, 2, ",", "").'</li>';
                }
                ?>
            </ul>
        	<div class="popupformbtn">
                <input type="hidden" name="fileContent" value="<?php echo $fileContent;?>" />
    			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="importPayments" value="<?php echo $formText_ImportMatchedPayments_output; ?>">
            </div>
        <?php } ?>

	</div>
</form>
</div>
<style>

.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$(".selectCollectingCase").unbind("click").bind("click", function(){
		var data = {};
		ajaxCall('get_collecting_cases', data, function(obj) {
			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(obj.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
		});
	})

	$("form.output-form2").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
            var form_data = new FormData();

            <?php if(!isset($_POST['previewPayments'])){ ?>
                var file_data = $('#file').prop('files')[0];
                form_data.append('file', file_data);
            <?php } ?>

            $(formdata).each(function(index, obj){
                form_data.append(obj.name, obj.value);
            });

			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
                contentType: false,
                processData: false,
				data: form_data,
				success: function (data) {
					fw_loading_end();
                    $("#popup-validate-message").html("");
					if(data.error !== undefined){
						var _msg = '';
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
						});
						$("#popup-validate-message").html(_msg, true);
						$("#popup-validate-message").show();
					} else {
	                    $('#popupeditboxcontent').html('');
	                    $('#popupeditboxcontent').html(data.html);
	    				$('#popupeditbox').css('height', "auto");
	                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
	                    $("#popupeditbox:not(.opened)").remove();
						out_popup.addClass("close-reload");
	                    $(window).resize();
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
				$("#popup-validate-message").show();
				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
				fw_loading_end();
			});
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message").html(message);
				$("#popup-validate-message").show();
				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			} else {
				$("#popup-validate-message").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		}
	});

	$(".datepicker").datepicker({
		dateFormat: "dd.mm.yy",
		firstDay: 1
	})
});
</script>
