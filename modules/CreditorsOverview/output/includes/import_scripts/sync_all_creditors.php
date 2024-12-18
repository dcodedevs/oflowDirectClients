<?php
$languageID = 'no';
include(__DIR__."/../../languagesOutput/default.php");
if(is_file(__DIR__."/../../languagesOutput/".$languageID.".php")){
	include(__DIR__."/../../languagesOutput/".$languageID.".php");
}

$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1
AND DATE(cr.sync_started_time) < '".date("Y-m-d")."' AND IFNULL(cr.onboarding_incomplete, 0) = 0
ORDER BY cr.id LIMIT 30";
// $sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
// WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1
// AND DATE(cr.sync_started_time) < '2023-11-13' AND IFNULL(cr.onboarding_incomplete, 0) = 0
// ORDER BY cr.id LIMIT 30";
$o_query = $o_main->db->query($sql);
$creditors = $o_query ? $o_query->result_array() : array();

foreach($creditors as $creditor) {
	$creditorId = $creditor['id'];
	$s_sql = "SELECT creditor.* FROM creditor WHERE sync_started_time is not null AND sync_started_time < (NOW() - INTERVAL 30 MINUTE) AND sync_status = 1 AND id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$creditor_time_over = ($o_query ? $o_query->row_array() : array());

    if($creditor['sync_status'] != 1 || $creditor_time_over) {
		if(!class_exists("Integration24SevenOffice")){
			require_once __DIR__ . '/../../../../'.$creditor['integration_module'].'/internal_api/load.php';
		}
		$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql);
		$integration_sessions = $o_query ? $o_query->result_array() : array();
		$username = "";
		$session_id = "";
		echo "<div style='padding: 5px 0px; border-bottom:1px solid #cecece;'><div><b>".$creditor['creditorName']." (id: ".$creditor['id'].")</b></div>";
		foreach($integration_sessions as $integration_session) {
			$v_config = array(
				'ownercompany_id' => 1,
				'creditorId' => $creditor['id'],
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
	        $s_sql = "UPDATE creditor SET sync_status = 1, sync_started_time = NOW() WHERE id = ?";
	        $o_query = $o_main->db->query($s_sql, array($creditor['id']));
			try {
				include("import_cases2.php");
				if($connectedSuccessfully) {
					$s_sql = "UPDATE creditor SET autosyncing_not_working_date = '0000-00-00' WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor['id']));

				    $data['changedAfter'] = date("Y-m-d", strtotime("01.01.2012"));
				    $data['ShowOpenEntries'] = 1;

					$loopCounter = 0;
					$invoicesTransactions = array();
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

					if($connectedSuccessfully) {
						$sql = "SELECT SUM(amount) as total_sum FROM creditor_transactions WHERE creditor_id = ? AND open = 1";
						$o_query = $o_main->db->query($sql, array($creditor['id']));
						$total_sum_entry = $o_query ? $o_query->row_array() : array();
						$total_sum = round($total_sum_entry['total_sum'], 2);

						$integration_total_sum = 0;
						$invoice_transaction_ids = 0;
						foreach($invoicesTransactions as $invoicesTransaction) {
							$integration_total_sum += round($invoicesTransaction['amount'], 2);
							$invoice_transaction_ids[] = $invoicesTransaction['id'];
						}
						if(round($total_sum, 2) != round($integration_total_sum, 2)) {
							$sql = "UPDATE creditor SET control_sum_correct = '0000-00-00 00:00:00' WHERE id = ?";
							$o_query = $o_main->db->query($sql, array($creditor['id']));
							echo $creditor['customerName']." ".round($total_sum, 2)." ".round($integration_total_sum, 2)."</br></br>";

							$transactions_to_sync = array();
							foreach($localTransactions as $localTransaction){
								if(!in_array($localTransaction['transaction_id'], $invoice_transaction_ids)) {
									if($localTransaction['collectingcase_id'] > 0) {
										$transactions_to_sync[] = $localTransaction;
									}
								}
							}
							if(count($transactions_to_sync) > 0) {
								$newTransactionData = array();
								foreach($transactions_to_sync as $transactions_to_sync) {
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
								                    $o_query = $o_main->db->query($sql, array($invoicesTransaction['id'], $creditor['id']));
								                    $local_transaction = $o_query ? $o_query->row_array() : array();
								                    if($local_transaction) {
								                        if($invoicesTransaction['open']) {
								                            $sql = "UPDATE creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, updatedBy = 'import', updated=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?   WHERE id = ?";
								                            $o_query = $o_main->db->query($sql, array($moduleID, $creditor['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'], $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction['id']));
								                            if($o_query) {
								                                $importedSuccessfully = true;
								                            }
								                        } else {
								                            $sql = "UPDATE creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, updatedBy = 'import', updated=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?  WHERE id = ?";
								                            $o_query = $o_main->db->query($sql, array($moduleID, $creditor['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'],  $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction['id']));
								                            if($o_query) {
								                                $importedSuccessfully = true;
								                            }
								                        }
								                    } else {
								                        $sql = "INSERT INTO creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, createdBy = 'import', created=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?";
								                        $o_query = $o_main->db->query($sql, array($moduleID, $creditor['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'], $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit']));
								                        if($o_query) {
								                            $transactionId = $o_main->db->insert_id();

								                            $importedSuccessfully = true;
								                        }
								                    }
													if($importedSuccessfully){
														if($local_transaction['collectingcase_id'] > 0){
															$s_sql = "UPDATE collecting_cases SET stopped_date = NOW() WHERE id = ?";
															$o_query = $o_main->db->query($s_sql, array($local_transaction['collectingcase_id']));
														}
													}
												}
											}
										}
									}
								}
							}

						} else {
							$sql = "UPDATE creditor SET control_sum_correct = NOW() WHERE id = ?";
							$o_query = $o_main->db->query($sql, array($creditor['id']));
						}
					}

				} else {
					$s_sql = "UPDATE creditor SET autosyncing_not_working_date = NOW() WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor['id']));
					echo 'Failed';
				}
				$s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			} catch(Exception $e) {
				$s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor['id']));
				echo 'Failed';
			}

		} else {
			$s_sql = "UPDATE creditor SET autosyncing_not_working_date = NOW(), sync_started_time = NOW()  WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			echo 'Failed no session';
		}
		echo '</div>';
	}
}
?>
