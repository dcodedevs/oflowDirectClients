<?php

$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$checkCasesToProcess= $v_data['params']['checkCaseToProcess'];
$suggestedToProcess= $v_data['params']['suggestedToProcess'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['accounturl'];
if($languageID == ""){
	$languageID = "no";
}
$variables->loggID = $username;

if($creditor_filter > 0){
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_filter));
	$creditor_api = ($o_query ? $o_query->row_array() : array());
} else {
	$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$creditor_api = ($o_query ? $o_query->row_array() : array());
}
if($creditor_api) {
    ob_start();
    include(__DIR__."/../../output/languagesOutput/default.php");
    if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
        include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
    }
	$cases_to_be_processed_count = 0;
    $s_sql = "SELECT * FROM creditor_processing_batch WHERE creditor_id = ? AND IFNULL(processing_status, 0) <> 1";
    $o_query = $o_main->db->query($s_sql, array($creditor_api['id']));
    $active_batch_count = ($o_query ? $o_query->num_rows() : 0);
	if($active_batch_count == 0) {
		$cases_to_be_processed_count = 0;

		$s_sql = "INSERT INTO creditor_processing_batch SET created = NOW(), createdBy = ?, creditor_id = ?, processing_status = 0";
	    $o_query = $o_main->db->query($s_sql, array($username, $creditor_api['id']));
	    if($o_query){
			$batch_id = $o_main->db->insert_id();
			if(count($checkCasesToProcess) > 0) {
				foreach($checkCasesToProcess as $checkCaseToProcess) {
					$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND collectingcase_id = ?";
				    $o_query = $o_main->db->query($s_sql, array($creditor_api['id'], $checkCaseToProcess));
				    $transaction = ($o_query ? $o_query->row_array() : array());
					if($transaction){
						$s_sql = "INSERT INTO creditor_processing_batch_line SET created = NOW(), createdBy = ?, transaction_id = ?, creditor_processing_batch_id = ?";
					    $o_query = $o_main->db->query($s_sql, array($username, $transaction['id'], $batch_id));
						if($o_query) {
							$cases_to_be_processed_count++;
						}
					}
				}
			}

		    if(count($suggestedToProcess) > 0) {
				foreach($suggestedToProcess as $singleToProcess) {
					$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
				    $o_query = $o_main->db->query($s_sql, array($singleToProcess));
				    $transaction = ($o_query ? $o_query->row_array() : array());
					if($transaction) {
						$s_sql = "INSERT INTO creditor_processing_batch_line SET created = NOW(), createdBy = ?, transaction_id = ?, creditor_processing_batch_id = ?";
					    $o_query = $o_main->db->query($s_sql, array($username, $transaction['id'], $batch_id));
						if($o_query) {
							$cases_to_be_processed_count++;
						}
					}
				}
			}
			if($cases_to_be_processed_count > 0){
			    $v_return['cases_to_be_processed_count'] = $cases_to_be_processed_count;
			    $v_return['batch_id'] = $batch_id;
			    $v_return['status'] = 1;
			} else {
				$s_sql = "UPDATE creditor_processing_batch SET processing_status = 1, updated = NOW(), updatedBy = ? WHERE id = ?";
			    $o_query = $o_main->db->query($s_sql, array($username, $creditor_api['id']));
				$v_return['error'] = 'no cases selected';
			}
		}
	} else {
		$v_return['status'] = 0;
		$v_return['error'] = 'Currently cases being processed, try again later';
	}
    if(count($checkCasesToProcess) > 0) {
        // foreach($checkCasesToProcess as $checkCaseToProcess) {
        //     $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
        //     $o_query = $o_main->db->query($s_sql, array($checkCaseToProcess));
        //     $case = ($o_query ? $o_query->row_array() : array());
        //     if($case){
        //     }
        // }

        // $reminderLevelOnly = 1;
        // $manualProcessing = 1;
        // $creditorId = $creditor_api['id'];
        // $collecting_case_id = $checkCasesToProcess;
		//
        // include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
    }
    $casesReported = array();
    if(count($suggestedToProcess) > 0) {
        // $newCaseCount = 0;
        // foreach($suggestedToProcess as $singleToProcess) {
        //     $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
        //     $o_query = $o_main->db->query($s_sql, array($singleToProcess));
        //     $transaction = ($o_query ? $o_query->row_array() : array());
        //     if($transaction) {
        //         $caseCreated = create_case_from_transaction($transaction['id'], $creditor_api['id'], $languageID, false);
        //         if($caseCreated > 0) {
        //             $newCaseCount++;
        //             $casesReported[] = $caseCreated;
        //         }
        //     }
        // }
        // echo $newCaseCount." ".$formText_CasesCreated_output."<br/>";
		// if(count($casesReported) > 0){
	    //     $creditorId = $creditor_api['id'];
	    //     $collecting_case_id = $casesReported;
	    //     include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
		// }
    }
    // $result_output = ob_get_contents();
    // $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
    // ob_end_clean();
    // $v_return['html'] = $result_output;
}

?>
