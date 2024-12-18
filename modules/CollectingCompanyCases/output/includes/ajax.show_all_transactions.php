<?php
$case_id = isset($_POST['case_id']) ? $_POST['case_id'] : 0;

$sql = "SELECT * FROM collecting_company_cases WHERE id = $case_id";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();
if($caseData){
	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
	$creditor = ($o_query ? $o_query->row_array() : array());
	if($creditor){
		require_once __DIR__ . '/../../../'.$creditor['integration_module'].'/internal_api/load.php';
		$api = new Integration24SevenOffice(array(
			'ownercompany_id' => 1,
			'identityId' => $creditor['entity_id'],
			'creditorId' => $creditor['id'],
			'o_main' => $o_main
		));

		?>
		<table class="claimsTable table table-borderless">
			<tr>
				<th width="20%"><?php echo $formText_SystemType_Output; ?></th>
				<th width="10%"><?php echo $formText_Date_Output; ?></th>
				<th width="10%"><?php echo $formText_LastChangedDate_Output; ?></th>
				<th width="10%">
					<?php echo $formText_Account_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_TransactionNo_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_InvoiceNr_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_Amount_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_DueDate_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_LinkId_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_Status_Output;?>
				</th>
			</tr>
			<?php
			$s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."' ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql);
			$connected_transactions = ($o_query ? $o_query->result_array() : array());
			foreach($connected_transactions as $connected_transaction) {
				if($connected_transaction['link_id'] > 0) {
					$s_sql = "SELECT * FROM creditor_transactions WHERE link_id = '".$o_main->db->escape_str($connected_transaction['link_id'])."' AND id <> '".$o_main->db->escape_str($connected_transaction['link_id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."' ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql);
					$linked_transactions = ($o_query ? $o_query->result_array() : array());
					foreach($linked_transactions as $linked_transaction){
						$transactionNr = $linked_transaction['transaction_nr'];
						if($transactionNr > 0) {
							// $data['changedAfter'] = date("Y-m-d", strtotime("01.02.2023"));
							//
							$transactionData = array();
							$transactionData['DateSearchParameters'] = 'DateChangedUTC';
							$transactionData['date_start'] = $data['changedAfter'];
							$transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
							// $transactionData['LinkId'] = 10272870;

							// $transactionData['bookaccountStart'] = 7830;
							// $transactionData['bookaccountEnd'] = 7830;
							$transactionData['TransactionNoStart'] = $transactionNr;
							$transactionData['TransactionNoEnd'] = $transactionNr;

							$invoicesTransactions = $api->get_transactions($transactionData, true);
							$realTransactions = $invoicesTransactions['Transaction'];
							if($realTransactions[0]['Id'] == "") {
								$realTransactions = array($realTransactions);
							}
							if(count($realTransactions) > 0) {

								?>

									<?php
									foreach($realTransactions as $transaction) {
										?>
										<tr>
											<td><?php echo $transaction['SystemType'];?></td>
											<td><?php echo date("d.m.Y", strtotime($transaction['Date'])) ;?></td>
											<td><?php echo date("d.m.Y H:i:s", strtotime($transaction['DateChanged']));?></td>
											<td><?php echo $transaction['AccountNo'];?></td>
											<td><?php echo $transaction['TransactionNo'];?></td>
											<td><?php echo $transaction['InvoiceNo'];?></td>
											<td><?php echo $transaction['Amount'];?></td>
											<td><?php if($transaction['DueDate'] != "") echo date("d.m.Y", strtotime($transaction['DueDate']));?></td>
											<td><?php echo $transaction['LinkId'];?></td>
											<td><?php if($transaction['Open']){ echo $formText_Open_output; } else { echo $formText_Closed_output; } ?></td>
										</tr>
										<?php
									}
									?>
								<?php
							}
						}
					}
				} else {
					$transactionNr = $connected_transaction['transaction_nr'];
					if($transactionNr > 0) {
						// $data['changedAfter'] = date("Y-m-d", strtotime("01.02.2023"));
						//
						$transactionData = array();
						$transactionData['DateSearchParameters'] = 'DateChangedUTC';
						$transactionData['date_start'] = $data['changedAfter'];
						$transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
						// $transactionData['LinkId'] = 10272870;

						// $transactionData['bookaccountStart'] = 7830;
						// $transactionData['bookaccountEnd'] = 7830;
						$transactionData['TransactionNoStart'] = $transactionNr;
						$transactionData['TransactionNoEnd'] = $transactionNr;

						$invoicesTransactions = $api->get_transactions($transactionData, true);
						$realTransactions = $invoicesTransactions['Transaction'];
						if($realTransactions[0]['Id'] == "") {
							$realTransactions = array($realTransactions);
						}
						// var_dump($realTransactions);
						if(count($realTransactions) > 0) {
							?>
							<?php
							foreach($realTransactions as $transaction) {
								?>
								<tr>
									<td><?php echo $transaction['SystemType'];?></td>
									<td><?php echo date("d.m.Y", strtotime($transaction['Date']));?></td>
									<td><?php echo date("d.m.Y H:i:s", strtotime($transaction['DateChanged']));?></td>
									<td><?php echo $transaction['AccountNo'];?></td>
									<td><?php echo $transaction['TransactionNo'];?></td>
									<td><?php echo $transaction['Amount'];?></td>
									<td><?php if($transaction['DueDate'] != "") echo date("d.m.Y", strtotime($transaction['DueDate']));?></td>
									<td><?php echo $transaction['LinkId'];?></td>
									<td><?php if($transaction['Open']){ echo $formText_Open_output; } else { echo $formText_Closed_output; } ?></td>
								</tr>
								<?php
							}
							?>
							<?php
						}
					}
				}
			}
			?>
		</table>
		<?php
	}
}

?>
