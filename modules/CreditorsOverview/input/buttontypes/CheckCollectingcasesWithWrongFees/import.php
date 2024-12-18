<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['checkCollectingcasesWithWrongFees'])) {
		$s_sql = "SELECT collecting_company_cases.* FROM creditor
		LEFT OUTER JOIN collecting_company_cases ON collecting_company_cases.creditor_id = creditor.id
		LEFT OUTER JOIN customer ON collecting_company_cases.debitor_id = customer.id
		LEFT OUTER JOIN collecting_company_cases_claim_lines ON collecting_company_case_id = collecting_company_cases.id
		WHERE (collecting_company_cases_claim_lines.claim_type = '4' OR collecting_company_cases_claim_lines.claim_type = '5' OR collecting_company_cases_claim_lines.claim_type = '6' OR collecting_company_cases_claim_lines.claim_type = '7') AND customer.customer_type_for_collecting_cases = 2 AND collecting_company_cases.collecting_cases_process_step_id > 0 AND case_closed_date IS NOT NULL AND case_closed_date <> '0000-00-00'
		GROUP BY collecting_company_cases.id";
		$o_query = $o_main->db->query($s_sql);
		$closed_collecting_cases = ($o_query ? $o_query->result_array() : array());
		echo count($closed_collecting_cases)." Collecting cases closed<br/>";
		foreach($closed_collecting_cases as $closed_collecting_case) {
			echo "<a href='".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$closed_collecting_case['id']."' target='_blank'>".$closed_collecting_case['id'].'</a> '. $formText_ForgivenAmountExceptMainClaim_output.": ". $closed_collecting_case['forgivenAmountExceptMainClaim'].'<br/>';
		}
		echo '<br/><br/><br/>';
		$s_sql = "SELECT collecting_company_cases.* FROM creditor
		LEFT OUTER JOIN collecting_company_cases ON collecting_company_cases.creditor_id = creditor.id
		LEFT OUTER JOIN customer ON collecting_company_cases.debitor_id = customer.id
		LEFT OUTER JOIN collecting_company_cases_claim_lines ON collecting_company_case_id = collecting_company_cases.id
		WHERE (collecting_company_cases_claim_lines.claim_type = '4' OR collecting_company_cases_claim_lines.claim_type = '5' OR collecting_company_cases_claim_lines.claim_type = '6' OR collecting_company_cases_claim_lines.claim_type = '7') AND customer.customer_type_for_collecting_cases = 2 AND collecting_company_cases.collecting_cases_process_step_id > 0 AND (case_closed_date IS NULL OR case_closed_date = '0000-00-00')
		GROUP BY collecting_company_cases.id";
		$o_query = $o_main->db->query($s_sql);
		$open_collecting_cases = ($o_query ? $o_query->result_array() : array());
		echo count($open_collecting_cases)." Collecting cases open<br/>";

		foreach($open_collecting_cases as $open_collecting_cases) {
			echo "<a href='".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$open_collecting_cases['id']."' target='_blank'>".$open_collecting_cases['id'].'</a> '. $formText_ForgivenAmountExceptMainClaim_output.": ". $open_collecting_cases['forgivenAmountExceptMainClaim'].'<br/>';
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="checkCollectingcasesWithWrongFees" value="Check collecting company cases with wrong fees (person)">
		</div>
	</form>
</div>
