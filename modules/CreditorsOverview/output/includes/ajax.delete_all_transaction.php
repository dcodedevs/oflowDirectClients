<?php
$creditor_id = $_POST['creditor_id'];
$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditor_id));
$creditorData = $o_query ? $o_query->row_array() : array();
if($creditorData){
	require_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';
	$v_config = array(
		'ownercompany_id' => 1,
		'identityId' => $creditorData['entity_id'],
		'creditorId' => $creditorData['id'],
		'o_main' => $o_main
	);
	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."' ORDER BY DESC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && 0 < $o_query->num_rows())
	{
		$v_int_session = $o_query->row_array();
		$v_config['session_id'] = $v_int_session['session_id'];
	}
	$api = new Integration24SevenOffice($v_config);

	if($api->error == "") {
		$data['changedAfter'] = date("Y-m-d", strtotime("01.01.2012"));
		$data['ShowOpenEntries'] = 1;

		$invoicesTransactions = array();
		$loopCounter = 0;
		do {
			$dateStart = $data['changedAfter'];
			$dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));

			$transactionData = array();
			$transactionData['DateSearchParameters'] = 'DateChangedUTC';
			$transactionData['date_start'] = $dateStart;
			$transactionData['date_end'] = $dateEnd;
			$transactionData['bookaccountStart'] = 1500;
			$transactionData['bookaccountEnd'] = 1529;
			$transactionData['ShowOpenEntries'] = $data['ShowOpenEntries'];

			$connectedSuccessfully = false;
			$connect_tries = 0;
			do {
				$connect_tries++;
				$invoicesTransactionsApi = $api->get_transactions($transactionData);
				if($invoicesTransactionsApi !== null){
					$invoicesTransactions = array_merge($invoicesTransactionsApi, $invoicesTransactions);
					$connectedSuccessfully = true;
					break;
				}
			} while($connect_tries < 11);
			$connect_tries--;
			$data['changedAfter'] = $dateEnd;
			$loopCounter++;
		} while(strtotime($dateEnd) < time());

		$invoice_transaction_ids = array();
		foreach($invoicesTransactions as $invoicesTransaction) {
			$invoice_transaction_ids[] = $invoicesTransaction['id'];
		}

	    $s_sql = "SELECT * FROM creditor_transactions  WHERE creditor_id = ? AND open = 1";
	    $o_query = $o_main->db->query($s_sql, array($creditorData['id']));
	    $localTransactions = ($o_query ? $o_query->result_array() : array());
		$deletedCount = 0;
		if(count($invoice_transaction_ids) > 0) {
			foreach($localTransactions as $localTransaction) {
				if(!in_array($localTransaction['transaction_id'], $invoice_transaction_ids)) {
					$case_id = 0;
					if($localTransaction['collectingcase_id'] > 0 ){
						$case_id = $localTransaction['collectingcase_id'];
					}
					if($localTransaction['collecting_company_case_id'] > 0){
						$case_id = $localTransaction['collecting_company_case_id'];
					}
					if($case_id > 0){ echo ' has case - '.$case_id; } else {
						$s_sql = "UPDATE creditor_transactions SET open = 0 WHERE creditor_transactions.id = ?";
						$o_query = $o_main->db->query($s_sql, array($localTransaction['id']));
						if($o_query){
							$deletedCount++;
						}
					}
				}
			}
		}
		echo $deletedCount." closed transactions";

	} else {
		echo $api->error;
	}
}
?>
