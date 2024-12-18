<?php
	if(isset($_POST['mark_duedate'])) {

		$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id > 0 AND collecting_company_case_id > 0";
		$o_query = $o_main->db->query($s_sql);
		$creditor_transactions = $o_query ? $o_query->result_array() : array();
		$count = 0;
		foreach($creditor_transactions as $creditor_transaction) {
			$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor_transaction['collectingcase_id']));
			$collecting_case = $o_query ? $o_query->row_array() : array();

			$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor_transaction['collecting_company_case_id']));
			$collecting_company_case = $o_query ? $o_query->row_array() : array();

			if($collecting_case && $collecting_company_case) {
				$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? ORDER BY due_date DESC";
				$o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
				$last_letter = $o_query ? $o_query->row_array() : array();

				$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE collecting_company_case_id = ? ORDER BY created ASC";
				$o_query = $o_main->db->query($s_sql, array($collecting_company_case['id']));
				$first_letter = $o_query ? $o_query->row_array() : array();

				if($last_letter && $first_letter) {
					if(date("Y-m-d", strtotime($last_letter['due_date'] ))== date("Y-m-d",strtotime($first_letter['created'] ))) {
						$s_sql = "UPDATE collecting_company_cases SET due_date_issue = 1 WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($collecting_company_case['id']));
						if($o_query){
							$count++;
						}
					}
				}
			}
		}
		var_dump($count);
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<?php echo $formText_MarkDueDateIssue_output;?>
			<input type="submit" name="mark_duedate" value="Mark due date issue">

		</div>
	</form>
</div>
