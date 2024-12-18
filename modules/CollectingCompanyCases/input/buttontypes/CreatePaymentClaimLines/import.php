<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('max_execution_time', 120);

if(isset($_POST['detectSalary'])) {
	$s_sql = "SELECT * FROM collecting_company_cases ORDER BY id";
	$o_query = $o_main->db->query($s_sql);
	$cases = ($o_query ? $o_query->result_array() : array());
	$addedClaimlines = 0;
	$addedCreditClaimlines = 0;
	foreach($cases as $case) {
		$sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND collecting_company_case_id = ?";
		$o_query = $o_main->db->query($sql, array($case['creditor_id'], $case['id']));
		$local_transactions = $o_query ? $o_query->result_array() : array();
		foreach($local_transactions as $local_transaction){
			$sql = "SELECT * FROM creditor_transactions WHERE link_id = ? AND system_type = 'Payment' AND creditor_id = ? AND date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($case['created'])))."'";

			$o_query = $o_main->db->query($sql, array($local_transaction['link_id'], $local_transaction['creditor_id']));
			$payment_transactions = $o_query ? $o_query->result_array() : array();
			foreach($payment_transactions as $payment_transaction){
				if(intval($payment_transaction['company_claimline_id']) == 0){
					$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
					id=NULL,
					moduleID = ?,
					created = now(),
					createdBy= ?,
					collecting_company_case_id = ?,
					name= ?,
					claim_type='".$o_main->db->escape_str("15")."',
					amount= '".$o_main->db->escape_str($payment_transaction['amount'])."'";
					$o_query = $o_main->db->query($s_sql, array($moduleID, 'import', $local_transaction['collecting_company_case_id'], $formText_DirectPaymentToCreditor_output." ".date("d.m.Y", strtotime($payment_transaction['date']))));
					if($o_query) {
						$claimline_id = $o_main->db->insert_id();
						if($claimline_id > 0) {
							$s_sql = "UPDATE creditor_transactions SET company_claimline_id = ? WHERE id = '".$o_main->db->escape_str($payment_transaction['id'])."'";
							$o_query = $o_main->db->query($s_sql, array($claimline_id));
							$addedClaimlines++;
						}
					}
				}
			}

			$sql = "SELECT * FROM creditor_transactions WHERE link_id = ? AND system_type = 'CreditnoteCustomer' AND creditor_id = ? AND date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($case['created'])))."'";

			$o_query = $o_main->db->query($sql, array($local_transaction['link_id'], $local_transaction['creditor_id']));
			$payment_transactions = $o_query ? $o_query->result_array() : array();
			foreach($payment_transactions as $payment_transaction){
				if(intval($payment_transaction['company_claimline_id']) == 0){
					$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
					id=NULL,
					moduleID = ?,
					created = now(),
					createdBy= ?,
					collecting_company_case_id = ?,
					name= ?,
					claim_type='".$o_main->db->escape_str("16")."',
					amount= '".$o_main->db->escape_str($payment_transaction['amount'])."'";
					$o_query = $o_main->db->query($s_sql, array($moduleID, 'import', $local_transaction['collecting_company_case_id'], $formText_CreditInvoice_output." ".$payment_transaction['invoice_nr']));
					if($o_query) {
						$claimline_id = $o_main->db->insert_id();
						if($claimline_id > 0) {
							$s_sql = "UPDATE creditor_transactions SET company_claimline_id = ? WHERE id = '".$o_main->db->escape_str($payment_transaction['id'])."'";
							$o_query = $o_main->db->query($s_sql, array($claimline_id));
							$addedCreditClaimlines++;
						}
					}
				}
			}
		}
	}
	echo $addedClaimlines." payment claimlines were created<br/>";
	echo $addedCreditClaimlines." credit claimlines were created";

}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="detectSalary" value="Create payment claim lines">

			<!-- <input type="submit" name="changeAssociation" value="Change Associations"> -->
		</div>
	</form>
</div>
