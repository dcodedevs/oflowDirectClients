<?php
	include(__DIR__."/fnc_generate_pdf.php");
	if(isset($_POST['case_id']))
	{
        $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
        $case = $o_query ? $o_query->row_array() : array();
		if($case){
			$result = generate_pdf($case['id'], 0, 1);
		}
	}
?>
