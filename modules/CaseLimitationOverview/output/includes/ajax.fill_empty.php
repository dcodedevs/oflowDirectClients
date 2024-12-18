<?php 

$s_sql = "SELECT 
ccc.*,
cccl.original_due_date
FROM collecting_company_cases ccc
JOIN collecting_company_cases_claim_lines cccl ON cccl.collecting_company_case_id = ccc.id AND cccl.claim_type = 1
WHERE (ccc.case_closed_date = '0000-00-00' OR ccc.case_closed_date IS NULL)
AND (ccc.case_limitation_date = '0000-00-00' OR ccc.case_limitation_date IS NULL)
ORDER BY cccl.original_due_date ASC";
$o_query = $o_main->db->query($s_sql);
$cases = ($o_query ? $o_query->result_array() : array());
$cases_updated = 0;
$case_processed_ids = array();
foreach($cases as $case) {
    if(!in_array($case['id'], $case_processed_ids)) {
        if($case['original_due_date'] != "" && $case['original_due_date'] != "0000-00-00"){
            $expireDate = date("Y-m-d", strtotime("+3 years", strtotime($case['original_due_date'])));
            $case_processed_ids[] = $case['id'];
            $s_sql = "UPDATE collecting_company_cases SET case_limitation_date = ? WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($expireDate, $case['id']));
            if($o_query) {
                $cases_updated++;
            }
        }
    }
}
echo $cases_updated." collecting company cases updated";
?>