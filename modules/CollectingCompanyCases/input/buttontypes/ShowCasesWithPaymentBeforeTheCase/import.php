<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('max_execution_time', 120);

if(isset($_POST['detectSalary'])) {
	$s_sql = "SELECT collecting_company_cases.* FROM collecting_company_cases_claim_lines
	JOIN collecting_company_cases ON collecting_company_cases.id = collecting_company_cases_claim_lines.collecting_company_case_id
	WHERE collecting_company_cases_claim_lines.claim_type = 1 AND collecting_company_cases_claim_lines.amount <> collecting_company_cases_claim_lines.original_amount 
	GROUP BY collecting_company_cases.id";
	$o_query = $o_main->db->query($s_sql);
	$cases = ($o_query ? $o_query->result_array() : array());
	$addedClaimlines = 0;
	$addedCreditClaimlines = 0;
	foreach($cases as $case) {
		$sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND (claim_type = 18 OR claim_type = 15)";
		$o_query = $o_main->db->query($sql, array($case['id']));
		$has_payment = $o_query ? $o_query->row_array() : array();
		if($has_payment){
			echo "<a href='".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$case['id']."' target='_blank'>".$case['id'].' - has payment while payment was deducted</a><br/>';
		}
	}

}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="detectSalary" value="Show Cases with payment before the case">

			<!-- <input type="submit" name="changeAssociation" value="Change Associations"> -->
		</div>
	</form>
</div>
