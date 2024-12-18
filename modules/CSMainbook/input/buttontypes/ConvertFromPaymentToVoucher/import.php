<?php

	if(isset($_POST['convert'])) {

        require(__DIR__."/../../../output/includes/fnc_calculate_coverlines.php");

		$s_sql = "SELECT * FROM collecting_cases_payments WHERE content_status < 2";
		$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
		$collecting_cases_payments = $o_query ? $o_query->result_array() : array();
		foreach($collecting_cases_payments as $collecting_cases_payment){
			$s_sql = "INSERT INTO cs_mainbook_voucher SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = '".$o_main->db->escape_str($collecting_cases_payment['created'])."',
			createdBy = '".$o_main->db->escape_str($collecting_cases_payment['createdBy'])."',
			sortnr = '".$o_main->db->escape_str($sortnr)."',
			date = '".$o_main->db->escape_str($collecting_cases_payment['date'])."',
			text = '".$o_main->db->escape_str($formText_Payment_output)."',
			case_id = '".$o_main->db->escape_str($collecting_cases_payment['collecting_case_id'])."',
			amount = '".$o_main->db->escape_str($collecting_cases_payment['amount'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query) {
				$voucher_id = $o_main->db->insert_id();

				$amount = $collecting_cases_payment['amount'];
				$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($collecting_cases_payment['collecting_case_id']));
				$collectingCase = $o_query ? $o_query->row_array() : array();

				$s_sql = "SELECT * FROM creditor WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($collectingCase['creditor_id']));
				$creditor = $o_query ? $o_query->row_array() : array();

				if($collectingCase['status'] == 7){
					$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor['warning_covering_order_and_split_id']));
					$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
				} else {
					$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor['covering_order_and_split_id']));
					$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
				}
				$insertInfo = calculate_coverlines($coveringOrderAndSplit, $voucher_id, $collectingCase, $collecting_cases_payment['amount']);

				if($insertInfo) {
					$paymentId = $voucher_id;
					$collectingCaseId = $collecting_cases_payment['collecting_case_id'];

					$sql = "SELECT * FROM collecting_company_cases WHERE id = '".$o_main->db->escape_str($collectingCaseId)."'";
					$o_query = $o_main->db->query($sql);
					$caseData = $o_query ? $o_query->row_array() : array();

					$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
					LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
					WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
					ORDER BY cccl.claim_type ASC, cccl.created DESC";
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
					$insertInfo = calculate_coverlines($coveringOrderAndSplit, $paymentId, $collectingCase, $collecting_cases_payment['amount'], true);

					$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
					cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
					amount = '".$o_main->db->escape_str($amount)."',
					collecting_claim_line_type = '0',
					bookaccount_id = '".$o_main->db->escape_str(1)."'";
					$o_query = $o_main->db->query($s_sql);

					$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str(1)."'";
					$o_query = $o_main->db->query($s_sql);
					$cs_bookaccount = ($o_query ? $o_query->row_array() : array());

					if($cs_bookaccount['summarize_on_ledger'] == 1){
						$summary_on_collecting_company_ledger += $amount;
					} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
						$summary_on_creditor_ledger += $amount;
					} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
						$summary_on_debitor_ledger += $amount;
					}

					$summary_on_collecting_company_ledger = 0;
					$summary_on_debitor_ledger = 0;
					$summary_on_creditor_ledger = 0;
					foreach($insertInfo as $collecting_claim_line_type => $insertInfoSingle) {
						$collectioncompany_share = $insertInfoSingle[0];
						$creditor_share = $insertInfoSingle[1];
						$agent_share = $insertInfoSingle[2];
						$total_amount = $insertInfoSingle[3];
						$debitor_share = $insertInfoSingle[4];
						$claim_line_type = array();
						if($collecting_claim_line_type > 0) {
							$s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = '".$o_main->db->escape_str($collecting_claim_line_type)."' ";
							$o_query = $o_main->db->query($s_sql);
							$claim_line_type = ($o_query ? $o_query->row_array() : array());
						}
						if($collectioncompany_share > 0){
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
							collecting_claim_line_type = '".$o_main->db->escape_str($collecting_claim_line_type)."',
							bookaccount_id = '".$o_main->db->escape_str($cs_bookaccount_id)."'".$sql_update;
							$o_query = $o_main->db->query($s_sql);
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
							collecting_claim_line_type = '".$o_main->db->escape_str($collecting_claim_line_type)."',
							bookaccount_id = '".$o_main->db->escape_str($cs_bookaccount_id)."'".$sql_update;
							$o_query = $o_main->db->query($s_sql);
						}
						if($debitor_share > 0) {
							$sql_update = "";
							$cs_bookaccount_id = 21;
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
								$summary_on_collecting_company_ledger += $debitor_share*(-1);
							} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
								$summary_on_creditor_ledger += $debitor_share*(-1);
							} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
								$summary_on_debitor_ledger += $debitor_share*(-1);
							}
							$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
							cs_mainbook_voucher_id = '".$o_main->db->escape_str($paymentId)."',
							amount = '".$o_main->db->escape_str($debitor_share*(-1))."',
							collecting_claim_line_type = '".$o_main->db->escape_str($collecting_claim_line_type)."',
							bookaccount_id = '".$o_main->db->escape_str($cs_bookaccount_id)."'.$sql_update";
							$o_query = $o_main->db->query($s_sql);
						}
						$totalUnpaid -= $total_amount;
					}

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
					collecting_claim_line_type = '0',
					bookaccount_id = '".$o_main->db->escape_str(22)."'".$sql_update;
					$o_query = $o_main->db->query($s_sql);

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
					collecting_claim_line_type = '0',
					bookaccount_id = '".$o_main->db->escape_str(16)."'".$sql_update;
					$o_query = $o_main->db->query($s_sql);

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
					collecting_claim_line_type = '0',
					bookaccount_id = '".$o_main->db->escape_str(15)."'".$sql_update;
					$o_query = $o_main->db->query($s_sql);

				} else {
					echo "Error with the transaction ".$collecting_cases_payment['collecting_case_id']."</br>";
				}
			}
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<?php echo $formText_ConvertFromPaymentToVouchers_output;?>
			<input type="submit" name="convert" value="convert">

		</div>
	</form>
</div>
