<?php
set_time_limit(1800);
$creditorId = $_POST['creditor_id'];
$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditorId));
$creditorData = $o_query ? $o_query->row_array() : array();
if(!$creditorData){
	return;
}
if(!class_exists("Integration24SevenOffice")){
	require_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';
}
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
		
		$bookaccountEnd = 1529;
		if($creditorData['bookaccount_upper_range'] >= 1500){
			$bookaccountEnd = $creditorData['bookaccount_upper_range'];
		}
		$dateStart = $data['changedAfter'];
		$dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));

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

    $s_sql = "SELECT * FROM creditor_transactions  WHERE creditor_id = ? AND open = 1";
    $o_query = $o_main->db->query($s_sql, array($creditorData['id']));
    $localTransactions = ($o_query ? $o_query->result_array() : array());
	$total_sum = 0;
	$integration_total_sum = 0;
	foreach($localTransactions as $localTransaction){
		$total_sum += round($localTransaction['amount'], 2);
	}
	foreach($invoicesTransactions as $invoicesTransaction) {
		$integration_total_sum += round($invoicesTransaction['amount'], 2);
	}
	echo count($invoicesTransactions)." ". $formText_TransactionsFoundInExternalSystem_output."</br>";
	echo count($localTransactions)." ". $formText_TransactionsFoundInLocalDatabase_output;
	if(round($total_sum, 2) != round($integration_total_sum, 2)){
		$sql = "UPDATE creditor SET control_sum_correct = '0000-00-00 00:00:00' WHERE id = ?";
		$o_query = $o_main->db->query($sql, array($creditorData['id']));
		echo "<br/>checksum not correct ".round($total_sum, 2)." ".round($integration_total_sum, 2)."</br></br>";

		$invoice_transaction_ids = array();
		foreach($invoicesTransactions as $invoicesTransaction) {
			$invoice_transaction_ids[] = $invoicesTransaction['id'];
		}
		$extraLocalTransactions = false;
		foreach($localTransactions as $localTransaction) {
			if(!in_array($localTransaction['transaction_id'], $invoice_transaction_ids)) {
				$case_id = 0;
				$collecting_case_id = 0;
				if($localTransaction['collectingcase_id'] > 0 ){
					$case_id = $localTransaction['collectingcase_id'];
				}
				if($localTransaction['collecting_company_case_id'] > 0){
					$collecting_case_id = $localTransaction['collecting_company_case_id'];
				}
				echo '<br/>extra local transaction: <span class="check_transaction" data-transaction="'.$localTransaction['id'].'">'.$localTransaction['id']."</span> ".$localTransaction['invoice_nr']." ". $localTransaction['system_type']. " ";
				if($case_id > 0){
					echo ' has case - <a href="'.$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$case_id.'" target="_blank">'.$case_id."</a>";
					echo "<span class='stop_case_and_close_transaction' data-id='".$localTransaction['id']."'>".$formText_StopCaseAndCloseTransaction_output."</span>";
				}
				if($collecting_case_id > 0){
					echo ' has collecting case - <a href="'.$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$collecting_case_id.'" target="_blank">'.$collecting_case_id."</a>";
					echo "<span class='close_transaction' data-id='".$localTransaction['id']."'>".$formText_CloseTransaction_output."</span>";
				
				}
				if($case_id == 0 && $collecting_case_id == 0){
					echo ' no case'; echo "<span class='delete_transaction' data-id='".$localTransaction['id']."'>".$formText_CloseTransaction_output."</span>";
				}

				$extraLocalTransactions = true;
			}
		}
		if($extraLocalTransactions){
			echo "<br/><span class='delete_all_transaction'>".$formText_CloseAllExtraLocalTransactions_output."</span>";
		}

		$invoice_local_ids = array();
		foreach($localTransactions as $localTransaction) {
			$invoice_local_ids[] = $localTransaction['transaction_id'];
		}
		$extraExternalTransactions = false;
		foreach($invoicesTransactions as $invoicesTransaction) {
			if(!in_array($invoicesTransaction['id'], $invoice_local_ids)) {
				echo '<br/>extra external transaction: '.$invoicesTransaction['id']." ".$invoicesTransaction['date']." ".$invoicesTransaction['dateChanged']." ".$invoicesTransaction['invoiceNr'];
				$extraExternalTransactions = true;
			}
		}
		if($extraExternalTransactions){
			echo "<br/><span class='create_transactions'>".$formText_CreateAllExternalTransactions_output."</span>";
		}
		$missingTransactions = array();
		$differentSummary = array();
		foreach($invoicesTransactions as $invoicesTransaction){
			$pairedTransaction = array();
			foreach($localTransactions as $localTransaction){
				if($localTransaction['transaction_id'] == $invoicesTransaction['id']) {
					$pairedTransaction = $localTransaction;
					break;
				}
			}
			if(!$pairedTransaction){
				$missingTransactions[] = $pairedTransaction;
			} else {
				if(round($invoicesTransaction['amount'], 2) != round($pairedTransaction['amount'], 2)){
					$differentSummary[] = array($invoicesTransaction, $pairedTransaction);
				}
			}
		}
		if(count($missingTransactions) > 0) {
			?>
			<h2><?php echo $formText_MissingTransactions_output;?></h2>
			<table class="table">
				<tr><th><?php echo $formText_CustomerId_output;?></th><th><?php echo $formText_Type_output;?></th><th><?php echo $formText_InvoiceNr_output;?></th><th><?php echo $formText_Amount_output;?></th></tr>
			<?php
			foreach($missingTransactions as $missingTransaction) {
				?>
				<tr><td><?php echo $missingTransaction['invoiceNr'];?></td><td><?php echo $missingTransaction['amount'];?></td></tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		if(count($differentSummary) > 0) {
			?>
			<h2><?php echo $formText_Differences_output;?></h2>
			<table class="table">
				<tr><th><?php echo $formText_CustomerId_output;?></th><th><?php echo $formText_Type_output;?></th><th><?php echo $formText_InvoiceNr_output;?></th><th><?php echo $formText_AmountInExternal_output;?></th><th><?php echo $formText_AmountInLocal_output;?></th></tr>
			<?php
			foreach($differentSummary as $different){
				?>
				<tr><td><?php echo $different[1]['external_customer_id'];?></td><td><?php echo $different[0]['systemType'];?></td><td><?php echo $different[0]['invoiceNr'];?></td><td><?php echo $different[0]['amount'];?></td><td><?php echo $different[1]['amount'];?></td></tr>
				<?php
			}
			?>
			</table>
			<?php
		}
	} else {
		$sql = "UPDATE creditor SET control_sum_correct = NOW() WHERE id = ?";
		$o_query = $o_main->db->query($sql, array($creditorData['id']));
	}


	?>
	<style>
	.delete_transaction {
		cursor: pointer;
		color: #46b2e2;
		margin-left: 20px;
	}
	.stop_case_and_close_transaction {
		cursor: pointer;
		color: #46b2e2;
		margin-left: 20px;
	}
	.close_transaction {
		cursor: pointer;
		color: #46b2e2;
		margin-left: 20px;
	}
	.create_transactions {
		cursor: pointer;
		color: #46b2e2;
		margin-top: 20px;
	}
	.delete_all_transaction {
		cursor: pointer;
		color: #46b2e2;
		margin-top: 20px;
	}
	.check_transaction {
		cursor: pointer;
		color: #46b2e2;
	}
	</style>
	<script type="text/javascript">
	$(function(){
		$(".delete_transaction").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				transaction_id: $(this).data('id')
			};
			ajaxCall('delete_transaction', data, function(json) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(json.html);
				out_popup = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			});
		})
		$(".stop_case_and_close_transaction").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				transaction_id: $(this).data('id')
			};
			ajaxCall('stop_case_and_close_transaction', data, function(json) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(json.html);
				out_popup = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			});
		})
		$(".check_transaction").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				transaction_id: $(this).data('transaction')
			};
			ajaxCall('show_transaction_info', data, function(json) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(json.html);
				out_popup = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			});
		})
		$(".close_transaction").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				transaction_id: $(this).data('id')
			};
			ajaxCall('close_transaction', data, function(json) {
				var data = {
					creditor_id: '<?php echo $creditorId;?>'
				};
				ajaxCall('compareWithOpenTransactions', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});
			});
		})

		$(".delete_all_transaction").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				creditor_id: '<?php echo $creditorData['id'];?>'
			};
			ajaxCall('delete_all_transaction', data, function(json) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(json.html);
				out_popup = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			});
		})

		$(".create_transactions").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				creditor_id: '<?php echo $creditorData['id'];?>'
			};
			ajaxCall('create_transaction', data, function(json) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(json.html);
				out_popup = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			});
		})
	})
	</script>
	<?php
} else {
	echo $api->error;
}
?>
