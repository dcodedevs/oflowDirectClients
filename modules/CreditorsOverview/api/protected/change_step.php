<?php

$case_steps = $v_data['params']['case_steps'];
$username= $v_data['params']['username'];
$processed_override_actions= $v_data['params']['processed_override_actions'];

if(count($case_steps) > 0) {
    foreach($case_steps as $case_id=>$case_step_id){

        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($case_id));
        $case = ($o_query ? $o_query->row_array() : array());


        if($case){
            $s_sql = "SELECT * FROM creditor WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
            $creditor = ($o_query ? $o_query->row_array() : array());
            if($creditor){
                $collecting_case_id = $case['id'];
                $creditorId = $creditor['id'];
                // $collecting_level_to_move_from = $case['collectinglevel'];
                $case_step_to_move_to = $case_step_id;
                $override_action = intval($processed_override_actions[$case_id]);
                include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
                $v_return['log'] = $log;
            } else {
                $v_return['error'] = 'No creditor info';
            }
        } else {
            $v_return['error'] = 'No case info';
        }
    }
    $v_return['status'] = 1;
} else {
    $v_return['error'] = 'No cases selected';
}


?>
