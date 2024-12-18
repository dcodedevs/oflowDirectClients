<?php

$case_id = $v_data['params']['case_id'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$username= $v_data['params']['username'];
$remove= $v_data['params']['remove'];
$onhold_comment= $v_data['params']['onhold_comment'];

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
        if($remove){
            $s_sql = "UPDATE collecting_cases SET onhold_by_creditor = 0, onhold_comment = '' WHERE id = '".$o_main->db->escape_str($case['id'])."'";
            $o_query = $o_main->db->query($s_sql);
        } else {
            $s_sql = "UPDATE collecting_cases SET onhold_by_creditor = 1, onhold_comment = '".$o_main->db->escape_str($onhold_comment)."' WHERE id = '".$o_main->db->escape_str($case['id'])."'";
            $o_query = $o_main->db->query($s_sql);
        }
        if($o_query){
            $v_return['status'] = 1;
        } else {
            $v_return['error'] = 'Error with updating database';
        }
    } else {
        $v_return['error'] = 'Case not found';
    }
}
?>
