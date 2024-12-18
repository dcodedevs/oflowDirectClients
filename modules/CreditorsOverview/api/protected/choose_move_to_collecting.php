<?php
include(__DIR__."/../languagesOutput/default.php");

$case_id = $v_data['params']['case_id'];
$transaction_id = $v_data['params']['transaction_id'];
$username = $v_data['params']['username'];
$choose_move_to_collecting = $v_data['params']['choose_move_to_collecting'];

if($choose_move_to_collecting != "") {
    if(isset($case_id) && $case_id > 0)
    {
        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($case_id));
        if($o_query && $o_query->num_rows() == 1) {
            $s_sql = "UPDATE collecting_cases SET
            updated = now(),
            updatedBy= ?,
            choose_move_to_collecting_process = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $case_id));
        }
        $v_return['status'] = 1;
    } if(isset($transaction_id) && $transaction_id > 0)
    {
        $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($transaction_id));
        if($o_query && $o_query->num_rows() == 1) {
            $s_sql = "UPDATE creditor_transactions SET
            updated = now(),
            updatedBy= ?,
            choose_move_to_collecting_process = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $transaction_id));
        }
        $v_return['status'] = 1;
    } else {
        $v_return['error'] = $formText_MissingCase_output;
    }
} else {
    $v_return['error'] = $formText_ChoiceValue_output;
}


?>
