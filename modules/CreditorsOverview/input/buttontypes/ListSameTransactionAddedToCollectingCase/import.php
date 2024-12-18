<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if($_POST['list']){
		$s_sql = "SELECT COUNT(collecting_company_cases_claim_lines.id) as duplicated, collecting_company_cases_claim_lines.collecting_company_case_id  FROM collecting_company_cases_claim_lines 
		WHERE claim_type = 1 AND invoice_nr > 0		
		GROUP BY invoice_nr, collecting_company_case_id
		HAVING duplicated > 1" ;
		$o_query = $o_main->db->query($s_sql);
		$cases = ($o_query ? $o_query->result_array() : array());
		foreach($cases as $caseData){
			echo "<a href='".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$caseData['collecting_company_case_id']."' target='_blank'>".$caseData['collecting_company_case_id'].' - has duplicate main transactions connected '.$caseData['duplicated'].'</a><br/>';
		}
	}	
	if(isset($_POST['checkCreditorNames'])) {
		$s_sql = "SELECT cmt.*,cmv.case_id FROM cs_mainbook_transaction cmt
		JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		WHERE cmt.bookaccount_id = 20 AND IFNULL(cmt.used_as_settlement_payment,0) = 0 ORDER BY cmt.created DESC" ;
		$o_query = $o_main->db->query($s_sql);
		$mainclaim_to_creditor_transactions = ($o_query ? $o_query->result_array() : array());

		foreach($mainclaim_to_creditor_transactions as $mainclaim_to_creditor_transaction){
			$caseWithoutClaimlines = false;
			$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($mainclaim_to_creditor_transaction['case_id']));
			$caseData = ($o_query ? $o_query->row_array() : array());
			if($caseData){
				$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND claim_type = 15";
				$o_query = $o_main->db->query($s_sql, array($caseData['id']));
				$claimlines = ($o_query ? $o_query->result_array() : array());
				if(count($claimlines) == 0){
					$caseWithoutClaimlines = true;
				}
				if($caseWithoutClaimlines){
					$s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = ? AND creditor_id = ?";
					$o_query = $o_main->db->query($s_sql, array($caseData['id'], $caseData['creditor_id']));
					$main_transactions = ($o_query ? $o_query->result_array() : array());

					$link_ids = array();
					foreach($main_transactions as $main_transaction){
						if($main_transaction['link_id'] != ""){
							$link_ids[] = $main_transaction['link_id'];
						}
					}
					$hasPayments = false;
					if(count($link_ids) > 0){
						$s_sql = "SELECT * FROM creditor_transactions WHERE link_id IN('".implode(",", $link_ids)."') AND creditor_id = ? AND system_type='Payment'";
						$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
						$payment_transactions = ($o_query ? $o_query->result_array() : array());
						if(count($payment_transactions) > 0){
							$hasPayments = true;							
							$lastPaymentCreatedDate = "";
							foreach($payment_transactions as $payment_transaction){
								if(strtotime($payment_transaction['created']) > strtotime($lastPaymentCreatedDate)){
									$lastPaymentCreatedDate = $payment_transaction['created'];
								}
							}
						}
					}
					if($hasPayments){
						echo "<a href='".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$caseData['id']."' target='_blank'>".$caseData['id'].' - has linked payment</a>';

						if($caseData['case_closed_date'] != "0000-00-00" && $caseData['case_closed_date'] != "") {
							echo " - case closed ";
						} else {
							echo " - case open ";
						}
						echo " - payment created ".date("d.m.Y", strtotime($lastPaymentCreatedDate));
						echo "<br/>";
					} else {
						// echo "<a href='".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$caseData['id']."' target='_blank'>".$formText_CaseMissingPayment_output." " .$caseData['id'].'</a><br/>';	
					}
				}
			}
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="list" value="List company cases with same transactions">
		</div>
	</form>
</div>
