<?php
$languageID = $variables->languageID;
if(count($_POST['process_case'])> 0){
	foreach($_POST['process_case'] as $caseId){
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
			$casesToGenerate = array();
			$manualProcessing = 1;
			$creditorId = $creditor['id'];
			$collecting_case_id = $case['id'];
			include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_cases_collecting.php");

			// if(count($casesToGenerate) > 0) {
			//     $v_return['log'] = $log;
			// 	$_POST['casesToGenerate'] = $casesToGenerate;
			//     include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_actions.php");
			// }
			$result_output = ob_get_contents();
			$result_output = trim(preg_replace('/\s\s+/', '', $result_output));
			ob_end_clean();
			echo $result_output;
		} else {
			echo $formText_MissingCase_output;
		}
	}
} else {
	echo $formText_CasesNotSelected_output;
}
?>
