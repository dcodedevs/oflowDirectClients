<?php
include(__DIR__."/../languagesOutput/default.php");

$objection_id = $v_data['params']['objection_id'];
$username = $v_data['params']['username'];
$collecting_case_id = $v_data['params']['collecting_case_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$objection_closed_handling_description = $v_data['params']['objection_closed_handling_description'];

include_once(__DIR__."/../../output/includes/fnc_process_open_cases_for_tabs.php");

if($objection_closed_handling_description != "") {
    if(isset($objection_id) && $objection_id > 0)
    {
        $s_sql = "SELECT * FROM collecting_cases_objection WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($objection_id));
        if($o_query && $o_query->num_rows() == 1) {
            $objection = $o_query->row_array();
            $s_sql = "UPDATE collecting_cases_objection SET
            updated = now(),
            updatedBy= ?,
            objection_closed_date = NOW(),
            objection_closed_by = ?,
            objection_closed_handling_description = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($username, $username, $objection_closed_handling_description, $objection_id));
            
            $s_sql = "SELECT * FROM creditor WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($creditor_filter));
            $creditor = ($o_query ? $o_query->row_array() : array());

            $s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
            WHERE collectingcase_id = '".$o_main->db->escape_str($objection['collecting_case_id'])."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
            $o_query = $o_main->db->query($s_sql);
            //trigger reordering 
            $source_id = 4;
            process_open_cases_for_tabs($creditor['id'], $source_id);
        }
        $v_return['status'] = 1;
    } else {
        $v_return['error'] = $formText_MissingObjection_output;
    }
} else {
    $v_return['error'] = $formText_FillInMessage_output;
}


?>
