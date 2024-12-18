<?php

$case_id = $v_data['params']['case_id'];
$step_id = $v_data['params']['step_id'];
$customer_id = $v_data['params']['customer_id'];
$username= $v_data['params']['username'];
$accountname= $v_data['params']['accountname'];
$languageID = $v_data['params']['languageID'];
$extradomaindirroot = $v_data['params']['extradomaindirroot'];

$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($step_id));
$step = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM collecting_cases WHERE id = '".$o_main->db->escape_str($case_id)."'";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();
if($caseData){
    $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
    $creditor = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor['customer_id']));
    $creditorCustomer = $o_query ? $o_query->row_array() : array();
    if($creditorCustomer['id'] == $customer_id) {
        ob_start();
        include(__DIR__."/../../output/languagesOutput/default.php");
        if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
            include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
        }

        $stopped_sql = "";
        if($step['status_id'] == 4 || $step['status_id'] == 2){
            $stopped_sql = ", stopped_date = NOW()";
        }
        $s_sql = "UPDATE collecting_cases SET collecting_cases_process_step_id = '".$o_main->db->escape_str($step['id'])."', status = '".$o_main->db->escape_str($step['status_id'])."', sub_status = '".$o_main->db->escape_str($step['sub_status_id'])."'".$stopped_sql." WHERE id = '".$o_main->db->escape_str($caseData['id'])."'";
        $o_query = $o_main->db->query($s_sql);

        if($o_query){
            echo $formText_NextStepSuccessfullyChanged_output;
        } else {
            echo $formText_ErrorUpdating_output;
        }
        // $skip_to_step = $step_id;
        // $collecting_case_id = $caseData['id'];
        // $creditorId = $caseData['creditor_id'];
        // $case_step_to_move_to = $skip_to_step;
        // include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_cases.php");
        //
        // $_POST['casesToGenerate'] = array($caseData['id']);
        // include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_actions.php");

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
