<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['checkCreditorNames'])) {
		// $s_sql = "SELECT ct.creditor_id, ct.account_nr FROM creditor_transactions ct
		// WHERE account_nr <> 1500
		// GROUP BY creditor_id, account_nr";
		// $o_query = $o_main->db->query($s_sql);
		// $creditors = ($o_query ? $o_query->result_array() : array());
		// foreach($creditors as $creditor){
		// 	$sql = "SELECT * FROM creditor_additional_bookaccounts WHERE creditor_id = ? AND bookaccount=?";
		// 	$o_query = $o_main->db->query($sql, array($creditor['creditor_id'], $creditor['account_nr']));
		// 	$bookaccount = $o_query ? $o_query->row_array() : array();
		// 	if($bookaccount){
		// 		$sql = "UPDATE creditor_additional_bookaccounts SET creditor_id = ?, bookaccount = ?, updatedBy = ?, updated=NOW() WHERE id = ?";
		// 		$o_query = $o_main->db->query($sql, array($creditor['creditor_id'], $creditor['account_nr'], $variables->loggID,$bookaccount['id']));
		// 		$fw_redirect_url = $_POST['redirect_url'];
		// 	} else {
		// 		$sql = "INSERT INTO creditor_additional_bookaccounts SET creditor_id = ?,  bookaccount = ?, createdBy = ?, created=NOW()";
		// 		$o_query = $o_main->db->query($sql, array($creditor['creditor_id'],  $creditor['account_nr'], $variables->loggID));
		// 	}
		// }

		$s_sql = "SELECT ct.creditor_id, ct.account_nr FROM creditor_transactions ct
		WHERE account_nr <> 1500
		GROUP BY creditor_id, account_nr";
		$o_query = $o_main->db->query($s_sql);
		$creditors = ($o_query ? $o_query->result_array() : array());
		foreach($creditors as $creditor){
			$sql = "SELECT * FROM creditor WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($creditor['creditor_id']));
			$creditor_info = $o_query ? $o_query->row_array() : array();
			if($creditor_info['bookaccount_upper_range'] < $creditor['account_nr']){
				$sql = "UPDATE creditor SET bookaccount_upper_range = ?, updatedBy = ?, updated=NOW() WHERE id = ?";
				$o_query = $o_main->db->query($sql, array($creditor['account_nr'], $variables->loggID,$creditor['creditor_id']));	
			}			
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="checkCreditorNames" value="Fill bookaccount upper range">
		</div>
	</form>
</div>
