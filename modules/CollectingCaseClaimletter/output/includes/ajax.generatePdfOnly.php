<?php
$letter_id = $_POST['letter_id'];
if($letter_id > 0){
    if($variables->developeraccess >=20) {
        $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($letter_id));
        $letter = ($o_query ? $o_query->row_array() : array());
		if($letter['collecting_company_case_id'] > 0){
			include_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_calculate_interest.php");
			include_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_generate_pdf.php");
		} else {
			include_once(__DIR__."/../../../CollectingCases/output/includes/fnc_calculate_interest.php");
			include_once(__DIR__."/../../../CollectingCases/output/includes/fnc_generate_pdf.php");
		}

        $result = generate_pdf_from_letter($letter_id, $letter['rest_note']);
        var_dump($result, $letter_id);
    }
}
?>
