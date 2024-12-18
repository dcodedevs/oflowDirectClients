<?php

$case_id = $v_data['params']['case_id'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$username= $v_data['params']['username'];

if($creditor_filter > 0){
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_filter));
	$creditor = ($o_query ? $o_query->row_array() : array());
} else {
	$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$creditor = ($o_query ? $o_query->row_array() : array());
}
if($creditor){
    $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($case_id));
    $case = ($o_query ? $o_query->row_array() : array());
    if($case){
        $s_sql = "UPDATE collecting_cases SET approved_by_creditor = '".$o_main->db->escape_str($case['needs_creditor_approval'])."', needs_creditor_approval = 0, onhold_by_creditor = 0 WHERE id = '".$o_main->db->escape_str($case['id'])."'";
        $o_query = $o_main->db->query($s_sql);

        if($o_query) {
            $s_sql = "INSERT INTO collecting_cases_process_approve_log SET id=NULL, createdBy='".$o_main->db->escape_str($username)."', created=NOW(), collecting_case_id='".$o_main->db->escape_str($case['id'])."',
            approved_to_step='".$case['needs_creditor_approval']."'";
            $o_query = $o_main->db->query($s_sql);

        }

        $v_return['status'] = 1;
    } else {
        $v_return['error'] = 'Case not found';
    }
}
?>
