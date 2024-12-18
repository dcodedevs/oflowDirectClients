<?php
$creditor_filter = $v_data['params']['creditor_filter'];
$batch_id = $v_data['params']['batch_id'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['accounturl'];
if($languageID == ""){
	$languageID = "no";
}

include(__DIR__."/../languagesOutput/default.php");
if(is_file(__DIR__."/../languagesOutput/".$languageID.".php")) {
	include(__DIR__."/../languagesOutput/".$languageID.".php");
}

$s_sql = "UPDATE creditor_processing_batch SET updated = NOW(), updatedBy = ?, processing_status = 1 WHERE creditor_id= ? AND id = ? AND processing_status <> 1";
$o_query = $o_main->db->query($s_sql, array($username, $creditor_filter, $batch_id));
if($o_query){
	$s_sql = "UPDATE creditor_processing_batch_line SET updated = NOW(), updatedBy = ?, status = 2, status_message = ? WHERE creditor_processing_batch_id = ?";
	$o_query = $o_main->db->query($s_sql, array($username, $formText_ProcessingCanceled_output."<br/>", $batch_id));
	$v_return['status'] = 1;
} else {
	$v_return['error'] = $formText_ErrorPausingBatch_output;
}
?>
