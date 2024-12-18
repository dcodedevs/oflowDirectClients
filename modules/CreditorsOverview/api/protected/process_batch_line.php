<?php
include(__DIR__."/../../output/includes/fnc_create_case_from_transaction.php");
$creditor_filter = $v_data['params']['creditor_filter'];
$batch_id = $v_data['params']['batch_id'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['accounturl'];
if($languageID == "") {
	$languageID = "no";
}

include(__DIR__."/../languagesOutput/default.php");
if(is_file(__DIR__."/../languagesOutput/".$languageID.".php")) {
	include(__DIR__."/../languagesOutput/".$languageID.".php");
}

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_filter));
$creditor_api = ($o_query ? $o_query->row_array() : array());
if($creditor_api){
	$s_sql = "SELECT * FROM creditor_processing_batch WHERE id = ? AND creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($batch_id, $creditor_filter));
	$creditor_processing_batch = $o_query ? $o_query->row_array() : array();
	if($creditor_processing_batch) {
		if($creditor_processing_batch['processing_status'] == 0){
			$s_sql = "SELECT * FROM creditor_processing_batch_line WHERE creditor_processing_batch_id = ? AND IFNULL(status, 0) = 1";
			$o_query = $o_main->db->query($s_sql, array($creditor_processing_batch['id']));
			$active_processing_line = $o_query ? $o_query->row_array() : array();
			if(!$active_processing_line) {
				$s_sql = "SELECT * FROM creditor_processing_batch_line WHERE creditor_processing_batch_id = ? AND IFNULL(status, 0) = 0 ORDER BY id ASC";
				$o_query = $o_main->db->query($s_sql, array($creditor_processing_batch['id']));
				$next_line_to_process = $o_query ? $o_query->row_array() : array();
				if($next_line_to_process) {
					$s_sql = "UPDATE creditor_processing_batch_line SET updated =NOW(), updatedBy = ?, status = 1 WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($username, $next_line_to_process['id']));
					if($o_query){
						$s_sql = "SELECT * FROM creditor_transactions WHERE id = ? AND creditor_id = ?";
						$o_query = $o_main->db->query($s_sql, array($next_line_to_process['transaction_id'], $creditor_processing_batch['creditor_id']));
						$transaction = $o_query ? $o_query->row_array() : array();
						$status_message = "";
					    ob_start();
					    include(__DIR__."/../../output/languagesOutput/default.php");
					    if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
					        include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
					    }
						if($transaction) {
							if($transaction['collectingcase_id'] > 0) {
								$s_sql = "SELECT * FROM collecting_cases WHERE id = ? AND creditor_id = ?";
								$o_query = $o_main->db->query($s_sql, array($transaction['collectingcase_id'], $transaction['creditor_id']));
								$collecting_case = $o_query ? $o_query->row_array() : array();
								if($collecting_case) {
									$reminderLevelOnly = 1;
							        $manualProcessing = 1;
							        $creditorId = $creditor_api['id'];
							        $collecting_case_id = array($collecting_case['id']);

							        include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
								} else {
									echo $formText_MissingCollectingCases_output;
								}
							} else {
								$casesReported = array();
								$newCaseCount = 0;
				                $caseCreated = create_case_from_transaction($transaction['id'], $creditor_api['id'], $languageID, false);
				                if($caseCreated > 0) {
				                    $newCaseCount++;
				                    $casesReported[] = $caseCreated;
				                }
								if(count($casesReported) > 0) {
							        $creditorId = $creditor_api['id'];
							        $collecting_case_id = $casesReported;
							        include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
								}
							}
						} else {
							echo $formText_MissingTransasction_output;
						}

					    $result_output = ob_get_contents();
					    $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
					    ob_end_clean();
					    $status_message = $result_output;
						$s_sql = "UPDATE creditor_processing_batch_line SET updated =NOW(), updatedBy = ?, status = 2, status_message = ? WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($username, $status_message, $next_line_to_process['id']));

						$v_return['status'] = 1;

						$s_sql = "SELECT * FROM creditor_processing_batch_line WHERE creditor_processing_batch_id = ? AND IFNULL(status, 0) = 0 ORDER BY id ASC";
						$o_query = $o_main->db->query($s_sql, array($creditor_processing_batch['id']));
						$next_line_to_process = $o_query ? $o_query->row_array() : array();
						if(!$next_line_to_process) {
							$s_sql = "UPDATE creditor_processing_batch SET processing_status = 1 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditor_processing_batch['id']));
							$html = "";

							$s_sql = "SELECT * FROM creditor_processing_batch_line WHERE creditor_processing_batch_id = ? AND IFNULL(status, 0) = 2 ORDER BY id ASC";
							$o_query = $o_main->db->query($s_sql, array($creditor_processing_batch['id']));
							$lines = $o_query ? $o_query->result_array() : array();
							foreach($lines as $line){
								$html.=$line['status_message'];
							}
							$v_return['result'] = $html;
						}
					}
				} else {
					$s_sql = "UPDATE creditor_processing_batch SET processing_status = 1 WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor_processing_batch['id']));
				}
			} else {
				$v_return['error'] = $formText_StillProcessing_output;
			}
		} else {
			$v_return['error'] = $formText_BatchNotActive_output;
		}
	} else {
		$v_return['error'] = $formText_MissingBatch_output;
	}
} else {
	$v_return['error'] = $formText_MissingCreditor_output;
}

?>
