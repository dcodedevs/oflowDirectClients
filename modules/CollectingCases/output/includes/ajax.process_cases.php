<?php

$s_sql = "SELECT creditor.* FROM creditor ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$creditors = ($o_query ? $o_query->result_array() : array());

ob_start();
include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/default.php");
if(is_file(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php")){
    include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php");
}
foreach($creditors as $creditor){
    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor['customer_id']));
    $creditorCustomer = $o_query ? $o_query->row_array() : array();
    $casesToGenerate = array();
    if($_POST['action'] == "processCollecting"){
        $creditorId = $creditor['id'];
        include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_cases_collecting.php");
        $v_return['log'] = $log;
    } else if($_POST['action'] == "processReminder"){
        if($creditor['choose_progress_of_reminderprocess'] == 1){
            $creditorId = $creditor['id'];
            $manualProcessing = 1;
            include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_cases.php");
            $v_return['log'] = $log;
        }
    }

    // if(count($casesToGenerate) > 0) {
    //     $_POST['casesToGenerate'] = $casesToGenerate;
    //     echo $creditorCustomer['name'].":<br/>";
    //     include(__DIR__."/../../../CreditorsOverview/output/includes/process_scripts/handle_actions.php");
    //     echo "<br/><br/>";
    // }
}
$result_output = ob_get_contents();
$result_output = trim(preg_replace('/\s\s+/', '', $result_output));
ob_end_clean();
echo $result_output;
?>
