<?php 
if(!function_exists("process_continuing_step")) {
    function process_continuing_step($caseId, $startStepId = 0){
        global $o_main;
        global $variables;

        $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($caseId));
        $case = $o_query ? $o_query->row_array() : array();
        $result = array();
        if($case) {        
            $next_step = array();

            $sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($case['continuing_process_step_id']));
            $current_step = $o_query ? $o_query->row_array() : array();
            if($current_step) {
                $sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE collecting_company_cases_continuing_process_id = ? ORDER BY sortnr";
                $o_query = $o_main->db->query($sql, array($current_step['collecting_company_cases_continuing_process_id']));
                $continuing_steps = $o_query ? $o_query->result_array() : array();
                $step_count = 0;
                $current_step_count = 0;
                foreach($continuing_steps as $continuing_step) {
                    $step_count++;
                    if($continuing_step['id'] == $current_step['id']){
                        $current_step_count = $step_count;
                    }
                    if($current_step_count > 0 && $step_count == $current_step_count + 1){
                        $next_step = $continuing_step;
                    }
                }
            } else if($startStepId > 0) {
                $sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE id = ?";
                $o_query = $o_main->db->query($sql, array($startStepId));
                $next_step = $o_query ? $o_query->row_array() : array();
            }
            

            if($next_step) {
                if(strtotime("+".$next_step['days_after_due_date']." days", strtotime($case['due_date'])) <= strtotime(date("Y-m-d"))) {
                    if($next_step['appear_in_legal_step_handling']){ 
                        $sql = "INSERT INTO legal_step_handling SET created = NOW(), createdBy = ?, continuing_process_step_id = ?, collecting_company_case_id = ?";
                        $o_query = $o_main->db->query($sql, array($variables->loggID, $next_step['id'], $case['id']));
                    } else if($next_step['appear_in_call_debitor_step_handling']){
                        $sql = "INSERT INTO call_debitor_step_handling SET created = NOW(), createdBy = ?, continuing_process_step_id = ?, collecting_company_case_id = ?";
                        $o_query = $o_main->db->query($sql, array($variables->loggID, $next_step['id'], $case['id']));                
                    }
                    if($next_step['create_letter']) {
                        include_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_calculate_interest.php");
                        include_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_generate_pdf.php");
                        $collecting_cases_pdftext_id = $next_step['collecting_cases_pdftext_id'];
                        $add_due_days = $next_step['add_number_of_days_to_due_date'];
                        $update_interest = true;

                        $s_sql = "SELECT collecting_cases_pdftext.*  
                        FROM collecting_cases_pdftext 
                        WHERE id = ? ORDER BY sortnr";
                        $o_query = $o_main->db->query($s_sql, array($collecting_cases_pdftext_id));
                        $collecting_case_pdftext = ($o_query ? $o_query->row_array() : array());

                        if($collecting_case_pdftext && $add_due_days != "" && $add_due_days > 0) {

                            if($update_interest){
                                $noInterestError = false;
                                $s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_company_case_id = ? ";
                                $o_query = $o_main->db->query($s_sql, array($case['id']));

                                $currentClaimInterest = 0;
                                $interestArray = calculate_interest(array(), $case);
                                $totalInterest = 0;
                                foreach($interestArray as $interest_index => $interest) {
                                    $interest_index_array = explode("_", $interest_index);
                                    $claimline_id = intval($interest_index_array[2]);

                                    $interestRate = $interest['rate'];
                                    $interestAmount = $interest['amount'];
                                    $interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
                                    $interestTo = date("Y-m-d", strtotime($interest['dateTo']));

                                    $s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
                                    date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."',
                                    collecting_company_cases_claim_line_id = '".$o_main->db->escape_str($claimline_id)."'";
                                    $o_query = $o_main->db->query($s_sql, array());
                                    $totalInterest += $interestAmount;
                                }

                                $s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."' AND claim_type = 8 ORDER BY created DESC";
                                $o_query = $o_main->db->query($s_sql, array($case['id']));
                                $interest_claim_line = ($o_query ? $o_query->row_array() : array());
                                if($interest_claim_line) {
                                    $s_sql = "UPDATE collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
                                    collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."'
                                    WHERE id = '".$o_main->db->escape_str($interest_claim_line['id'])."'";
                                    $o_query = $o_main->db->query($s_sql);
                                } else {
                                    $s_sql = "INSERT INTO collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
                                    collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."', claim_type = 8, name= '".$o_main->db->escape_str($formText_Interest_output)."'";
                                    $o_query = $o_main->db->query($s_sql);
                                }
                            }
                            $dueDate = date("Y-m-d", strtotime("+".$add_due_days." days", time()));

                            $s_sql = "UPDATE collecting_company_cases SET due_date = '".$o_main->db->escape_str($dueDate)."' WHERE id = '".$o_main->db->escape_str($case['id'])."'";
                            $o_query = $o_main->db->query($s_sql);

                            $single_task_array = array();
                            $single_task_array['collecting_case_pdftext'] = $collecting_case_pdftext;

                            $result_pdf = generate_pdf($case['id'], 0, 0, $single_task_array);
                            // var_dump($result);
                        }
                    }

                    $due_date = date("Y-m-d", strtotime("+".$next_step['add_number_of_days_to_due_date']." days"));
                    $sql = "UPDATE collecting_company_cases SET updated = NOW(), continuing_process_step_id = ?, due_date = ? WHERE id = ?";
                    $o_query = $o_main->db->query($sql, array($next_step['id'],  $due_date, $case['id']));
                    if($o_query){
                        $result['success'] = 1;
                    }
                } else {
                    $result['due_date_not_reached'] = 1;
                }
            }
        }
        return $result;
    }
}
?>