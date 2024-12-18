<?php
$languageID = $variables->languageID;
if(count($_POST['process_case_confirm'])> 0){
	$confirmed = 0;
	foreach($_POST['process_case_confirm'] as $caseId){
		$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($caseId));
		$case = ($o_query ? $o_query->row_array() : array());
		if($case){
			ob_start();
			include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/default.php");
			if(is_file(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php")){
				include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php");
			} else {
				include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/no.php");
			}

			$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
			$o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
			$creditor = ($o_query ? $o_query->row_array() : array());

			$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
			$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
			$debitor = ($o_query ? $o_query->row_array() : array());
			$is_company = false;
		 	if($debitor['customer_type_for_collecting_cases'] == 0) {
				$customer_type_collect_debitor = $debitor['customer_type_collect'];
				if($debitor['customer_type_collect_addition'] > 0) {
					$customer_type_collect_debitor = $debitor['customer_type_collect_addition'] - 1;
				}
				if($customer_type_collect_debitor == 0) {
					$is_company = true;
				} else if($customer_type_collect_debitor == 1) {
				}

			 } else if($debitor['customer_type_for_collecting_cases'] == 1) {
				 $is_company = true;
			 } else if($debitor['customer_type_for_collecting_cases'] == 2) {
			}
			if($is_company){
				if($debitor['confirmed_as_company'] == '0000-00-00' || $debitor['confirmed_as_company'] == ''){
					$s_sql = "SELECT * FROM customer WHERE id = ?";
				    $o_query = $o_main->db->query($s_sql, array($debitor['id']));
				    if($o_query && $o_query->num_rows() == 1) {
						$s_sql = "UPDATE customer SET
						confirmed_as_company= NOW(),
						confirmed_by = ?
						WHERE id = ?";
						$o_main->db->query($s_sql, array($variables->loggID,  $debitor['id']));
						$confirmed++;
					}
				}
			}
		} else {
			echo $formText_MissingCase_output;
		}
	}
	echo $confirmed." ".$formText_CompaniesConfirmed_output;
} else {
	echo $formText_CasesNotSelected_output;
}
?>
