<?php
	if(isset($_POST['fix_stopped'])) {

		$s_sql = "SELECT * FROM collecting_company_cases WHERE IFNULL(case_closed_date, '0000-00-00') <> '0000-00-00'";
		$o_query = $o_main->db->query($s_sql);
		$closed_company_cases = $o_query ? $o_query->result_array() : array();
		foreach($closed_company_cases as $closed_company_case){
			$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
			LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
			WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
			ORDER BY cccl.claim_type ASC, cccl.created DESC";
			$o_query = $o_main->db->query($s_sql, array($closed_company_case['id']));
			$claims = ($o_query ? $o_query->result_array() : array());

			$totalSumPaid = 0;
			$totalSumDue = 0;

			$forgivenAmountOnMainClaim = 0;
			$forgivenAmountExceptMainClaim = 0;
			foreach($claims as $claim) {
				$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt
				LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
				WHERE cmv.case_id = ? AND cmt.collecting_claim_line_type = '".$o_main->db->escape_str($claim['claim_type'])."' ORDER BY cmv.created DESC";
				$o_query = $o_main->db->query($s_sql, array($closed_company_case['id']));
				$transactions = ($o_query ? $o_query->result_array() : array());
				$amountToBePaid = $claim['amount'];
				foreach($transactions as $transaction) {
					$amountToBePaid+=$transaction['amount'];
				}
				if($claim['claim_type'] == 1 || $claim['payment_after_closed']) {
					$forgivenAmountOnMainClaim += $amountToBePaid;
				} else {
					$forgivenAmountExceptMainClaim += $amountToBePaid;
				}
			}


			$sql = "UPDATE collecting_company_cases SET
			updated = now(),
			updatedBy='script',
			forgivenAmountOnMainClaim = ?,
			forgivenAmountExceptMainClaim = ?
			WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($forgivenAmountOnMainClaim, $forgivenAmountExceptMainClaim, $closed_company_case['id']));
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<?php echo $formText_FixStoppedCaseData_output;?>
			<input type="submit" name="fix_stopped" value="Fix stopped case data">

		</div>
	</form>
</div>
