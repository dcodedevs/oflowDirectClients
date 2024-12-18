<?php 
$claimline_id = $_POST['claimline_id'];
$case_id = $_POST['case_id'];
$checked = $_POST['checked'];

if($claimline_id > 0 && $case_id > 0) {    
    $s_sql = "UPDATE collecting_company_cases_claim_lines SET
    invoice_closed_allow_processing_anyway = ? WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($checked, $claimline_id));    	
}
?>