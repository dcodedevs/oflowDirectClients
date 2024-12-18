<?php
include(__DIR__."/../../output/includes/fnc_create_case_from_transaction.php");

$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$checkCasesToProcess= $v_data['params']['checkCaseToProcess'];
$suggestedToProcess= $v_data['params']['suggestedToProcess'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['accounturl'];
$sign_agreement = $v_data['params']['sign_agreement'];
if($languageID == ""){
	$languageID = "no";
}
$variables->loggID = $username;

if($creditor_filter > 0) {
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
    $transactions_to_move = array();
    $cases_to_move = array();

    if(count($checkCasesToProcess) > 0) {
        // foreach($checkCasesToProcess as $checkCaseToProcess) {
        //     $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
        //     $o_query = $o_main->db->query($s_sql, array($checkCaseToProcess));
        //     $case = ($o_query ? $o_query->row_array() : array());
        //     if($case){
        //     }
        // }

        $reminderLevelOnly = 1;
        $manualProcessing = 1;
        $creditorId = $creditor_api['id'];
        $collecting_case_id = $checkCasesToProcess;

        include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
    }
    if($creditor_api['skip_reminder_go_directly_to_collecting'] == 0) {
        $casesReported = array();
        if(count($suggestedToProcess) > 0) {
            $newCaseCount = 0;
            foreach($suggestedToProcess as $singleToProcess) {
                $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($singleToProcess));
                $transaction = ($o_query ? $o_query->row_array() : array());
                if($transaction) {
                    $caseCreated = create_case_from_transaction($transaction['id'], $creditor_api['id'], $languageID, false);
                    if($caseCreated > 0) {
                        $newCaseCount++;
                        $casesReported[] = $caseCreated;
                    }
                }
            }
            // echo $newCaseCount." ".$formText_CasesCreated_output."<br/>";
            if(count($casesReported) > 0){
                $creditorId = $creditor_api['id'];
                $collecting_case_id = $casesReported;
                include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
            }
        }
    } else {
        $transactions_to_move = $suggestedToProcess;
    }

    if($sign_agreement) {
		$filename = "";
		$create_agreement_file = __DIR__ . '/../../api/protected/fnc_create_agreement_file.php';
		if (file_exists($create_agreement_file)) {
			include $create_agreement_file;
			$result = create_agreement_file($creditor_api['id']);
			$filename = $result['file'];
		}
		$read_agreement_sql = ", collecting_agreement_accepted_by = '".sanitize_escape($username)."', collecting_agreement_accepted_date = NOW(), collecting_agreement_file = '".sanitize_escape($filename)."'";
		$s_sql = "UPDATE creditor SET
		updated = now(),
		updatedBy= ?".$read_agreement_sql."
		WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($username, $creditor_api['id']));

		$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_api['id']));
		$creditor_api = ($o_query ? $o_query->row_array() : array());
	}
    if(count($cases_to_move) > 0 || count($transactions_to_move) > 0){     
        if($creditor_api['collecting_agreement_accepted_date'] != "" && $creditor_api['collecting_agreement_accepted_date'] != "0000-00-00 00:00:00") {

            $creditor = $creditor_api;       
            $s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
            $o_query = $o_main->db->query($s_sql);
            $system_settings = ($o_query ? $o_query->row_array() : array());

            if($creditor['skip_reminder_go_directly_to_collecting'] == 0) {
                $s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($system_settings['default_collecting_process_to_move_from_reminder']));
                $collectingProcess = ($o_query ? $o_query->row_array() : array());
            } else {
                if($creditor['collecting_process_to_move_from_reminder'] > 0) {
                    $s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($creditor['collecting_process_to_move_from_reminder']));
                    $collectingProcess = ($o_query ? $o_query->row_array() : array());
                } else {				
                    $s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($system_settings['default_collecting_process_to_move_from_reminder']));
                    $collectingProcess = ($o_query ? $o_query->row_array() : array());
                }
            }
            $proccessedAmount = 0;
            if($collectingProcess) {
                require(__DIR__."/../../output/includes/fnc_move_transaction_to_collecting.php");
                $transactionsToBeProcessed = array();
                foreach($transactions_to_move as $checkCaseToProcess) {
                    $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND id = ? AND creditor_id = ? ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($checkCaseToProcess, $creditor['id']));
                    $transaction = ($o_query ? $o_query->row_array() : array());

                    if($transaction){
                        $transactionsToBeProcessed[] = $transaction;
                    }
                }
                foreach($cases_to_move as $checkCaseToProcess) {
                    
                    $s_sql = "SELECT collecting_cases.*, 
                    IF(IFNULL(profile.collecting_process_move_to, 0) = 0, IFNULL(stepProcess.collecting_process_move_to,0), profile.collecting_process_move_to) as collectingProcessToMoveTo 
                    FROM collecting_cases 
                    LEFT JOIN creditor_reminder_custom_profiles profile ON profile.id = collecting_cases.reminder_profile_id
                    LEFT JOIN collecting_cases_process_steps step2 ON step2.id = collecting_cases.collecting_cases_process_step_id AND step2.collecting_cases_process_id = profile.reminder_process_id
                    LEFT JOIN collecting_cases_process stepProcess ON step2.collecting_cases_process_id = stepProcess.id
                    WHERE collecting_cases.id = ?";
                    $o_query = $o_main->db->query($s_sql, array($checkCaseToProcess));
                    $case = ($o_query ? $o_query->row_array() : array());

                    $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
                    $transaction = ($o_query ? $o_query->row_array() : array());
                    if($transaction) {
                        $transaction['collectingProcessToMoveTo'] = $case['collectingProcessToMoveTo'];
                        $transactionsToBeProcessed[] = $transaction;
                    }
                }
                foreach($transactionsToBeProcessed as $transaction){
                    $processId = $collectingProcess['id'];
                    if($transaction['collectingProcessToMoveTo'] > 0){
                        $processId = $transaction['collectingProcessToMoveTo'];
                    }
                    $v_return = move_transaction_to_collecting($transaction['id'], $processId, $username);
                    if($v_return['status']){
                        $proccessedAmount++;
                    } else {
                        foreach($v_return['error'] as $error){
                            echo $error."<br/>";
                        }
                    }
                }
                $creditorId = $creditor['id'];
                $fromProcessCases = true;
                include(__DIR__."/../../output/includes/import_scripts/import_cases2.php");
            } else {
                echo $formText_MissingProcess_output."<br/>";
            }
            
            echo $proccessedAmount." ".$formText_CasesProcessed_output;
        } else {            
		    $v_return['not_signed'] = 1;
        }
    }
    $result_output = ob_get_contents();
    $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
    ob_end_clean();
    $v_return['html'] = $result_output;
    $v_return['cases_created_count'] = $newCaseCount;
    $v_return['cases_processed_count'] = $casesReported;
    $v_return['created_letters_count'] = $created_letters;
    $v_return['emails_sent_count'] = $emails_sent;

    $v_return['status'] = 1;
}

?>
