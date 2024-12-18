<?php

$case_actions = $v_data['params']['case_actions'];
$username= $v_data['params']['username'];
$added_action_count = 0;
if(count($case_actions) > 0) {
    foreach($case_actions as $case_id=>$case_action){
        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($case_id));
        $case = ($o_query ? $o_query->row_array() : array());

        if($case){
            $s_sql = "SELECT * FROM creditor WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
            $creditor = ($o_query ? $o_query->row_array() : array());
            if($creditor){

                if($case['status'] == 0 || $case['status'] == 1){
                    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
                    $o_query = $o_main->db->query($s_sql, array($case['reminder_process_id']));
                    $process = ($o_query ? $o_query->row_array() : array());
                } else if($case['status'] == 3 || $case['status'] == 7){
                    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
                    $o_query = $o_main->db->query($s_sql, array($case['collecting_process_id']));
                    $process = ($o_query ? $o_query->row_array() : array());
                }

                $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = '".$o_main->db->escape_str($process['id'])."' AND collectinglevel = '".$case['collectinglevel']."' ORDER BY sortnr DESC";
                $o_query = $o_main->db->query($s_sql);
                $currentStep = $o_query ? $o_query->row_array() : array();


                // CREATE HANDLING AND ACTION RECORD - START
                $s_sql = "INSERT INTO collecting_cases_handling
                SET id=NULL,
                    createdBy='process',
                    created=NOW(),
                    text='Manual action triggered',
                    from_status='".$o_main->db->escape_str($case['collectinglevel'])."',
                    to_status='".$o_main->db->escape_str($case['collectinglevel'])."',
                    `type`=0,
                    collecting_case_id='".$o_main->db->escape_str($case['id'])."'";
                $o_query = $o_main->db->query($s_sql);
                $handling_id = $o_main->db->insert_id();

                $s_sql = "SELECT * FROM collecting_cases_process_steps_action WHERE collecting_cases_process_steps_id = ? ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql, array(intval($currentStep['id'])));
                $actions = $o_query ? $o_query->result_array() : array();
                if(count($actions) > 0) {
                    if($handling_id)
                    {
                        foreach($actions as $action) {
                            if($action['action'] == $case_action) {
                                $s_sql = "INSERT INTO collecting_cases_handling_action SET id=NULL, createdBy='process', created=NOW(), handling_id='".$o_main->db->escape_str($handling_id)."', action_type='".$case_action."', collecting_cases_process_steps_action_id = '".$action['id']."'";
                                $o_query = $o_main->db->query($s_sql);
                                if($o_query){
                                    $added_action_count++;
                                }
                            }
                        }
                    }
                }

            } else {
                $v_return['error'] = 'No creditor info';
            }
        } else {
            $v_return['error'] = 'No case info';
        }
    }
    $v_return['status'] = 1;
    $v_return['return'] = $added_action_count;
} else {
    $v_return['error'] = 'No cases selected';
}


?>
