<?php

$case_id = $v_data['params']['case_id'];
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$username= $v_data['params']['username'];
$remove= $v_data['params']['remove'];
$onhold_comment= $v_data['params']['onhold_comment'];
$responsible_person= $v_data['params']['responsible_person'];

include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");
if($onhold_comment != ""){
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
            $s_sql = "INSERT INTO collecting_cases_objection  SET created = NOW(), createdBy = ?, stopped_by_creditor = 1, collecting_case_id = ?, message_from_debitor = ?, responsible_person_id = ?";
            $o_query = $o_main->db->query($s_sql, array($username, $case['id'], $onhold_comment, $responsible_person));
            if($o_query) {
                
                $s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
                WHERE collectingcase_id = '".$o_main->db->escape_str($case['id'])."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
                $o_query = $o_main->db->query($s_sql);
                //trigger reordering 
                $source_id = 4;
                process_open_cases_for_tabs($creditor['id'], $source_id);

                $v_return['status'] = 1;
            } else {
                $v_return['error'] = 'Error with updating database';
            }
        } else {
            $v_return['error'] = 'Case not found';
        }
    }
} else {
    $v_return['error'] = 'Missing fields';
}
?>
