<?php

if($variables->loggID == "byamba@dcode.no"){
    $from_api = true;
    $cid = $_GET['cid'];
    
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    include(__DIR__."/../../output/includes/download_customer_report_pdf.php");
    var_dump($pdf_string);
}
// include(__DIR__."/../../output/includes/fnc_create_case_from_transaction.php");
//
// $customer_id = 1;
// // $checkCasesToProcess= $v_data['params']['checkCaseToProcess'];
// $suggestedToProcess= array("1772", "1773", "1774");
// $username= "byamba@dcode.no";
// // $accountname= $v_data['params']['accountname'];
// $languageID = "no";
// $extradomaindirroot = $v_data['params']['accounturl'];
// if($languageID == ""){
// 	$languageID = "no";
// }
// $variables->loggID = $username;
// $s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
// $o_query = $o_main->db->query($s_sql, array($customer_id));
// $creditor = ($o_query ? $o_query->row_array() : array());
// if($creditor){
//     ob_start();
//     include(__DIR__."/../../output/languagesOutput/default.php");
//     if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
//         include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
//     }
//     if(count($checkCasesToProcess) > 0) {
//         // foreach($checkCasesToProcess as $checkCaseToProcess) {
//         //     $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
//         //     $o_query = $o_main->db->query($s_sql, array($checkCaseToProcess));
//         //     $case = ($o_query ? $o_query->row_array() : array());
//         //     if($case){
//         //     }
//         // }
//
//         $reminderLevelOnly = 1;
//         $manualProcessing = 1;
//         $creditorId = $creditor['id'];
//         $collecting_case_id = $checkCasesToProcess;
//
//         include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
//     }
//     $casesReported = array();
//     if(count($suggestedToProcess) > 0) {
//         $newCaseCount = 0;
//         foreach($suggestedToProcess as $singleToProcess) {
//             $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
//             $o_query = $o_main->db->query($s_sql, array($singleToProcess));
//             $transaction = ($o_query ? $o_query->row_array() : array());
//             if($transaction) {
//                 $caseCreated = create_case_from_transaction($transaction['id'], $creditor['id'], $languageID, false);
//                 if($caseCreated > 0) {
//                     $newCaseCount++;
//                     $casesReported[] = $caseCreated;
//                 }
//             }
//         }
//         echo $newCaseCount." ".$formText_CasesCreated_output."<br/>";
// 		$test = true;
// 		if(count($casesReported) > 0){
// 	        $creditorId = $creditor['id'];
// 	        $collecting_case_id = $casesReported;
// 	        include(__DIR__."/../../output/includes/process_scripts/handle_cases.php");
// 		}
//     }
//     $result_output = ob_get_contents();
//     $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
//     ob_end_clean();
//     $v_return['html'] = $result_output;
//     $v_return['cases_created_count'] = $newCaseCount;
//     $v_return['cases_processed_count'] = $casesReported;
//     $v_return['created_letters_count'] = $created_letters;
//     $v_return['emails_sent_count'] = $emails_sent;
//
//     $v_return['status'] = 1;
// 	var_dump($v_return);
// }

?>
