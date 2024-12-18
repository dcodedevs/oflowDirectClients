<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['checkEmptyLetters'])) {
		
		// $sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
		// WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 AND cr.loc_customer_difference is null 
		// AND IFNULL(cr.onboarding_incomplete, 0) = 0 ORDER BY id ASC LIMIT 50";
		$sql = "SELECT collecting_cases_claim_letter.* FROM collecting_cases_claim_letter
		WHERE created > '2024-11-05'";
		$o_query = $o_main->db->query($sql);
		$claimletters = $o_query ? $o_query->result_array() : array();
		$updatedLetterCount = 0;
		foreach($claimletters as $letter) {
			if($letter['pdf'] != ""){
				if(filesize(__DIR__."/../../../../../".$letter['pdf']) == 0) {
					if($letter['collecting_company_case_id'] > 0){
						include_once(__DIR__."/../../../../CollectingCompanyCases/output/includes/fnc_calculate_interest.php");
						include_once(__DIR__."/../../../../CollectingCompanyCases/output/includes/fnc_generate_pdf.php");
					} else {
						include_once(__DIR__."/../../../../CollectingCases/output/includes/fnc_calculate_interest.php");
						include_once(__DIR__."/../../../../CollectingCases/output/includes/fnc_generate_pdf.php");
					}

					$result = generate_pdf_from_letter($letter['id'], $letter['rest_note']);
					if($result['item']){
						$sql = "UPDATE collecting_cases_claim_letter SET sending_status= -2
						WHERE id = ?";
						$o_query = $o_main->db->query($sql, array($letter['id']));
						$updatedLetterCount++;
					}
				}
			}
		}
		echo $updatedLetterCount;
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="checkEmptyLetters" value="check empty letters">
		</div>
	</form>
</div>
