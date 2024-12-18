<?php

$case_id = $v_data['params']['case_id'];
$due_date = $v_data['params']['due_date'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['extradomaindirroot'];
if($languageID == ""){
	$languageID = "no";
}

$sql = "SELECT * FROM collecting_cases WHERE id = '".$o_main->db->escape_str($case_id)."'";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();
include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");
if($caseData) {
    $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
    $creditor = ($o_query ? $o_query->row_array() : array());

    if($creditor_filter == $creditor['id']) {
        ob_start();
        include(__DIR__."/../languagesOutput/default.php");
        if(is_file(__DIR__."/../languagesOutput/".$languageID.".php")){
            include(__DIR__."/../languagesOutput/".$languageID.".php");
        }
		if($due_date != ""){
	        $s_sql = "UPDATE collecting_cases SET due_date='".date("Y-m-d", strtotime($o_main->db->escape_str($due_date)))."'
	        WHERE id = '".$o_main->db->escape_str($caseData['id'])."'";
	        $o_query = $o_main->db->query($s_sql);

	        if($o_query){
	            echo $formText_DueDateSuccessfullyUpdated_output;
                
                //trigger reordering 
                $s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
                WHERE collectingcase_id = '".$o_main->db->escape_str($caseData['id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
                $o_query = $o_main->db->query($s_sql);
                process_open_cases_for_tabs($creditor['id']);
	        } else {
	            echo $formText_ErrorUpdating_output;
	        }
		} else {
			echo $formText_DueDateMissing_output;
		}

        $result_output = ob_get_contents();
        $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
        ob_end_clean();
        $v_return['html'] = $result_output;
        $v_return['status'] = 1;
    } else {
        $v_return['error'] = 'Wrong customer';
    }
} else {
    $v_return['error'] = 'Missing case';
}
?>
