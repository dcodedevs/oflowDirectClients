<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	require_once(__DIR__."/../../../output/includes/fnc_process_open_cases_for_tabs.php");
	require_once __DIR__ . '/../../../output/includes/creditor_functions_v2.php';
	if(isset($_POST['fix_due_date']) || isset($_POST['createTransactions'])) {
		$s_sql = "SELECT * FROM creditor  WHERE has_mainclaim_payed = 1";
	    $o_query = $o_main->db->query($s_sql);
	    $creditors = ($o_query ? $o_query->result_array() : array());
		foreach($creditors as $creditor) {	
			$filters = array();				
			$filters['list_filter'] = "transactions_with_fees_only";	
			$filters['sublist_filter'] = "resclaim_need_date_fix";	
			$transactions_needs_date_fix = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);

			foreach($transactions_needs_date_fix as $transaction_needs_date_fix){
				if($transaction_needs_date_fix['collectingcase_id'] > 0) {		
					$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($transaction_needs_date_fix['collectingcase_id']));
					$case = ($o_query ? $o_query->row_array() : array());
					if($case) {
						$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
						WHERE cccl.rest_note = 1 AND cccl.content_status < 2 AND cccl.case_id = ?  ORDER BY cccl.created DESC";
						$o_query = $o_main->db->query($s_sql, array($transaction_needs_date_fix['collectingcase_id']));
						$letter = ($o_query ? $o_query->row_array() : array());
						if($letter) {
							$new_due_date = date("Y-m-d", strtotime("+14 days", strtotime($letter['created'])));
							
							$s_sql = "UPDATE collecting_cases SET due_date = ? WHERE id = ? AND creditor_id = ?";
							$o_query = $o_main->db->query($s_sql, array($new_due_date, $transaction_needs_date_fix['collectingcase_id'], $transaction_needs_date_fix['creditor_id']));
							if($o_query){
								$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($transaction_needs_date_fix['id']));
							}
						}
					}					
				}
			}			
			//trigger reordering 		
			process_open_cases_for_tabs($creditor['id']);
		}

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="fix_due_date" value="Fix due dates">
		</div>
	</form>
</div>
