<?php
if($_POST['report_id']) {
	$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['report_id']));
	$report = $o_query ? $o_query->row_array() : array();
	if($report) {
		$s_sql = "DELETE FROM collecting_cases_report_24so WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($report['id']));
		if($o_query) {
			$s_sql = "UPDATE collecting_cases_claim_letter SET fees_status = 0 WHERE fees_status = 2 AND billing_report_id = ?";
			$o_query = $o_main->db->query($s_sql, array($report['id']));

			$s_sql = "UPDATE collecting_cases_claim_letter SET billing_report_id = 0 WHERE billing_report_id = ?";
			$o_query = $o_main->db->query($s_sql, array($report['id']));

			$s_sql = "UPDATE collecting_cases SET billing_report_id = 0 WHERE billing_report_id = ?";
			$o_query = $o_main->db->query($s_sql, array($report['id']));
		}
	}
} else if(isset($_POST['date'])){
	$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE date = ?";
	$o_query = $o_main->db->query($s_sql, array(date("Y-m-d", strtotime($_POST['date']))));
	$reports = $o_query ? $o_query->result_array() : array();
	foreach($reports as $report){
		$s_sql = "DELETE FROM collecting_cases_report_24so WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($report['id']));
		if($o_query) {
			$s_sql = "UPDATE collecting_cases_claim_letter SET fees_status = 0 WHERE fees_status = 2 AND billing_report_id = ?";
			$o_query = $o_main->db->query($s_sql, array($report['id']));

			$s_sql = "UPDATE collecting_cases_claim_letter SET billing_report_id = 0 WHERE billing_report_id = ?";
			$o_query = $o_main->db->query($s_sql, array($report['id']));
			$s_sql = "UPDATE collecting_cases SET billing_report_id = 0 WHERE billing_report_id = ?";
			$o_query = $o_main->db->query($s_sql, array($report['id']));
		}
	}
}
?>
