<?php
$transaction_id = $_POST['transaction_id'];

$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_transactions.id = ?";
$o_query = $o_main->db->query($s_sql, array($transaction_id));
$transactions_to_sync = ($o_query ? $o_query->row_array() : array());
if($transactions_to_sync['collectingcase_id'] > 0) {
	$s_sql = "SELECT *, creditor.companyname as creditorName FROM creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($transactions_to_sync['creditor_id']));
	$creditorData = ($o_query ? $o_query->row_array() : array());
	if($creditorData){

		if(!class_exists("Integration24SevenOffice")){
			require_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';
		}
		$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."' ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql);
		$integration_sessions = $o_query ? $o_query->result_array() : array();
		$username = "";
		$session_id = "";
		echo "<div style='padding: 5px 0px; border-bottom:1px solid #cecece;'><div><b>".$creditorData['creditorName']." (id: ".$creditorData['id'].")</b></div>";
		foreach($integration_sessions as $integration_session) {
			$v_config = array(
				'ownercompany_id' => 1,
				'creditorId' => $creditorData['id'],
				'o_main' => $o_main
			);
			$v_config['session_id'] = $integration_session['session_id'];
			try {
				$connect_tries = 0;
				$connectedSuccessfully = false;
				do {
					$connect_tries++;
					$api = new Integration24SevenOffice($v_config);
					if($api->error == "") {
						$connectedSuccessfully = true;
						$username = $integration_session['username'];
						$session_id = $integration_session['session_id'];
						break;
					} else  {
						// echo $api->error;
					}
				} while($connect_tries < 2);
				if($connectedSuccessfully) {
					break;
				}
			} catch(Exception $e) {
				echo  "Critical error with exception. ".$e->getMessage();
			}
		}
		if($username != "") {

			$newTransactionData['date_start'] = $transactions_to_sync['date'];
			$newTransactionData['date_end'] = date('Y-m-d', strtotime($transactions_to_sync['date']) + 60*60*24);
			$newTransactionData['bookaccountStart'] = 1500;
			$newTransactionData['bookaccountEnd'] = 1529;
			$invoicesTransactions = $api->get_transactions($newTransactionData);
			foreach($invoicesTransactions as $invoicesTransaction) {
				if($invoicesTransaction['id'] == $transactions_to_sync['transaction_id']) {
					if($invoicesTransaction['open'] != $transactions_to_sync['open']) {
						$newTransactionData2 = array();
						$newTransactionData2['date_start'] = $invoicesTransaction['date'];
						$newTransactionData2['date_end'] = date('Y-m-d', strtotime($invoicesTransaction['dateChanged']) + 60*60*24);
						$newTransactionData2['bookaccountStart'] = 1500;
						$newTransactionData2['bookaccountEnd'] = 1529;
						$newTransactionData2['LinkId'] = $invoicesTransaction['linkId'];
						$connectedTransactions = $api->get_transactions($newTransactionData2);

						foreach($connectedTransactions as $invoicesTransaction) {
							$customerId = 0;
							if(isset($invoicesTransaction['dimensions']['Dimension']['Type'])) {
							   if($invoicesTransaction['dimensions']['Dimension']['Type'] == "Customer"){
								   $customerId = $invoicesTransaction['dimensions']['Dimension']['Value'];
							   }
							} else {
							   foreach($invoicesTransaction['dimensions']['Dimension'] as $dimension){
								   if($dimension['Type'] == "Customer"){
									   $customerId = $dimension['Value'];
								   }
							   }
							}
							$importedSuccessfully = false;
							$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ?";
							$o_query = $o_main->db->query($sql, array($invoicesTransaction['id'], $creditorData['id']));
							$local_transaction = $o_query ? $o_query->row_array() : array();
							if($local_transaction) {
								if($invoicesTransaction['open']) {
									$sql = "UPDATE creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, updatedBy = 'import', updated=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?   WHERE id = ?";
									$o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'], $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction['id']));
									if($o_query) {
										$importedSuccessfully = true;
									}
								} else {
									$sql = "UPDATE creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, updatedBy = 'import', updated=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?  WHERE id = ?";
									$o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'],  $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction['id']));
									if($o_query) {
										$importedSuccessfully = true;
									}
								}
							} else {
								$sql = "INSERT INTO creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, createdBy = 'import', created=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?";
								$o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'], $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit']));
								if($o_query) {
									$transactionId = $o_main->db->insert_id();

									$importedSuccessfully = true;
								}
							}
							if($importedSuccessfully){
								if($local_transaction['collectingcase_id'] > 0){
									$s_sql = "UPDATE collecting_cases SET stopped_date = NOW() WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($local_transaction['collectingcase_id']));
									echo "Case stopped";
								}
							}
						}
					}
				}
			}
		}
	}
	// $s_sql = "UPDATE collecting_cases SET stopped_date = NOW() WHERE id = ?";
	// $o_query = $o_main->db->query($s_sql, array($transaction['collectingcase_id']));
	// if($o_query){
	// 	$s_sql = "UPDATE creditor_transactions SET open = 0 WHERE creditor_transactions.id = ?";
	// 	$o_query = $o_main->db->query($s_sql, array($transaction['id']));
	//
	// 	if($o_query){
	// 		echo 'updated';
	// 	} else {
	// 		echo 'failed';
	// 	}
	// }
}

?>
