<?php
if($_POST['selected_creditor_id'] > 0){
	$sql = "SELECT * FROM creditor WHERE content_status < 2 AND id = ? ORDER BY id";
	$o_query = $o_main->db->query($sql, array($_POST['selected_creditor_id']));
	$creditors = $o_query ? $o_query->result_array() : array();
} else {
	$sql = "SELECT * FROM creditor WHERE content_status < 2 ORDER BY id";
	$o_query = $o_main->db->query($sql);
	$creditors = $o_query ? $o_query->result_array() : array();
}
require(__DIR__."/../creditor_functions.php");
require(__DIR__."/../fnc_create_case_from_transaction.php");
$nopreview = true;
if($set_preview){
	$nopreview = false;
}
$collecting_case_ids = $_POST['collecting_case_ids'];
foreach($creditors as $creditor) {
	$casesToBeProcessed = array();
	$fullCasesToBeProcessed = array();

	$filters = array();
	$filters['order_field'] = '';
	$filters['order_direction'] = 0;
	$filters['sublist_filter'] = "notStarted";
	$customerListNonProcessed = get_collecting_company_case_list($o_main, $creditor['id'], "warning", $filters);
	foreach($customerListNonProcessed as $v_row){
		if(in_array($v_row['id'], $collecting_case_ids)) {
			$casesToBeProcessed[] = $v_row['id'];
		}
		$fullCasesToBeProcessed[] = $v_row;
	}
	if($nopreview) {
		$creditorId = $creditor['id'];
		$collecting_case_id = $casesToBeProcessed;
		if(count($collecting_case_id) > 0) {
			$casesToGenerate = array();
			include(__DIR__."/handle_cases_collecting.php");
		}
	} else {
		$return_data[$creditor['id']]['warning_level'] = $fullCasesToBeProcessed;
	}

	$casesToBeProcessed = array();
	$fullCasesToBeProcessed = array();
	$filters = array();
	$filters['order_field'] = '';
	$filters['order_direction'] = 0;
	$filters['sublist_filter'] = "notStarted";
	$customerListNonProcessed = get_collecting_company_case_list($o_main, $creditor['id'], "collecting", $filters);
	foreach($customerListNonProcessed as $v_row){
		if(in_array($v_row['id'], $collecting_case_ids)) {
			$casesToBeProcessed[] = $v_row['id'];
		}
		$fullCasesToBeProcessed[] = $v_row;
	}
	if($nopreview){
		$creditorId = $creditor['id'];
		$collecting_case_id = $casesToBeProcessed;
		if(count($collecting_case_id) > 0) {
			$casesToGenerate = array();
			include(__DIR__."/handle_cases_collecting.php");
		}
	} else {
		$return_data[$creditor['id']]['collecting_level'] = $fullCasesToBeProcessed;
	}
}
?>
