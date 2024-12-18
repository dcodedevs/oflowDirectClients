<?php
if(!function_exists("get_available_commands")) {
	function get_available_commands($creditor_transaction){
		global $o_main;
		$s_sql = "select * from collecting_cases where id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_transaction['collectingcase_id']));
		$case = $o_query ? $o_query->row_array() : array();

		$available_commands = array();
		if($case){
			if($case['choose_progress_of_reminderprocess'] == 3) {
				$available_commands[] = 2;
			} else {
				$available_commands[] = 1;
			}
		} else {
			if($creditor_transaction['choose_progress_of_reminderprocess'] == 3) {
				$available_commands[] = 2;
			} else {
				$available_commands[] = 1;
			}
		}
		return $available_commands;
	}
}
?>
