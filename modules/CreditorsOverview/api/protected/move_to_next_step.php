<?php

$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$case_id= $v_data['params']['case_id'];
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
        $creditorId = $creditor['id'];
        $collecting_level_to_move_from = $case['collectinglevel'];
        include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
        $v_return['log'] = $log;
        $v_return['status'] = 1;
    } else {
        $v_return['error'] = 'Case not found';
    }
}

?>
