<?php
$sql = "SELECT creditor.*, creditor.companyname as customerName FROM creditor
LEFT OUTER JOIN customer ON customer.id = creditor.customer_id
WHERE creditor.integration_module <> '' AND creditor.sync_from_accounting = 1
ORDER BY creditor.id";
$o_query = $o_main->db->query($sql);
$creditors = $o_query ? $o_query->result_array() : array();


foreach($creditors as $creditor) {
	if(!$creditor['onboarding_incomplete']) {
		$sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND open = 1";
		$o_query = $o_main->db->query($sql, array($creditor['id']));
		$open_transactions = $o_query ? $o_query->result_array() : array();
		$total_sum = 0;
		$integration_total_sum = 0;
		$open_transactions_by_id = array();
		foreach($open_transactions as $open_transaction) {
			$total_sum += $open_transaction['amount'];
			$open_transactions_by_id[$open_transaction['transaction_id']] = $open_transaction;
		}
		if($creditor['integration_module'] != ""){
			if(!class_exists("Integration24SevenOffice")){
				require_once __DIR__ . '/../../../../'.$creditor['integration_module'].'/internal_api/load.php';
			}
			$v_config = array(
				'ownercompany_id' => 1,
				'identityId' => $creditor['entity_id'],
				'creditorId' => $creditor['id'],
				'o_main' => $o_main
			);
			$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && 0 < $o_query->num_rows())
			{
				$v_int_session = $o_query->row_array();
				$v_config['session_id'] = $v_int_session['session_id'];
			}
			try {
				$api = new Integration24SevenOffice($v_config);
				if($api->error == "") {
				    $data['changedAfter'] = date("Y-m-d", strtotime("01.01.2012"));
				    $data['ShowOpenEntries'] = 1;

					$loopCounter = 0;
					$invoicesTransactions = array();
					do {
						$dateStart = $data['changedAfter'];
						$dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));
						$bookaccountEnd = 1529;
						if($creditor['bookaccount_upper_range'] >= 1500){
							$bookaccountEnd = $creditor['bookaccount_upper_range'];
						}
						$transactionData = array();
						$transactionData['DateSearchParameters'] = 'DateChangedUTC';
						$transactionData['date_start'] = $dateStart;
						$transactionData['date_end'] = $dateEnd;
						$transactionData['bookaccountStart'] = 1500;
						$transactionData['bookaccountEnd'] = $bookaccountEnd;
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

					if($connectedSuccessfully) {
						$integration_total_sum = 0;
						foreach($invoicesTransactions as $invoicesTransaction) {
							$integration_total_sum += $invoicesTransaction['amount'];
							$local_transaction_entry = $open_transactions_by_id[$invoicesTransaction['id']];
							if($local_transaction_entry) {
								if($invoicesTransaction['currency'] != "" && $local_transaction_entry['currency'] != $invoicesTransaction['currency']){
									$sql = "UPDATE creditor_transactions SET currency = ?, currency_rate=?, currency_unit = ? WHERE id = ?";
									$o_query = $o_main->db->query($sql, array($invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction_entry['id']));
								}
							}
						}
						if(round($total_sum, 2) != round($integration_total_sum, 2)){
							$sql = "UPDATE creditor SET control_sum_correct = '0000-00-00 00:00:00' WHERE id = ?";
							$o_query = $o_main->db->query($sql, array($creditor['id']));
							echo $creditor['customerName']." ".round($total_sum, 2)." ".round($integration_total_sum, 2)."</br></br>";
						} else {
							$sql = "UPDATE creditor SET control_sum_correct = NOW() WHERE id = ?";
							$o_query = $o_main->db->query($sql, array($creditor['id']));
						}
					}
				}
			} catch(Exception $e) {
				echo $formText_FailedToConnect_output."<br/>";
				$failedMsg = "Critical error with exception. ".$e->getMessage();
			}
		}
	}
}
?>
