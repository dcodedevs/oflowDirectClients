<?php

$case_id = $v_data['params']['case_id'];
$customer_id = $v_data['params']['customer_id'];


$sql = "SELECT * FROM collecting_cases WHERE id = '".$o_main->db->escape_str($case_id)."'";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();
$status = $caseData['status'];
if($caseData['status'] == 0){
    $status = 1;
}

$s_sql = "SELECT collecting_cases_process_steps.* FROM collecting_cases_process_steps
WHERE collecting_cases_process_steps.collecting_cases_process_id = '".$o_main->db->escape_str($caseData['collecting_cases_process_id'])."'
AND collecting_cases_process_steps.status_id = '".$o_main->db->escape_str($status)."'
ORDER BY collecting_cases_process_steps.sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$steps = $o_query ? $o_query->result_array() : array();

$v_return['status'] = 1;
$v_return['steps'] = $steps;
$v_return['caseData'] = $caseData;
?>
