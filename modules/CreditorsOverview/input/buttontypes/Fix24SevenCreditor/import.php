<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['checkTransactions']) || isset($_POST['createTransactions'])) {
		$s_sql = "SELECT * FROM creditor_transactions  WHERE creditor_id = 1031 AND open = 1";
	    $o_query = $o_main->db->query($s_sql);
	    $localTransactions = ($o_query ? $o_query->result_array() : array());

		$localTransactionIds = array();
		foreach($localTransactions as $localTransaction) {
			$localTransactionIds[] =  $localTransaction['transaction_id'];
		}
		echo implode('","',$localTransactionIds);
		// $transactionToBeCreatedCount = 0;

		// $s_sql = "SELECT * FROM creditor_transactions_2  WHERE creditor_id = 1031 AND open = 1";
	    // $o_query = $o_main->db->query($s_sql);
	    // $backup_transactions = ($o_query ? $o_query->result_array() : array());
		// foreach($backup_transactions as $backup_transaction) {
		// 	if(!in_array($backup_transaction['id'], $localTransactionIds)){
		// 		$transactionToBeCreatedCount++;
		// 		if($_POST['createTransactions']){
		// 			$insert_array = $backup_transaction;
		// 			// $o_main->db->insert('creditor_transactions', $insert_array);
		// 		}
		// 	}
		// }
		// echo count($localTransactionIds)." local transactions found<br/>";

		// echo count($backup_transactions)." backup transactions found<br/>";

		// echo $transactionToBeCreatedCount .' transactions will be created';
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="checkTransactions" value="Check Transactions">
			<?php if($transactionToBeCreatedCount > 0) { ?>
				<input type="submit" name="createTransactions" value="Create Transactions">
			<?php } ?>
		</div>
	</form>
</div>
