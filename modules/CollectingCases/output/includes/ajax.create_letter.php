<?php 
$username= $variables->loggID;
if($username == "byamba@dcode.no"){
    $username = "david@dcode.no";
}
include(__DIR__."/fnc_generate_pdf.php");

$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
$cases = $o_query ? $o_query->result_array() : array();

// $s_sql = "SELECT * FROM collecting_cases WHERE create_letter = 1 AND creditor_id = 4260 ";
// $o_query = $o_main->db->query($s_sql);
// $cases = $o_query ? $o_query->result_array() : array();

include(dirname(__FILE__).'/../languagesOutput/no.php');
foreach($cases as $case){
    $s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ? AND creditor_id = ? AND open = 1";
    $o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
    $transaction = $o_query ? $o_query->row_array() : array();
    if($transaction){
        if($transaction['case_balance'] > 300){
            require_once __DIR__ . '/../../../Integration24SevenOffice/internal_api/load.php';
            $s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
            $o_query = $o_main->db->query($s_sql);
            $system_settings = ($o_query ? $o_query->row_array() : array());

            if(!function_exists("generateRandomString")) {
                function generateRandomString($length = 8) {
                    $characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < $length; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }
                    return $randomString;
                }
            }

            do{
                $code = generateRandomString(10);
                $code_check = null;
                $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
                $o_query = $o_main->db->query($s_sql, array($code));
                if($o_query){
                    $code_check = $o_query->row_array();
                }
            } while($code_check != null);
            
            $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($case['collecting_cases_process_step_id']));
            $step = $o_query ? $o_query->row_array() : array();

            if($step) {
                $step_add_days = $step['add_number_of_days_to_due_date'];
                if($step_add_days > 0){
                    $new_due_date = date("Y-m-d", strtotime("+".$step_add_days." days"));   

                    $s_sql = "UPDATE collecting_cases SET due_date = ? WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($new_due_date, $case['id']));

                    $result = generate_pdf($case['id']);

                    $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
                    $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
                    $creditorData = ($o_query ? $o_query->row_array() : array());

                    if(count($result['errors']) > 0) {
                        foreach($result['errors'] as $error){
                            echo $formText_LetterFailedToBeCreatedForCase_output." ".$case['id']." ".$error."</br>";
                        }
                    } else {
                        $successfullyCreatedLetters++;
                        if($creditorData['print_reminders'] == 0) {
                            if($result['item']['id'] > 0){
                                $s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(),  print_batch_code = ? WHERE id = ?";
                                $o_query = $o_main->db->query($s_sql, array($code, $result['item']['id']));
                                if($o_query) {
                                    $lettersForDownload[] = $result['item']['id'];

                                    if(count($lettersForDownload) > 0){
                                        echo $formText_LettersForManualPrinting_output." <a href='".$extradomaindirroot."/modules/CollectingCaseClaimletter/output/includes/ajax.download.php?code=".$code."&ids=".implode(",",$lettersForDownload)."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>"."<br/>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        echo $formText_CaseIsClosed_output."</br>";
    }
}
?>